<?php

namespace Scratch\Core\Console\Scripts;

use Scratch\Core\Console\Script;

class _404  {

  function __construct(){}
  #[Script(command:'server:dev')]
  function index(){
    echo 'HERE';
  }
}
