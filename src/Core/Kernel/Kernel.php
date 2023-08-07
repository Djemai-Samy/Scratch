<?php

namespace Scratch\Core\Kernel;

use Scratch\Core\Config\Config;
use Scratch\Controllers\_404;
use Scratch\Core\HTTP\Request;
use ReflectionMethod;
use Scratch\Core\FileSystem\FileSystem;
use Scratch\Core\Route;
use Scratch\Core\SSG;

abstract class Kernel {

  public const APP_ROOT = __DIR__ . '/../../../../../..';

  public const SCRATCH_FOLDER =  '/.scratch';

  public const STATIC_FOLDER =  '/static';

  public const ROUTES_FOLDER =  '/routes';

  protected const ROUTE_CLASS = '';

  protected const ROUTE_SEP = '';

  /**
   * Controllers namespace.
   *
   * @var string
   */
  protected $controllers_namespace;

  /**
   * Controllers directory.
   *
   * @var string
   */
  protected $controller_dir;

  protected FileSystem $fs;

  /**
   * Container instance.
   *
   * @var Container
   */
  protected $container;

  /**
   * Array of controllers.
   *
   * @var array
   */
  protected $controllers = [];

  protected $route;

  /**
   * Initialize the Kernel class.
   */
  public function __construct() {
  }

  abstract protected function beforeRoute(): void;
  abstract protected function handleRoute($controller, $handler, $reflection, $attribute): void;
  abstract protected function handleNotFound(): void;

  /**
   * Handle the request.
   *
   * @return void
   */
  public function handle(): void {
    $this->beforeRoute();

    foreach ($this->controllers as $controller) {
      if (!class_exists($this->controllers_namespace . $controller)) continue;

      // Get the list of methods in the controller class
      $handlers = get_class_methods($this->controllers_namespace . $controller);

      foreach ($handlers as $handler) {

        // Create a ReflectionMethod object to access information about the controller method
        $reflection = new ReflectionMethod($this->controllers_namespace . $controller, $handler);

        // Get attributes with Route class from the controller method
        $attributes = $reflection->getAttributes(static::ROUTE_CLASS);

        foreach ($attributes as $attribute) {
          

          if ($this->isRouteMatched($attribute->getArguments())) {

            $this->handleRoute($controller, $handler, $reflection, $attribute);
            die;
          }
        }
      }
    }

    $this->handleNotFound();
  }

  /**
   * Get the list of controllers.
   *
   * @return array
   */
  protected function getControllers(): array {
    $controllersFilnames = [];
    $this->scanForController($this->controller_dir, $controllersFilnames);
    return $controllersFilnames;
  }

  /**
   * Recursively scans a directory for controllers and populates an array with controller names.
   *
   * @param string $directory
   *   The directory path to scan.
   * @param array $controllers
   *   Array to store controller names.
   *
   * @return array
   *   the array passe in parameters with the added controllers name
   */
  private function scanForController(string $directory, array &$controllers): array {
    // Get the contents of the directory
    $directoryContents = scandir($directory);

    foreach ($directoryContents as $directoryContent) {
      // Skip current directory and parent directory entries
      if ($directoryContent == '.' or $directoryContent == '..') {
        continue;
      }

      // If the entry is a file, extract the controller name and add it to the array
      if (is_file($directory . '/' . $directoryContent)) {
        $filename = explode($this->controller_dir, $directory . '/' . $directoryContent)[1];

        $classname = explode('.', $filename)[0];
        $controllers[] =  str_replace('/', '\\', $classname);
      } else {
        // If the entry is a directory, recursively scan it for controllers
        $this->scanForController($directory . '/' . $directoryContent, $controllers);
      }
    }
    return $controllers;
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
  protected abstract function isRouteMatched(array $args): bool;

  /**
   * Inject dependencies and route parameters into method parameters.
   *
   * @param array $parameters
   *   Array of method parameters.
   * @param string $routePath
   *   The route path.
   *
   * @return array
   *   Array of injected parameters.
   */
  protected function injectParams(array $parameters, string $routePath): array {
    $params = [];
    foreach ($parameters as $parameter) {
      $dependencyClass = $parameter->getType();


      if ($dependencyClass !== null) {
        // If the parameter is a dependency, get the dependency instance
        $params[] = $this->getDependency($dependencyClass);
      } else {
        // If the parameter is a route parameter, extract it from the route path
        $params[] = $this->getRouteParamValue($routePath, $parameter->getName());
      }
    }
    return $params;
  }

  /**
   * Get the dependency instance.
   *
   * @param \ReflectionClass $dependencyClass
   *   The dependency class.
   *
   * @return mixed
   *   The dependency instance.
   */
  private function getDependency($dependencyClass) {
    $dependencyClassName = $dependencyClass->getName();
    $dependencyInstance = $this->container->addDependencies($dependencyClassName);
    return $dependencyInstance;
  }

  /**
   * Get the route parameter value from the route path.
   *
   * @param string $routePath
   *   The route path.
   * @param mixed $param
   *   The route parameter.
   *
   * @return string|bool
   *   The route parameter value if found, false otherwise.
   */
  private function getRouteParamValue(string $routePath, $param): string|bool {
    // Split the route path into segments
    $routeSegments = explode(static::ROUTE_SEP, $routePath);

    // Split the request URI into segments
    $requestSegments = explode(static::ROUTE_SEP,  $this->route);


    // Loop for every segment of the root 
    foreach ($routeSegments as $index => $segment) {
      // Check if the segment matches the route parameter

      if (preg_match('/\{(' . $param . ')(<[^>]+>)?\}/', $segment, $matches)) {

        // Not Regex route
        // If the parameter does not have a regex constraint, 
        if (!isset($matches[2])) {
          // return the corresponding segment value from the request segments
          return $requestSegments[$index];
        }

        // Regex route
        // If the parameter has a regex constraint, attempt to match the constraint against the request segment
        if (preg_match($matches[2], $requestSegments[$index], $matches)) {
          return $requestSegments[$index];
        }
      }
    }
    // If no match is found, return false
    return false;
  }
}
