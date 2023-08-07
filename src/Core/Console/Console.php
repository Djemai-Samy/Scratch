<?php

namespace Scratch\Core\Console;

use Scratch\Core\FileSystem\FileSystem;
use Scratch\Core\Kernel\Container;
use Scratch\Core\Kernel\Kernel;

class Console extends Kernel {

  public const CONTROLLERS_FOLDER_NAME = 'Scripts/';

  protected const ROUTE_CLASS = Script::class;
  
  protected const ROUTE_SEP = ':';
  /**
   * Initialize the Kernel class.
   */
  public function __construct(array $argv) {
    $this->controller_dir =  __DIR__ . '/Scripts/';
    $this->controllers_namespace = 'Scratch\\Core\\Console\\Scripts';

    $this->container = new Container();

    $this->route = $argv[1];

    // Initialize the FileSystem instance
    $this->fs = $this->container->addDependencies(FileSystem::class);

    // Get the list of controllers
    $this->controllers = $this->getControllers();
  }

  protected function beforeRoute() : void{}

  protected function handleRoute($controller, $handler, $reflection, $attribute): void {

    // Create an instance of the controller class with dependencies resolved
    $controllerInstance = $this->container->addDependencies($this->controllers_namespace . $controller);

    // Inject parameters and dependencies into the method and invoke it
    $params = $this->injectParams($reflection->getParameters(), $attribute->getArguments()['command']);

    var_dump($controllerInstance);
    // Invoke the controller method with the provided parameters
    $response = $controllerInstance->$handler(...$params);
    // Send the response and return
    echo $response;
  }

  protected function handleNotFound(): void {
    // If no matching route is found, handle the 404 case
    $_404Instance = $this->container->addDependencies($this->controllers_namespace . '\_404');
    $_404Instance->index();
  }

  /**
   * Check if the route matches the request path and method.
   *
   * @param string $routePath
   *   The route path to match against
   * @param string $routeMethod
   *   The route method to match against.
   *
   * @return bool
   *   True if the route matches, false otherwise.
   */
  protected function isRouteMatched(array $args): bool {

    // Extract the route path and method from the attribute arguments
    $routePath = $args['command'];

    // Replace parameter placeholders with regex patterns
    $pattern = preg_replace_callback('/\{([a-zA-Z0-9_]+)(<[^>]+>)?\}/', function ($matches) {
      $paramName = $matches[1];
      $pattern = isset($matches[2]) ? substr($matches[2], 1, -1) : '[a-zA-Z0-9_]+';
      return "(?P<$paramName>$pattern)";
    }, $routePath);


    // Escape forward slashes in the pattern for use in a regex
    $pattern = '/^' . str_replace('/', '\/', $pattern) . '$/';

    // Check if the request path and method match the route
    if (preg_match($pattern, $this->route, $matches)) {
      return true;
    }

    return false;
  }
}
