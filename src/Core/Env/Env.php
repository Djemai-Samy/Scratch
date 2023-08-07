<?php

namespace Scratch\Core\Env;

use Exception;
use Scratch\Core\DataBag;

class Env {

  private const APP_MODE = 'APP_MODE';
  private const DEV_MODE = 'development';
  private const PROD_MODE = 'production';

  public function __construct() {
  }

  static public function getAppMode() {
    return getenv(static::APP_MODE);
  }


  static public function isProd() {
    return static::getAppMode() == static::PROD_MODE;
  }

  static public function isDev() {
    return static::getAppMode() == static::DEV_MODE;
  }
}
