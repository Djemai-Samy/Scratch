<?php

namespace Scratch\Core;

class Route {

  public function __construct(public string $routePage, public string $method = 'get') {
  }
}
