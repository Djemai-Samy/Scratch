<?php

namespace Scratch\Core\FileSystem;

use Scratch\Core\Kernel\Kernel;

class FileSystem {


  function __construct() {
  }

  public function file_exists($filename) {
    return file_exists($filename);
  }

  public function getFileContent(string $filePath): ?string {
    return file_get_contents($filePath) ?: null;
  }

  public function createFile(string $filePath, string $content): bool {
    return (bool) file_put_contents($filePath, $content);
  }

  public function is_dir($folderName) {
    return is_dir($folderName);
  }

  public function getStaticFileFromURI($uri) {
    $filename = $this->getIndex($this->getStaticFolder() . $uri);
    return $this->file_exists($filename) ? $filename : false;
  }

  public function createIndex(string $uri, string $content) {

    if (!$this->is_dir($this->getStaticFolder() . $uri)) {
      $this->createFolderStructure($this->getStaticFolder(true) . $uri, Kernel::APP_ROOT);
    }

    $filename = rtrim($uri, '/') . '/index.html';

    file_put_contents($this->getStaticFolder() . $filename, $content);
  }

  public function getIndex(string $folder,) {
    return $folder . (substr($folder, -1) === '/' ? 'index.html' : '/index.html');
  }

  public function createFolderStructure(string $folderStructure, string $basePath = ''): bool {

    // Normalize the folder structure to remove leading and trailing slashes and split it into parts
    $folderStructure = trim($folderStructure, '/');
    $folderParts = explode('/', $folderStructure);

    // Initialize the current directory to the base path
    $currentDir = $basePath;

    // Loop through each folder part and create the directories if they don't exist
    foreach ($folderParts as $folderPart) {

      $currentDir .=  '/' . $folderPart;
      // Check if the directory already exists
      if (!$this->is_dir($currentDir)) {
        // If the directory doesn't exist, create it with appropriate permissions (e.g., 0755 for readable and writable by owner)
        if (!mkdir($currentDir, 0755)) {
          // Handle the case when the directory creation fails (e.g., due to permissions or other issues)
          return false;
        }
      }
    }
    return true;
  }

  static function getAppRoot() {
    return Kernel::APP_ROOT;
  }
  static function getScratchFolder($relative = false) {
    return ($relative ? '' :  Kernel::APP_ROOT) . kernel::SCRATCH_FOLDER;
  }
  static function getStaticFolder($relative = false) {
    return ($relative ?  '' : Kernel::APP_ROOT) . Kernel::SCRATCH_FOLDER . Kernel::STATIC_FOLDER;
  }

  public function listFilesInDirectory(string $directoryPath): array {
    $files = scandir($directoryPath);
    $files = array_diff($files, ['.', '..']); // Remove "." and ".." entries
    return array_values($files);
  }

  public function copyFile(string $sourceFilePath, string $destinationFilePath): bool {
    return copy($sourceFilePath, $destinationFilePath);
  }

  public function deleteFile(string $filePath): bool {
    return unlink($filePath);
  }

  public function readJsonFile(string $filePath): ?array {
    $jsonString = $this->getFileContent($filePath);
    return $jsonString ? json_decode($jsonString, true) : null;
  }

  public function writeJsonFile(string $filePath, array $data): bool {
    $jsonString = json_encode($data);

    if ($jsonString === false) {
      return false;
    }

    return $this->createFile($filePath, $jsonString);
  }
}
