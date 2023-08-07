<?php

namespace Scratch\Core\React;

use Exception;
use Scratch\Core\DataBag;
use Scratch\Core\HTTP\Request;
use Scratch\Core\Logger\Logger;

class React {


  /**
   * html template filename.
   *
   * @var string
   */
  private $template_name;
  /**
   * html template filename.
   *
   * @var string
   */
  private $template_url;

  /**
   * server script entry filename.
   *
   * @var string
   */
  private $server_entry_name;
  /**
   * server script entry filename.
   *
   * @var string
   */
  private $server_entry_url;

  /**
   * client script entry filename.
   *
   * @var string
   */
  private $client_entry_name;
  /**
   * client script entry filename.
   *
   * @var string
   */
  private $client_entry_url;

  /**
   * CSS styles filename.
   *
   * @var string
   */
  private $styles_name;
  /**
   * CSS styles filename.
   *
   * @var string
   */
  private $styles_url;

  /**
   * Data for initial props.
   *
   * @var array
   */
  private $data = [];

  /**
   * Data in jSON format for initial props.
   *
   * @var string
   */
  private $json_data;

  /**
   * Encoded Data for intial props
   *
   * @var string
   */
  private $base64_data;

  /**
   * HTML data manipluation with helmet
   *
   */
  private $helmet;

  /**
   * Strinf representation of rendered React App
   *
   */
  private $app;

  function __construct(private Request $req, private ReactNavigation $navigation, private Logger $log) {
    $config = new ReactConfig();



    $this->template_name = $config->getOutputTemplate();
    $this->template_url = $this->template_name;

    $this->client_entry_name = $config->geOutputClientEntry();
    $this->client_entry_url = $this->client_entry_name;

    $this->server_entry_name = $config->getOutputServerEntry();
    $this->server_entry_url = $this->server_entry_name;

    $this->styles_name = $config->getOutputStyles();
    $this->styles_url = $this->styles_name;

    if (getenv('APP_MODE') == 'development' && $this->isServerLive($config->getDevServerURL() . "/" . $this->client_entry_url)) {

      $this->template_url = $config->getDevServerURL() . "/" . $this->template_name;
      $this->client_entry_url = $config->getDevServerURL() . "/" . $this->client_entry_name;
      $this->styles_url = $config->getDevServerURL() . "/" . $this->styles_name;
    }

    $this->helmet = new DataBag();
  }

  function render($data) {



    $data = json_encode(['url' => $this->req->getUri(), 'data' => $data]);
    if ($this->req->acceptJson()) {
      return $data;
    }
    $dataEsc = base64_encode($data);

    $ret = exec('node ./' . $this->server_entry_name . " \"$dataEsc\"", $out, $res);

    //read the entire string
    $str = $this->getTemplateContent();
    $str = str_replace('<ssrdata></ssrdata>', "<script>const data='$dataEsc'</script>", $str);
    $str = str_replace('<app></app>', $ret, $str);

    return $str;
  }

  function getStringSSR($data = []) {

    if ($this->req->acceptJson()) {
      echo $this->getJsonData();
      die();
    }
    $dataEsc = $this->getBase64Data();

    if (!$this->server_entry_name) return null;

    $ret = exec('node ../.scratch/entries/' . $this->server_entry_name . " \"$dataEsc\"", $out, $res);


    $this->helmet->setData(json_decode($out[count($out) - 2], true));
    return $ret;
  }

  function renderReactApp($data = []) {

    if ($this->req->acceptJson()) {
      echo $this->getJsonData();
      die();
    }
    $dataEsc = $this->getBase64Data();

    $this->app = exec('node ../.scratch/entries/' . $this->server_entry_name . " \"$dataEsc\"", $out, $res);

    if ($this->app === '') return  $this;

    $headers = json_decode($out[count($out) - 2], true);

    if ($headers) $this->helmet->setData(json_decode($out[count($out) - 2], true));

    return $this;
  }

  private function encodeDataToBase64() {
    if (!$this->getJsonData()) {
      $this->json_data = $this->encodeDataToJson();
    }
    return base64_encode($this->json_data);
  }

  private function encodeDataToJson() {
    return json_encode(['url' => $this->req->getUri(), 'data' => $this->getData()]);
  }

  /**
   * Get the value of template_name
   */
  public function getTemplateName() {
    return $this->template_name;
  }

  public function getTemplateContent() {
    if (file_exists($this->getTemplateName())) {
      return file_get_contents($this->getTemplateName());
    }

    throw new Exception("Template not found!", 404);
  }

  /**
   * Get the value of server_entry_name
   */
  public function getServerEntryName() {
    return $this->server_entry_name;
  }

  /**
   * Get the value of client_entry_name
   */
  public function getClientEntryName() {
    return $this->client_entry_name;
  }

  /**
   * Get the value of styles_name
   */
  public function getStyles() {
    return $this->styles_name;
  }

  /**
   * Get the value of data
   */
  public function getData() {
    return $this->data;
  }

  /**
   * Set the value of data
   */
  public function setData($data): self {
    $this->data = $data;

    $this->json_data = $this->encodeDataToJson();
    $this->base64_data = $this->encodeDataToBase64();
    return $this;
  }

  /**
   * Get the value of base64_data
   */
  public function getBase64Data() {
    return $this->base64_data;
  }


  /**
   * Get the value of json_data
   */
  public function getJsonData() {
    return $this->json_data;
  }

  function isServerLive($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($status === 200);
  }

  /**
   * Get the value of server_entry_url
   */
  public function getServerEntryUrl() {
    return $this->server_entry_url;
  }

  /**
   * Get the value of client_entry_url
   */
  public function getClientEntryUrl() {
    return $this->client_entry_url;
  }

  /**
   * Get the value of styles_url
   */
  public function getStylesUrl() {
    return $this->styles_url;
  }

  /**
   * Get the value of template_url
   */
  public function getTemplateUrl() {
    return $this->template_url;
  }

  /**
   * Get the value of helmet
   */
  public function getHelmet() {
    return $this->helmet;
  }

  /**
   * Set the value of helmet
   */
  public function setHelmet($helmet): self {
    $this->helmet = $helmet;

    return $this;
  }
  /**
   * Get the value of helmet
   */
  public function getApp() {
    return $this->app;
  }

  /**
   * Set the value of helmet
   */
  public function setApp($app): self {
    $this->app = $app;

    return $this;
  }
}
