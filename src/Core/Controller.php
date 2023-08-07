<?php

namespace Scratch\Core;

use Scratch\Core\HTTP\Response;
use Scratch\Core\View\View;

class Controller {

  protected  $defaultView = null;

  public function __construct() {
  }
  public function render(array $data = [], View $view = null,): Response {
    foreach ($data as $key => $value) {
      ${$key} = $value;
    }
    $view = $view ? $view :  $this->defaultView;
    return (new Response($view->renderView($data)))->setData($data);
  }

  function json($data) {
    return (new Response(json_encode($data)))->setData($data);;
  }

  function send($content, $status = 200, $headers = [], $cookies = []) {
    return new Response($content, $status, $headers, $cookies);
  }

  public function getDefeultView() {
    return $this->getDefeultView();
  }

  public function setDefaultView(View $view) {
    $this->defaultView = $view;
    return $this;
  }
}
