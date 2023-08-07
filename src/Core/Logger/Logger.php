<?php

namespace Scratch\Core\Logger;

use Scratch\Core\HTTP\Request;

class Logger {

  function __construct(private Request $req) {
  }

  function dd($data) {
    $this->dump($data);
    die();
  }

  function dump($data) {
    if (!$this->req->acceptJson()) {
      echo "<pre>";
      var_dump($data);
      echo "</pre>";
    }
  }
}
