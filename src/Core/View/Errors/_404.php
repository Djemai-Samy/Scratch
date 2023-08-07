<?php

namespace Scratch\Core\View\Errors;

use Scratch\Core\HTTP\Request;
use Scratch\Core\Logger\Logger;
use Scratch\Core\View\Scripts;
use Scratch\Core\View\Styles;
use Scratch\Core\View\View;
use Scratch\Core\React\React;
use Scratch\Core\View\NothingToFound\NothingFound;
use Scratch\Core\View\ScratchDev;

class _404 extends View {

  const NAME = '_404';
  private Styles $styles;
  private Scripts $scripts;

  function __construct(
    private Request $req,
    private Logger $log,
    private React $react,
    private ScratchDev $dev,
    private NothingFound $nothing
  ) {
    $this->styles = new Styles();
    $this->scripts = new Scripts();
  }

  function render($data = []) {

    $this->react->setData($data);
    $react = $this->react->renderReactApp($data);

    $helmet = $react->getHelmet();
    $title = $helmet->get('title') == '' ? '<title>My APP</title>' : $helmet->get('title');
    $app = $react->getApp() != null ? $react->getApp() : $this->nothing->render();
    
    return (<<<HTML
      <!DOCTYPE html>
      <html {$helmet->get('htmlAttributes')} >
        <head>
          {$title}
          <meta name="viewport" content="width=device-width, initial-scale=1.0" />
          {$helmet->get('meta')}
          {$helmet->get('link')}
          <script src="{$this->react->getClientEntryUrl()}" defer></script>
          <link type="text/css" rel="stylesheet" href="{$this->react->getStylesUrl()}" />
        </head>
        <body {$helmet->get('bodyAttributes')}>
          
          <!-- Output the JSON data as a hidden input field or within a JavaScript variable -->
          <input type="hidden" id="jsonData" value="{$this->react->setData($data)->getBase64Data()}">

          <div id="root">{$app}</div>
         
         
          {$this->dev->setView($this)->render()}
        </body>
      </html>
      HTML);
  }
}
