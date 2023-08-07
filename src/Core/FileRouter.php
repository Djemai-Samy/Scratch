<?php
class FileRouter {
  // Filename based routing
  static function loadController() {

    $controller = 'Home';
    $action = "index";
    $params = [];

    $URL_parts = self::splitURL();
    $controller_name = ucfirst($URL_parts[0]);
    unset($URL_parts[0]);

    $filename = __DIR__ . '/../Controllers/' . $controller_name . '.php';

    if (file_exists($filename)) {
      $controller = $controller_name;
      if (isset($URL_parts[1])) {
        if (method_exists($controller, $URL_parts[1])) {
          $action = $URL_parts[1];
          unset($URL_parts[1]);
        }
      }
      $params = $URL_parts ? array_values($URL_parts) : [];
    } else {
      $filename = __DIR__ . '/../Controllers/_404.php';
      $controller = "_404";
    }
    // require $filename;
    $cotrollerInstance = new ('Scratch\\Controllers\\' . $controller);
    call_user_func_array([$cotrollerInstance, $action], $params);
  }

  private static function splitURL() {
    $URL = $_GET['url'] ?? "home";
    $URL_parts = explode('/', $URL);
    return $URL_parts;
  }
}
