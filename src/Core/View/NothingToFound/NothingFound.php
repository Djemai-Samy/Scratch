<?php

namespace Scratch\Core\View\NothingToFound;

use Scratch\Core\HTTP\Request;
use Scratch\Core\Logger\Logger;
use Scratch\Core\View\Scripts;
use Scratch\Core\View\Styles;
use Scratch\Core\View\View;
use Scratch\Core\Config\Config;
use Scratch\Core\FileSystem\FileSystem;
use Scratch\Core\Kernel\App;
use Scratch\Core\React\React;

class NothingFound extends View {

  const NAME = 'NothingFound';
  private Styles $styles;
  private Scripts $scripts;

  private $config_exist = false;

  function __construct(
    private Request $req,
    private Logger $log,
    private React $react,
    private FileSystem $fs,
    private Help $help,
  ) {
    $this->styles = new Styles();
    $config = new Config(App::APP_ROOT . App::CONFIG_FOLDER . '/' . App::CONFIG_ROUTES_FILE);
    if ($config->get('controllers.resource.path') && $config->get('views.resource.path')) {
      $this->config_exist = true;
    }
    $this->scripts = new Scripts();
  }

  private function getConfig() {
    if ($this->config_exist) return '';
    return (<<<HTML
      <h2>
          First, add a config file: <code>config/routes.json</code>
      </h2>
      <pre class="card">
        {
          "controllers": {
            "resource": {
              "path": "/src/Controller",
              "namespace": "YourNameSpace\\Controllers",
            }
          },
          "views": {
            "resource": {
              "path": "/src/View",
              "namespace": "YourNameSpace\\View",
            }
          }
        }
        </code>
      </pre>
    HTML);
  }

  function render($data = []) {
    $style = $this->fs->getFileContent(__DIR__ . '/style.css');
    $message = $this->req->getUri() == '/' ? 'You didn\'t setup the App!' : 'You didn\'t setup 404 Controller!';
    return (<<<HTML
      <style>
         {$style}
      </style>
      <div class="hero">
        <h1>Welcome to Scratch</h1>
        <p class="read-the-docs">If you see this, its means $message</p>

        <a href="https://djemai-samy.com" target="_blank" >
              
        </a>
      </div>

      <h2 class="logo-label">
        <span class="PHP" >
          PHP
         </span>
        + 
        <span class="React">
          React
        </span>
      </h2>

      <hr>
      
      {$this->getConfig()}
    
      <hr>

      {$this->help->render()}

    HTML);
  }
}
