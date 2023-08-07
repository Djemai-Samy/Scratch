<?php
function dd($data) {
  dump($data);
  die();
}

function dump($data) {
  if (!($_SERVER['HTTP_ACCEPT'] == "application/json")) {
    var_dump($_SERVER['HTTP_ACCEPT']);
    echo "<pre>";
    var_dump($data);
    echo "</pre>";
  }
}
