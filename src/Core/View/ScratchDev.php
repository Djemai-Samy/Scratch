<?php

namespace Scratch\Core\View;

use Scratch\Core\HTTP\Request;
use Scratch\Core\Logger\Logger;
use Scratch\Core\View\Scripts;
use Scratch\Core\View\Styles;
use Scratch\Core\View\View;
use Scratch\Core\Config\Config;
use Scratch\Core\Controller;
use Scratch\Core\Env\Env;
use Scratch\Core\Kernel\Container;
use Scratch\Core\Kernel\Kernel;
use Scratch\Core\React\React;

class ScratchDev extends View {

  private Styles $styles;
  private Scripts $scripts;

  private ?View $view = null;
  private string $viewClass = '';
  
  private ?Controller $controller= null;
  private $controllerClass = '';

  function __construct(
    private Request $req,
    private Logger $log,
    private React $react,
    private Container $container
  ) {

 

    $this->styles = new Styles();
    $this->scripts = new Scripts();

    $this->styles->set(
      'container',
      [
        'position' => 'sticky',
        'background-color' => 'hsla(0, 0%, 0%, 0.7)',
        'padding' => '0px',
        'bottom' => '0px',
        "z-index" => "9",
        'backdrop-filter' => 'blur(5px)',
        'color'=>"white",
        "font-family"=> "Inter, system-ui, Avenir, Helvetica, Arial, sans-serif"
        
      ]
    );

    $this->styles->set(
      'message-container',
      ['overflow' => 'clip',
      'text-align' => 'center',]
    );

    $this->styles->set(
      'data-container',
      ['height' => '0vh', 'overflow' => 'clip']
    );
    $this->styles->set(
      'arrow',
      ['position' => 'sticky', 'bottom' => '10px', 'left' => '100%', "z-index" => "10", "width" => "fit-content"]
    );

    $this->scripts->set(
      'dumper',
      <<<JS
      function dump(variable, name, nestedLevel){
        element = '';
        element += '<div class="dump">';
        element += '<div class="dump-header">';
        element += '<span class="dump-toggle">▶</span>';
        element += '<span class="dump-type">' + (name == '' ? typeof variable : name) + '</span>';
        element += '</div>';

        element += '<div class="dump-content" style="display: none;">';
        element += dumpVariable('', variable, nestedLevel);
        element += '</div>';

        element += '</div>';

        return element;
      }

      function dumpVariable(element, variable, nestedLevel) {
        if (Array.isArray(variable) || typeof variable === 'object') {
          for (const key in variable) {
              const value = variable[key];
              const valueType = typeof value;
              if (!Array.isArray(value) && typeof value !== 'object') {
                element += '<div class="dump-indent">';
                element += '<span class="dump-key">'+key+'</span> : ';
                element += '<span class="dump-value">'+value+'</span>';
                element += '<span class="dump-type">  ('+valueType+')</span>';
                element += '</div>';
                continue;
              }

              const maxHeight = nestedLevel >= 1 ? '50vh' : 'none';
              element += '<div class="dump-indent">';
              element += '<span class="dump-key dump-header">'+key+' <span class="dump-toggle">▶</span></span>';
              element += '<div id="'+nestedLevel+'" class="dump-content" style="max-height:'+maxHeight+'; display: none;">';
              element += dumpVariable('', value, nestedLevel + 1);
              element += '</div>';
              element += '</div>';
          }
        } else {
          element += '<div class="dump-value">'+variable+'</div>';
        }
        return element;
      }
      JS
    );

    $this->scripts->set(
      'fetcher',
      <<<JS
        // Store the original fetch function reference
        const originalFetch = window.fetch;

        // Override the fetch function
        window.fetch = function(url, options) {
          // Perform any pre-request operations here (if needed)
          const requestDiv = document.createElement('ul');

          const urlTitle = document.createElement('h3');
          urlTitle.textContent = 'URI: '+ url;
          const method = document.createElement('h3');
          method.textContent = 'Method: '+ ( options?.method ? options.method : 'GET');

          requestDiv.append(urlTitle, method)
          // Logging the request URL and options (for demonstration purposes)
          //console.log('Fetching:', url);
          //console.log('Options:', options);
          
          

          // Make the actual fetch call
          return originalFetch.apply(this, arguments)
            .then(response => {
              // Perform any post-request operations here (if needed)
              
              const resp = response.clone();
              // Read the response body as JSON and handle the data
              resp.json().then((data) => {
                // Logging the response (for demonstration purposes)
                const containerElement = document.getElementById('client-data');
                const dataDiv = document.createElement('div');
                dataDiv.innerHTML = dump(data, 'Response Data', 0);
                requestDiv.append(dataDiv, document.createElement('hr'));
                containerElement.append(requestDiv);
                addEvents();

                
              });
              // Return the modified response to keep the promise chain intact
              return response;
            })
            .catch(error => {
              // Handle fetch errors here (if needed)
              console.error('Fetch Error:', error);
              throw error;
            });
        };
      JS
    );

    $this->scripts->set(
      'onValidate',
      <<<JS
        document.querySelector("#toggle").addEventListener('click', (e)=>{
          
          const container = document.querySelector("#dev-message");
          const isClosed = container.style.height == "0px";
          container.style.height = isClosed ? "auto" : "0px"; 
          e.target.textContent = isClosed ? '❌' : '🛠️';
        });

        document.querySelector("#arrow").addEventListener('click', (e)=>{
          
          const container = document.querySelector("#dev-data");
          const isClosed = container.style.height == "0vh";
          container.style.height = isClosed ? "100vh" : "0vh"; 
          container.style.overflowY = isClosed ? "auto" : "clip"; 
          e.target.style.transform = isClosed ? 'rotate(0deg)' : 'rotate(180deg)';

          document.body.style.overflowY = isClosed ? "clip" : "";
        });

        function toggleData(){
          const content = this.nextElementSibling;
          content.style.display = content.style.display === 'none' ? 'block' : 'none';
          const arrow = this.querySelector('.dump-toggle');
          arrow.textContent = content.style.display === 'none' ? '▶' : '▼';
        }

         function addEvents() {
            const headers = document.querySelectorAll('.dump-header');

            headers.forEach(header => {
                header.removeEventListener('click', toggleData)
                header.addEventListener('click', toggleData);
            });
        };
        const observeUrlChange = () => {
        let oldHref = document.location.href;
        const body = document.querySelector("body");
        const observer = new MutationObserver(mutations => {
          if (oldHref !== document.location.href) {
            oldHref = document.location.href;
            document.querySelector('#client-request').textContent = 'URI: '+document.location.pathname
          }
        });
        observer.observe(body, { childList: true, subtree: true });
      };

      window.onload = observeUrlChange;
      addEvents();
      JS
    );
  }

  function getStatusMessage() {
    if (!file_exists(Kernel::APP_ROOT . '/.scratch/entries/' . $this->react->getServerEntryName())) {
      return 'There\'s no server entry script for SSR, try "scratch start" or "scratch build" commands!';
    }
    if (@get_headers($this->react->getClientEntryUrl())) {
      return 'Your React application is Live, Hot Reloading is working in the front!';
    }

    if (!file_exists(Kernel::APP_ROOT . 'public/' . $this->react->getClientEntryName())) {
      return 'Your React application is Not Built, try "scratch start" or "scratch build" commands!"';
    } else {
      return 'Your React application is built but Not Live, Front end changes wont update!';
    }
  }

  function dump($variable, $name = '', $nestedLevel = 0) {
    $dataAsHtml = '';
    $dataAsHtml .= '<div class="dump">';
    $dataAsHtml .= '<div class="dump-header">';
    $dataAsHtml .= '<span class="dump-toggle">▶</span>';
    $dataAsHtml .= '<span class="dump-type">' . ($name == '' ? gettype($variable) : $name) . '</span>';
    $dataAsHtml .= '</div>';

    $dataAsHtml .= '<div class="dump-content" style="display: none;">';
    $this->dumpVariable($dataAsHtml, $variable, $nestedLevel);
    $dataAsHtml .= '</div>';

    $dataAsHtml .= '</div>';

    return $dataAsHtml;
  }

  function dumpVariable(&$string, $variable, $nestedLevel) {
    // Check if the variable is an array or object

    if (is_array($variable) || is_object($variable)) {
      if (is_object($variable)) $variable = get_object_vars($variable);
      foreach ($variable as $key => $value) {
        if (!is_array($value) && !is_object($value)) {
          $string .= '<div class="dump-indent">';
          $string .= '<span class="dump-key">' . $key . '</span> : ';
          $string .= '<span class="dump-value">' . htmlspecialchars($value) . '</span> ';
          $string .= '<span class="dump-type">(' . gettype($value) . ')</span>';
          $string .= '</div>';
          continue;
        }
        $maxHeight = $nestedLevel >= 1 ? '50vh' : 'none';
        $string .= '<div class="dump-indent" >';
        $string .= '<span class="dump-key dump-header">' . $key . ' <span class="dump-toggle">▶</span></span>';
        $string .= '<div class="dump-content" id="' . $nestedLevel . '" style="max-height:66vh;display: none;">';
        $string .= $this->dumpVariable($string, $value, $nestedLevel + 1);
        $string .= '</div>';

        $string .= '</div>';
      }
    } else {
      // For other data types, simply display the value
      $string .= '<div class="dump-value">' . htmlspecialchars($variable) . '</div>';
    }
  }

  public function setView(View $view) {
   
    $this->view = $view;
    $reflector = new \ReflectionClass($view::class);
    $this->viewClass = $view::class;
    return $this;
  }

  function render($data = []) {
    if (Env::isProd()) return;

    $this->controller = $this->container->getDepInstanceByName('controller');
    $this->view = $this->container->getDepInstanceByName('view');
    $this->controllerClass =  $this->controller ? ($this->controller::class) : null;
    
    return (<<<HTML
      <style>
        @import url("https://fonts.googleapis.com/css2?family=Fira+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap");
        .dump {
            border: 1px solid #ccc;
            padding: 5px;
            margin: 5px;
        }

        .dump-header {
            cursor: pointer;
        }
        .dump-key {
            color: green;
            opacity: 0.9;
            font-size: 0.9em;
        }
        .dump-value {
            color: hsla(0, 0%, 90%, 1);
            font-size: 0.8em;
        }
        .dump-type{
          color:hsla(0, 0%, 70%, 1);;
        }

        .dump-content {
            display: none;
            margin-left: 10px;
            overflow: auto;
            
        }

        .dump-indent {
            margin-left: 10px;
        }

        .dump-toggle {
            margin-right: 5px;
        }

        .dump-arrow {
            cursor: pointer;
        }
      </style>

      <div style="{$this->styles->get('arrow')}">
        <p id="arrow">🔽</p>
        <p id="toggle">❌</p>
      </div>
     
      <div style="{$this->styles->get('container')}">
        <div id="dev-message" style="{$this->styles->get('message-container')}"  >
          <p>Scratch Dev mode</p>
          <p  >{$this->getStatusMessage()}</p>
          <hr>
          <p > Controller : {$this->controllerClass}</p>
          <p > View : {$this->viewClass}</p>
        </div>

        <div id="dev-data" style="{$this->styles->get('data-container')}">
          <div>
            <h2>Server Request: </h2>
            <ul>
              <li >URI: {$this->req->getUri()}</li>
              <li  >METHOD: {$this->req->getMethod()}</li>
              {$this->dump($this->req, 'Request Data: ')}
            </ul>
            <div>
              <h2>Initial Data: </h2>
              <ul>
                {$this->dump($this->react->getData())}
              </ul>
            </div>  
          </div>  
          <hr>
          <div>
            <h2>Client Navigation: </h2>
            <ul>
              <li  id='client-request'>URI: {$this->req->getUri()}</li>
            </ul>
          </div>
          <div>
            <h2>Client Requests: </h2>
            <ul id='client-data'></ul>
          </div>
        </div>
        
      </div>
      {$this->scripts->dumpAll()}
      HTML);
  }
}
