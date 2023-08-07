<?php

namespace Scratch\Core\Kernel;

use Scratch\Core\Config\Config;
use Scratch\Core\Controller;
use Scratch\Core\Controller\Errors\_404;
use Scratch\Core\HTTP\Request;
use Scratch\Core\Env\Env;
use Scratch\Core\FileSystem\FileSystem;
use Scratch\Core\React\ReactNavigation;
use Scratch\Core\Route;
use Scratch\Core\SSG;
use Scratch\Core\View\Errors\_404 as _404_View;
use Scratch\Core\View\PageView;

class App extends Kernel {

  public const STATIC_FOLDER =  '/static';

  protected const ROUTE_CLASS = Route::class;

  protected const ROUTE_SEP = '/';

  protected const VIEWS_FOLDER = '/View';

  protected const DEFAULT_VIEW_NAME = 'PageView';

  public const CONFIG_FOLDER = '/config';

  public const CONFIG_ROUTES_FILE = 'routes.json';

  protected $_404_controller_name;
  protected $default_404_view_name;
  /**
   * Views namespace.
   *
   * @var string
   */
  protected $views_namespace;
  protected $views_dir;
  protected $default_view_name;

  /**
   * Request instance.
   *
   * @var Request
   */
  protected $request;

  protected $static_file;

  public function __construct() {

    parent::__construct();

    $config = new Config(static::APP_ROOT . static::CONFIG_FOLDER . '/' . static::CONFIG_ROUTES_FILE);

    $this->controller_dir =  static::APP_ROOT . $config->get('controllers.resource.path');
    $this->controllers_namespace =  $config->get('controllers.resource.namespace');

    $this->views_dir =  $config->get('views.resource.path');
    $this->views_namespace =  $config->get('views.resource.namespace');
    $this->default_view_name =  $config->get('views.resource.default');

    $this->_404_controller_name =  $config->get('controllers.resource._404');
    $this->default_404_view_name =  $config->get('views.resource._404');

    $this->container = new Container();

    // Initialize the FileSystem instance
    $this->fs = $this->container->addDependencies(FileSystem::class);

    // Initialize the Request instance
    $this->request = $this->container->addDependencies(Request::class);

    $this->route = $this->request->getUri();

    // Get the list of controllers
    $this->controllers = $this->getControllers();

    $this->static_file = $this->fs->getStaticFileFromURI($this->request->getUri());
  }

  protected function beforeRoute(): void {
    if (!$this->request->acceptJson()  && $this->static_file) {
      if (Env::isProd()) {
        require $this->static_file;
        die();
      }
    }
  }

  protected function handleRoute($controller, $handler, $reflection, $attribute): void {
    // Create an instance of the controller class with dependencies resolved
    $controllerInstance = $this->container->addDependencies($this->controllers_namespace . $controller, 'controller');

    // Inject parameters and dependencies into the method and invoke it
    $params = $this->injectParams($reflection->getParameters(), $attribute->getArguments()[0]);

    $extendsAbstractController = is_subclass_of($controllerInstance, Controller::class,);
    if ($extendsAbstractController) {
      $view = null;
      if ($this->default_404_view_name) {
        $view = $this->container->getDepInstance($this->default_view_name);
      }

      if (!$view) {
        $view = $this->container->getDepInstance($this->views_namespace . '\\' . PageView::NAME);
      }
      if (!$view) {
        $view = $this->container->getDepInstance(PageView::class);
      }

      if ($view) {
        $controllerInstance->setDefaultView($view);
      }
    }

    // Invoke the controller method with the provided parameters
    $response = $controllerInstance->$handler(...$params);

    $ssgAttributes = $reflection->getAttributes(SSG::class);
    if ($extendsAbstractController && ($ssgAttributes || count($response->getData()) == 0)) {
      $this->handleStatic($ssgAttributes, $controllerInstance, $response);
    }
    // Send the response and return
    $response->send();
  }

  function handleStatic($ssgAttributes, $controllerInstance,  $response) {

    $isStaticPath = $ssgAttributes || count($response->getData()) == 0;

    if (isset($ssgAttributes[0]) && isset($ssgAttributes[0]->getArguments()['paths'])) {
      $getPaths = $ssgAttributes[0]->getArguments()['paths'];
      $paths = $controllerInstance->$getPaths();
      $isStaticPath = in_array($this->request->getUri(), $paths);
    }

    if ($isStaticPath) {

      $this->fs->createIndex($this->request->getUri(), $response->getContent());
    }
  }


  protected function isRouteMatched(array $args): bool {

    // Extract the route path and method from the attribute arguments
    $routePath = $args[0];
    $routeMethods = $args[1];

    // Replace parameter placeholders with regex patterns
    $pattern = preg_replace_callback('/\{([a-zA-Z0-9_]+)(<[^>]+>)?\}/', function ($matches) {
      $paramName = $matches[1];
      $pattern = isset($matches[2]) ? substr($matches[2], 1, -1) : '[a-zA-Z0-9_]+';
      return "(?P<$paramName>$pattern)";
    }, $routePath);

    // Escape forward slashes in the pattern for use in a regex
    $pattern = '/^' . str_replace('/', '\/', $pattern) . '$/';

    $reqRoute = explode('?', $this->request->getUri())[0];

    // Check if the request path and method match the route
    if (preg_match($pattern, $reqRoute, $matches) && in_array($this->request->getMethod(), $routeMethods)) {
      return true;
    }

    return false;
  }

  protected function handleNotFound(): void {
    // Add the default view to the controller
    $_404Instance = $this->getNotFoundController();

    $reactNav = $this->container->getDepInstance(ReactNavigation::class);

    if (!$reactNav->isSet() || Env::isDev()) {
      $reactNav->load();
    }

    $routes = $reactNav->getPages();

    $response = $_404Instance->index();

    if (isset($routes['pages'])) {
      foreach ($routes['pages'] as $key => $route) {
        if ($route['route'] == $this->request->getUri()) {
          $this->fs->createIndex($this->request->getUri(), $response->getContent());
        }
      }
    }


    $response->send();
  }

  private function getNotFoundController() {
    $_404Instance = null;

    if ($this->_404_controller_name) {
      // Get the default Controller for 404
      $_404Instance = $this->container->addDependencies($this->_404_controller_name, 'controller');
    }

    if (!$_404Instance) {
      $_404Instance = $this->container->addDependencies($this->controllers_namespace . '\\Errors\\_404', 'controller');
    }

    if (!$_404Instance) {
      $_404Instance = $this->container->addDependencies(_404::class, 'controller');
    }

    $_404_view = null;
    if ($this->default_404_view_name) {
      $_404_view = $this->container->addDependencies($this->default_404_view_name);
    }
    if (!$_404_view) {
      $_404_view = $this->container->addDependencies($this->views_namespace . '\\Errors\\' . _404_View::NAME);
    }
    if (!$_404_view) {
      $_404_view = $this->container->addDependencies(_404_View::class);
    }

    // Add the default view to the controller
    $_404Instance->setDefaultView($_404_view);

    return $_404Instance;
  }
}
