<?php

namespace Scratch\Core\View;


abstract class View {


  public function __construct() {
  }

  public function renderView(string|array|null $data = []) {

    return $this->render($data);
  }

  abstract protected function render(string|array|null $data = []);


  function useLayout(View $layout, string|array|null $content = []) {
    return $layout->render($content);
  }
}
