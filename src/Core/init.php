<?php

spl_autoload_register(function ($class) {
    $namespacePrefix = 'Scratch\\'; // Adjust this to your specific namespace prefix
    $baseDirectory = __DIR__ . '/../../../src/'; // Adjust this to your base directory path
    $extension = '.php'; // File extension for class files

    // Check if the class uses the specified namespace prefix
    if (strpos($class, $namespacePrefix) === 0) {
        // Remove the namespace prefix and DIRECTORY_SEPARATOR from the class
        $relativeClass = substr($class, strlen($namespacePrefix));
        $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass);

        // Construct the full path to the class file
        $file = $baseDirectory . $relativePath . $extension;
        try {
            // Use include_once instead of require to handle autoload errors gracefully
            include_once $file;
        } catch (Exception $e) {
            // Handle any exceptions that may occur during the autoload process
            // For example, log the error or show a user-friendly message
            // You can also choose to re-throw the exception if needed
        }
    }
});



//spl_autoload_register(
//  function ($class) {
//    $classArray = explode('\\', $class);
//    array_shift($classArray);
//    $fileName = implode("/", $classArray);
//    $path = __DIR__ . '/../' . str_replace('\\', '/', $fileName) . '.php';
//    if (file_exists($path)) {
//      require $path;
//    }
//  }
//);

// require __DIR__ . '/../../vendor/autoload.php';