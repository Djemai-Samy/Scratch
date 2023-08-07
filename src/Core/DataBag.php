<?php

namespace Scratch\Core;

class DataBag {
  protected $data = [];

  public function __construct(array $data = []) {
   $this->setData($data);
  }

  public function setData($data) {
    foreach ($data as $key => $value) {
      if (is_array($value)) {
        $this->data[$key] = new self($value);
      } else {
        $this->data[$key] = $value;
      }
    }
  }

  public function get($key, $default = null) {
    $keys = explode('.', $key);
    $value = $this->data;

    foreach ($keys as $innerKey) {
      if ($value instanceof self && $value->has($innerKey)) {
        $value = $value->get($innerKey);
      } elseif (is_array($value) && array_key_exists($innerKey, $value)) {
        $value = $value[$innerKey];
      } else {
        return $default;
      }
    }

    return $value;
  }



  public function set($key, $value) {
    $keys = explode('.', $key);
    $current = &$this->data;

    foreach ($keys as $innerKey) {
      if (!isset($current[$innerKey]) || !is_array($current[$innerKey])) {
        $current[$innerKey] = [];
      }
      $current = &$current[$innerKey];
    }

    $current = $value;
  }

  public function has($key) {
    return array_key_exists($key, $this->data);
  }

  public function all() {
    return $this->data;
  }
}
