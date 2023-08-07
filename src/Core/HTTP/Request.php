<?php

namespace Scratch\Core\HTTP;

class Request {
  public $method;
  public $uri;
  public $headers;
  public $parameters;
  public $cookies;
  public $files;

  public function __construct() {
    $this->method = $GLOBALS['_SERVER']['REQUEST_METHOD'];
    $this->uri = $GLOBALS['_SERVER']['REQUEST_URI'];
    $this->headers = $GLOBALS['_SERVER'];
    $this->parameters = $GLOBALS['_GET'];
    $this->cookies = $GLOBALS['_COOKIE'];
    $this->files = $GLOBALS['_FILES'];
  }

  public function getMethod() {
    return $this->method;
  }

  public function getUri() {
    return $this->uri;
  }

  public function getHeader($name) {
    return $this->headers[$name] ?? null;
  }

  public function getHeaders() {
    return $this->headers;
  }

  public function getParameter($name) {
    return $this->parameters[$name] ?? null;
  }

  public function getParameters() {
    return $this->parameters;
  }

  public function getCookie($name) {
    return $this->cookies[$name] ?? null;
  }

  public function getCookies() {
    return $this->cookies;
  }
  public function getFiles() {
    return $this->files;
  }

  function acceptJson(){
    return $this->headers['HTTP_ACCEPT'] == 'application/json';
  }
}
