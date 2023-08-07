<?php

namespace Scratch\Core\React;

use DirectoryIterator;
use Scratch\Core\Config\Config;
use Scratch\Core\FileSystem\FileSystem;
use Scratch\Core\Kernel\Kernel;

/**
 * Class ReactNavigation
 * Handles the generation of React router configuration based on the project folder structure.
 */
class ReactNavigation {

  private $pages = [];
  /**
   * ReactNavigation constructor.
   * @param FileSystem $fs A FileSystem object used for file-related operations.
   * @param ReactConfig $rc A ReactConfig object holding configuration for React navigation.
   */
  public function __construct(private FileSystem $fs, private ReactConfig $rc) {
  }

  /**
   * Check if the React router configuration file is set.
   * @return bool True if the configuration file is set, false otherwise.
   */
  public function isSet() {
    return $this->fs->file_exists(ReactConfig::ROOT_PROJECT . ReactConfig::ROUTER_FOLDER . ReactConfig::ROUTES_FILE_NAME);
  }

  /**
   * Generate and save the React router configuration based on the project folder structure.
   */
  public function load() {

    $routesFolder = $this->rc->getFolder();
   
    if(!$routesFolder){
      return;
    }

    $pagesFolder = ReactConfig::ROOT_PROJECT .  '/'.$this->rc->getFolder() . '/pages/';

    $result = ["pages" => []];
    $this->scanFolder($pagesFolder, $pagesFolder, $result);

    // Create the router folder if it doesn't exist
    $this->fs->createFolderStructure(ReactConfig::ROUTER_FOLDER, ReactConfig::ROOT_PROJECT);

    // Save the generated routes configuration to a JSON file
    $this->fs->writeJsonFile(ReactConfig::ROOT_PROJECT . ReactConfig::ROUTER_FOLDER . ReactConfig::ROUTES_FILE_NAME, $result);
    $this->pages = $result;
  }

  function scanFolder($absolutePath, $folderPath, &$result, $nestedLevel = 0) {

    $dir = new DirectoryIterator($folderPath);
    $pages = [];
    foreach ($dir as $fileInfo) {
      if ($fileInfo->isDot()) {
        continue;
      }

      $filePath = $fileInfo->getPathname();
      $relativePath = str_replace('\\', '/',   substr($filePath, strlen($absolutePath)),);

      if ($fileInfo->isDir()) {
        // Generate the configuration for a subfolder
        $page = [
          "route" => "/" . $fileInfo->getBasename(),
          "path" => "/" . $fileInfo->getBasename() . "/index.jsx",
        ];

        $subPages = ['pages' => []];
        // Recursively scan the subfolder
        $this->scanFolder($absolutePath, $filePath, $subPages, $nestedLevel + 1);

        if (!empty($subPages)) {
          $page["subPaths"] = $subPages['pages'];
        }

        $pages[] = $page;
      } else {
        // Generate the configuration for a file
        if (str_contains($fileInfo->getBasename(), 'index.jsx') && !($nestedLevel == 0)) {
          continue;
        }

        $page = [
          "route" => "/" . (!str_contains($fileInfo->getBasename(), 'index.jsx') || $nestedLevel !== 0 ? explode('.jsx', $relativePath)[0] : ""),
          "path" => "/" . $relativePath,
        ];

        $pages[] = $page;
      }
    }
    if (!empty($pages)) {
      // Merge the generated pages into the result array
      $result["pages"] = array_merge($result["pages"], $pages);
    }
  }

  function getDataFromJson() {

    if (!$this->isSet()) {
      $this->load();
      return;
    }

    return $this->fs->readJsonFile(ReactConfig::ROOT_PROJECT . ReactConfig::ROUTER_FOLDER . ReactConfig::ROUTES_FILE_NAME);
  }

  /**
   * Get the value of pages
   */
  public function getPages() {
    if (!$this->pages) {
      $this->pages = $this->getDataFromJson();
    }
    return $this->pages;
  }
}
