<?php

namespace NITSAN\NsPwa\Event;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Information\Typo3Version;
/**
 * Setup
 */
class generatePwaApp
{
   /**
     * executeOnSignalAfter
     *
     * @return void
     */
    public function generatePwaApp($extname = null)
    {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('ns_pwa', 'Configuration/TypoScript', 'Pwa');

        $versionInformation = GeneralUtility::makeInstance(Typo3Version::class);
        $versionInformation->getMajorVersion();

        $configurationManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface');
        $extbaseFrameworkConfiguration = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);

        $variations = isset($extbaseFrameworkConfiguration['ns_pwa.']['settings.']) ? $extbaseFrameworkConfiguration['ns_pwa.']['settings.'] : [];        
        if (is_object($extname)) {
			    $extname = $extname->getPackageKey();
		    }
        if(is_array($variations) && !empty($variations)){
          $pwa = [
            "short_name" => $variations['short_name'],
            "name" => $variations['name'],
            "icons" => [
                  [
                    "src" => "$variations[icon_192]",
                    "sizes" => "192x192",
                    "type" => "image/png",
                    "density" => 4
                  ],
                  [
                    "src" => "$variations[icon_512]",
                    "sizes" => "512x512",
                    "type" => "image/png"
                  ],
                  [
                    "src" => "$variations[icon_144]",
                    "sizes" => "144x144",
                    "type" => "image/png",
                    "purpose" => "maskable"
                  ]
                // Add other icon configurations
            ],
            "start_url" => "$variations[start_url]",
            "background_color" => "$variations[background_color]",
            "display" => "$variations[display]",
            "scope" => "$variations[scope]",
            "theme_color" => "$variations[theme_color]",
          ];

          // Check if ss_icon_mobile exists and add it to the screenshots array
          if (isset($variations['ss_icon_desktop']) && !empty($variations['ss_icon_desktop']))
          {
            $pwa['screenshots'][] = [
              "src" => "$variations[ss_icon_desktop]",
              "sizes" => "2880x2880",
              "type" => "image/jpg",
              "form_factor" => "wide",
              "label" => "For Desktop"
            ];
          }
          if (isset($variations['ss_icon_mobile']) && !empty($variations['ss_icon_mobile']))
          {
            $pwa['screenshots'][] = [
              "src" => "$variations[ss_icon_mobile]",
              "sizes" => "2880x2880",
              "type" => "image/jpg",
              "form_factor" => "narrow",
              "label" => "For Desktop"
            ];
          }
  
          if($versionInformation->getMajorVersion() >= 12)
          {
            //Creating PWA Directory
            if(!is_dir(Environment::getProjectPath() .'/fileadmin/pwa')){
              mkdir(Environment::getProjectPath() .'/fileadmin/pwa');
            }
            $this->copyfolder(Environment::getPublicPath() . "/typo3conf/ext/ns_pwa/Resources/Public/pwa/icons/", Environment::getProjectPath() . '/' . 'fileadmin/pwa/');
          }
          else{
            //Creating PWA Directory
            if(!is_dir(Environment::getPublicPath() .'/fileadmin/pwa')){
              mkdir(Environment::getPublicPath() .'/fileadmin/pwa');
            }
            $this->copyfolder(Environment::getPublicPath() . "/typo3conf/ext/ns_pwa/Resources/Public/pwa/icons/", Environment::getPublicPath() . '/' . 'fileadmin/pwa/');
          }
          
          $jsonFile = Environment::getPublicPath().'/service-worker.js';
          if (!file_exists($jsonFile)) {
              fopen(Environment::getPublicPath(). "/service-worker.js", "w") or die("Unable to open file!");
          }
          
          $short_name = $variations['short_name'];
          $name = $variations['name'];
          $icon = $variations['icon'];
          $data = "const dataCacheName = '$short_name';
          const cacheName = '$name';
          const filesToCache = [
            '/',
            '$icon',
          ];
          
          
          //install the sw
          self.addEventListener('install', function (e) {
            console.log('[ServiceWorker] Install');
            e.waitUntil(
              caches.open(cacheName).then(function (cache) {
                console.log('[ServiceWorker] Caching app shell');
                return cache.addAll(filesToCache);
              })
            );
          });
          
          
          self.addEventListener('activate', function (e) {
            console.log('[ServiceWorker] Activate');
            e.waitUntil(
              caches.keys().then(function (keyList) {
                return Promise.all(keyList.map(function (key) {
                  if (key !== cacheName && key !== dataCacheName) {
                    console.log('[ServiceWorker] Removing old cache', key);
                    return caches.delete(key);
                  }
                }));
              })
            );
            return self.clients.claim();
          });
          
          
          self.addEventListener('fetch', function (e) {
            console.log('[Service Worker] Fetch', e.request.url);
            e.respondWith(
              caches.match(e.request).then(function (response) {
                return response || fetch(e.request);
              })
            );
          });
          ";
          GeneralUtility::writeFile($jsonFile, $data);
        }

    }
    
    function copyfolder ($from, $to, $ext="*") 
    {
      // Source Folder Check
      if (!is_dir($from)) { exit("$from does not exist"); }

      // Create Destination Folder
      if (!is_dir($to)) {
        if (!mkdir($to)) { exit("Failed to create $to"); };
        echo "$to created\r\n";
      }

      // Get all files + folders in source
      $all = glob("$from$ext", GLOB_MARK);

      // Copy files + recursive internal folders
      if (count($all)>0)
      { 
        foreach ($all as $a)
        {
          $ff = basename($a); // Current file/folder
          if (is_dir($a))
          {
            $this->copyfolder("$from$ff/", "$to$ff/");
          }
          else {
            if (!copy($a, "$to$ff"))
            {
              exit("Error copying $a to $to$ff");
            }
            echo "$a copied to $to$ff\r\n";
          }
      }}
    }
}

    