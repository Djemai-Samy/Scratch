<?php

namespace Scratch\Core\View;


class Scripts {

  protected $scripts = [];

  public function __construct() {
  }

  /**
   * Get the value of scripts
   */
  public function all() {
    return $this->scripts;
  }

  /**
   * Set the value of scripts
   */
  public function setAll($scripts): self {
    $this->scripts = $scripts;

    return $this;
  }
  /**
   * Get the value of scripts
   */
  public function get(string $key) {
    return $this->scripts[$key];
  }

  /**
   * Set the value of styles
   */
  public function set(string $key, $script): self {

    $this->scripts[$key] = $script;
    return $this;
  }

  /**
   * return on script
   */
  public function dump(string $key): string {
    $script = $this->scripts[$key];
    $this->scripts[$key] = null;
    return '<script>'.$script.'</script>';
  }

  /**
   * Set the value of styles
   */
  public function dumpAll(): string {
    $all = '';
    foreach($this->scripts as $key=>$script){
      $all .= $script;
      $script = null;
    }
    
    return'<script>'.$all.'</script>';
  }


}
