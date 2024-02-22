<?php

declare(strict_types=1);

namespace NITSAN\NsPwa\Service;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Core\Information\Typo3Version;

/**
 * Settings service
 */
class PwaService
{
    /**
     * @return string
     */
    public function provideConfiguration(): string
    {
        $configurationManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface');
        $config = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);


        $variations = $config['ns_pwa.']['settings.'];

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
            "label" => "For Mobile"
          ];
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

        $caching = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Cache\CacheManager::class);
        $caching->flushCaches();

        if (Environment::isComposerMode()) 
        {
          $versionInformation = GeneralUtility::makeInstance(Typo3Version::class);
          $versionInformation->getMajorVersion();

          //Creating PWA Directory
          if(!Environment::getProjectPath() .'/public/fileadmin/pwa'){
            mkdir(Environment::getProjectPath() .'/public/fileadmin/pwa');
          }


          // Copy PWA icons from extension to fileadmin
          if($versionInformation->getMajorVersion() >= 12){
            $this->copyfolder(Environment::getProjectPath() . "/vendor/nitsan/ns-pwa/Resources/Public/pwa/icons/", Environment::getProjectPath() . '/' . '/public/fileadmin/pwa/');
          }
          else{
            $this->copyfolder(Environment::getProjectPath() . "/public/typo3conf/ext/Resources/Public/pwa/icons/", Environment::getProjectPath() . '/' . '/public/fileadmin/pwa/');
          }

          //Creating JavaScript file and append data
          $jsonFile = Environment::getProjectPath().'/public/service-worker.js';
          if (!file_exists($jsonFile)) {
              fopen(Environment::getProjectPath(). "/public/service-worker.js", "w") or die("Unable to open file!");
              GeneralUtility::writeFile($jsonFile, $data);
          }
        }
        else{
          GeneralUtility::writeFile($data, true);
        }

        

        $caching->flushCaches();

        return json_encode($pwa);
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
      // print_r($all);

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
            // echo "$a copied to $to$ff\r\n";
          }
      }}
    }
}

