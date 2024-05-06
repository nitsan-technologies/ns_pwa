<?php

namespace NITSAN\NsPwa\Middleware;


use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Http\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Resource\Exception\InvalidFileException;

class PwaMiddleware implements MiddlewareInterface
{
  protected ServerRequestInterface $request;

  const MANIFEST_NAME = 'site.webmanifest';

  /**
   * @throws InvalidFileException
   */
  public function process(
      ServerRequestInterface $request,
      RequestHandlerInterface $handler
  ): ResponseInterface {
      $this->request = $request;
      $this->processPwa();

      return $handler->handle($this->request);
  }

  /**
   * processPwa
   *
   * @return void
   */
  protected function processPwa(): void
  {
    $caching = GeneralUtility::makeInstance(CacheManager::class);
    $caching->flushCaches();
    
    $configurations = $this->getConfigurations();

    $this->addHeaderData($configurations);

    $data = $this->prepareJsonData($configurations);

    $this->processFiles($data);

  }

  /**
   * getConfigurations
   *
   * @return array
   */
  protected function getConfigurations(): array
  {
    return $this->getSite()->getConfiguration();
  }

  /**
   * addHeaderData
   *
   * @param array $configurations
   * @return void
   */
  protected function addHeaderData(array $configurations): void
  {
    $siteUrl = $this->request->getAttribute('normalizedParams')->getSiteUrl();
    $manifestUrl = $siteUrl.self::MANIFEST_NAME . '?' . time();

    $headerData = "<link rel='manifest' href='{$manifestUrl}'>";
    $headerData .= '<meta name="apple-mobile-web-app-capable" content="yes">';
    $headerData .= '<meta name="apple-mobile-web-app-status-bar-style" content="black">';
    $headerData .= "<meta name='apple-mobile-web-app-title' content='{$configurations['name']}'>";
    $headerData .= "<link rel='apple-touch-icon' href='{$configurations['icon']}'>";
    $headerData .= "<meta name='msapplication-TileImage' content='{$configurations['icon']}'>";
    $headerData .= "<meta name='theme-color' content='{$configurations['theme_color']}'>";
    $headerData .= "<meta name='msapplication-TileColor' content='{$configurations['theme_color']}'>";

    GeneralUtility::makeInstance(PageRenderer::class)->addHeaderData($headerData);
  }

  /**
   * prepareJsonData
   *
   * @param array $configurations
   * @return array
   */
  protected function prepareJsonData(array $configurations): array
  {
    $data = [
        "short_name" => "$configurations[short_name]",
        "name" => "$configurations[name]",
        "icons" => [
            [
              "src" => "$configurations[icon_192]",
              "sizes" => "192x192",
              "type" =>  "$configurations[icon_192_type]",
              "density" => 4
            ],
            [
              "src" => "$configurations[icon_512]",
              "sizes" => "512x512",
              "type" => "$configurations[icon_512_type]"
            ],
            [
              "src" => "$configurations[icon_144]",
              "sizes" => "144x144",
              "type" => "$configurations[icon_144_type]",
              "purpose" => "maskable"
            ]
        ],
        "start_url" =>  "$configurations[start_url]",
        "background_color" => "$configurations[background_color]",
        "display" => "$configurations[display]",
        "scope" => "$configurations[scope]",
        "theme_color" => "$configurations[theme_color]",
    ];

    // Check if ss_icon_mobile exists and add it to the screenshots array
    if (!empty($configurations["ss_icon_desktop"]))
    {
        $data["screenshots"][] = [
            "src" => "$configurations[ss_icon_desktop]",
            "sizes" => "$configurations[ss_icon_size_desktop]",
            "type" => "$configurations[ss_icon_desktop_type]",
            "form_factor" => "wide",
            "label" => "For Desktop"
        ];
    }
    if (!empty($configurations["ss_icon_mobile"]))
    {
        $data["screenshots"][] = [
            "src" => "$configurations[ss_icon_mobile]",
            "sizes" => "$configurations[ss_icon_size_mobile]",
            "type" => "$configurations[ss_icon_mobile_type]",
            "form_factor" => "narrow",
            "label" => "For Mobile"
        ];
    }  

    return $data;
  }

  /**
   * processFiles
   *
   * @param array $data
   * @return void
   */
  protected function processFiles(array $data): void
  {
    $versionInformation = GeneralUtility::makeInstance(Typo3Version::class);
    $versionInformation->getMajorVersion();
    $pwaFileadminPath = '/fileadmin/pwa';
    if (Environment::isComposerMode())
    {
      //Creating PWA Directory
      if(!is_dir(Environment::getPublicPath() .$pwaFileadminPath)){
        mkdir(Environment::getPublicPath() .$pwaFileadminPath);
      }
      // Copy PWA icons from extension to fileadmin
      if($versionInformation->getMajorVersion() >= 12){
        $this->copyfolder(Environment::getProjectPath() . "/vendor/nitsan/ns-pwa/Resources/Public/pwa/icons/", Environment::getPublicPath() . '/' . $pwaFileadminPath . '/');

        //Creating JavaScript file and append data
        $jsonFile = Environment::getPublicPath().'/'.self::MANIFEST_NAME;
        if (!file_exists($jsonFile)) {
          fopen(Environment::getPublicPath(). "/".self::MANIFEST_NAME, "w") or die("Unable to open file!");
        }
          GeneralUtility::writeFile($jsonFile, json_encode($data));
      }
      else{
        $this->copyfolder(Environment::getPublicPath() . "/typo3conf/ext/ns_pwa/Resources/Public/pwa/icons/", Environment::getPublicPath() . '/' . $pwaFileadminPath . '/');
        $jsonFile = Environment::getPublicPath().'/'.self::MANIFEST_NAME;
        if (!file_exists($jsonFile)) {
            fopen(Environment::getPublicPath(). "/".self::MANIFEST_NAME, "w") or die("Unable to open file!");
        }
        GeneralUtility::writeFile($jsonFile, json_encode($data));
      }
    }
    else{
      //File Creation and clone icons folder from extension
      if($versionInformation->getMajorVersion() >= 12)
      {
        //Creating PWA Directory
        if(!is_dir(Environment::getPublicPath() .$pwaFileadminPath)){
          mkdir(Environment::getPublicPath() .$pwaFileadminPath);
        }
        $this->copyfolder(Environment::getPublicPath() . "/typo3conf/ext/ns_pwa/Resources/Public/pwa/icons/", Environment::getProjectPath() . '/' . 'fileadmin/pwa/');

        //Creating JavaScript file and append data
        $jsonFile = Environment::getPublicPath().'/'.self::MANIFEST_NAME;
        if (!file_exists($jsonFile)) {
          fopen(Environment::getPublicPath(). "/".self::MANIFEST_NAME, "w") or die("Unable to open file!");
        }
        GeneralUtility::writeFile($jsonFile, json_encode($data));
      }
      else{
        //Creating PWA Directory
        if(!is_dir(Environment::getPublicPath() .$pwaFileadminPath)){
          mkdir(Environment::getPublicPath() .$pwaFileadminPath);
        }
        $this->copyfolder(Environment::getPublicPath() . "/typo3conf/ext/ns_pwa/Resources/Public/pwa/icons/", Environment::getPublicPath() . '/' . 'fileadmin/pwa/');
        
        $jsonFile = Environment::getPublicPath().'/'.self::MANIFEST_NAME;
        if (!file_exists($jsonFile)) {
          fopen(Environment::getPublicPath(). "/".self::MANIFEST_NAME, "w") or die("Unable to open file!");
        }
        GeneralUtility::writeFile($jsonFile, json_encode($data));
      }
    }
  }

  /**
   * copyfolder
   *
   * @param string $from
   * @param string $to
   * @param string $ext
   * @return void
   */
  protected function copyfolder(string $from, string $to, string $ext="*"): void
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
        }
      }
    }
  }

  /**
   * @return ServerRequest
   */
  protected function getServerRequest(): ServerRequest
  {
      return $GLOBALS['TYPO3_REQUEST'];
  }
    
  /**
  * @return Site
  */
  protected function getSite(): Site
  {
    return $this->getServerRequest()->getAttribute('site');
  }
} 