<?php

namespace Scratch\Core\Controller\Errors;

use Scratch\Core\Controller;
use Scratch\Core\HTTP\Response;
use Scratch\Core\View\PageView;

class _404 extends Controller {
  function __construct() {
  }
  public function index(): Response {
    return $this->render();
  }
}
