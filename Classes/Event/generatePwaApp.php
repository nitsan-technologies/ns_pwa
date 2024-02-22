<?php

namespace NITSAN\NsPwa\Event;

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
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
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        $extbaseFrameworkConfiguration = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        $variations = $extbaseFrameworkConfiguration['ns_pwa.']['settings.'];

        if (is_object($extname)) {
			    $extname = $extname->getPackageKey();
		    }
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
        mkdir(Environment::getPublicPath() .'/fileadmin/pwa');

        $this->copyfolder(Environment::getPublicPath() . "/typo3conf/ext/ns_pwa/Resources/Public/pwa/icons/", Environment::getPublicPath() . '/' . 'fileadmin/pwa/');
 
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
    
    function copyfolder ($from, $to, $ext="*") 
    {
      // (A1) SOURCE FOLDER CHECK
      if (!is_dir($from)) { exit("$from does not exist"); }

      // (A2) CREATE DESTINATION FOLDER
      if (!is_dir($to)) {
        if (!mkdir($to)) { exit("Failed to create $to"); };
        echo "$to created\r\n";
      }

      // (A3) GET ALL FILES + FOLDERS IN SOURCE
      $all = glob("$from$ext", GLOB_MARK);
      print_r($all);

      // (A4) COPY FILES + RECURSIVE INTERNAL FOLDERS
      if (count($all)>0) 
      { 
        foreach ($all as $a) 
        {
          $ff = basename($a); // CURRENT FILE/FOLDER
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

    