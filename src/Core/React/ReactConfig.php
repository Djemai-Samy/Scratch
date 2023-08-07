<?php

namespace Scratch\Core\React;

use Scratch\Core\Config\Config;
use Scratch\Core\Kernel\Kernel;

class ReactConfig extends Config {
  public const ROOT_PROJECT = Kernel::APP_ROOT;
  public const CONFIG_FILE_PATH = self::ROOT_PROJECT . '/config/react/react.json';
  public const ROUTER_FOLDER = Kernel::SCRATCH_FOLDER . '/router/';
  public const ROUTES_FILE_NAME = 'routes.json';

  protected $configFile;
  protected $dataBag;

  public function __construct() {
    parent::__construct(self::CONFIG_FILE_PATH);
  }

  public function geOutputClientEntry() {
    return $this->get('client.output.name');
  }
  public function getOutputServerEntry() {
    return $this->get('server.output.name');
  }
  public function getOutputStyles() {
    return $this->get('client.output.styles');
  }
  public function getOutputTemplate() {
    return $this->get('react.output.template');
  }
  public function getDevServerURL() {

    return $this->get('client.dev.url');
  }
  public function getFolder() {
    return $this->get('folder');
  }
}
