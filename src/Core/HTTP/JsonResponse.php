<?php

namespace Scratch\Core\HTTP;

class JsonResponse extends Response {

  function __construct($content = [], $statusCode = 200, $headers = [], $cookies = []){
    parent::__construct(json_encode($content), $statusCode, $headers, $cookies);
    if(!isset($this->headers['Content-Type'])) {
      $this->headers['Content-type'] = 'application/json';
    }
    $this->setData($content);
  }

}
