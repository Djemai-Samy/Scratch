<?php

namespace Scratch\Core\View;


class Styles {

  protected $styles = [];

  public function __construct() {
  }


  private function generateStyles($styles) {
    foreach ($styles as $class => $styles) {
      $string = '';
      foreach ($styles as $prop => $value) {
        str_replace(' ', '', $value);
        $string .= $prop . ':' . $value . ';';
      }
      $this->styles[$class] = $string;
    }
  }

  /**
   * Get the value of styles
   */
  public function all() {
    return $this->styles;
  }

  /**
   * Set the value of styles
   */
  public function setAll($styles): self {
    $this->styles = $styles;

    return $this;
  }
  /**
   * Get the value of styles
   */
  public function get(string $key) {
    return $this->styles[$key];
  }

  /**
   * Set the value of styles
   */
  public function set(string $key, array $styles): self {
    $stylesString = '';
    foreach ($styles as $prop => $value) {
      $stylesString .= $prop . ':' . str_replace(' ', '', $value) . ';';
    }
    $this->styles[$key] = $stylesString;
    return $this;
  }
}
