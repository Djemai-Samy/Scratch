<?php

namespace Scratch\Core\View\NothingToFound;

use Scratch\Core\HTTP\Request;
use Scratch\Core\Logger\Logger;
use Scratch\Core\View\Scripts;
use Scratch\Core\View\Styles;
use Scratch\Core\View\View;
use Scratch\Core\FileSystem\FileSystem;
use Scratch\Core\React\React;

class Help extends View {

  const NAME = 'NothingFound';
  private Styles $styles;
  private Scripts $scripts;

  function __construct(
    private Request $req,
    private Logger $log,
    private React $react,
    private FileSystem $fs
  ) {
    $this->styles = new Styles();
    $this->scripts = new Scripts();
  }


  private function getHelp() {
    if ($this->req->getUri() == '/') {
      return (<<<HTML
      <section>
        <h2>Setup your Application</h2>
        <ul class='card'>
          <h3>Scratch With React</h3>

          <p>Add a page:</p>
          <ol>
            <li>
              Adapt your configuration to your folder structure: <code>config/react.json</code>
            </li>
            <li>
              If you are using file based routing:
              <ol>
                <li>
                  Add a your Main Page in the pages folder: <code>_app.jsx</code>.
                </li>
                <li>
                  Add a page in the pages folder: <code>index.jsx</code>
                </li>
              </ol>
            </li>
            <li>
              If you are using Custom Compoenents:
              <ol>
                <li>
                  Add your Main Component to the function <code>renderApp</code> in <code>main.js</code>
                </li>
                <li>
                  Add a route with the path <code>/</code> and your custom component to the routes array in <code>main.js</code>
                </li>
              </ol>
            </li>
          </ol>

          <hr/>

          <p>
            To use React you need to watch or build the react app:
          </p>
          <ol>
            <li>
              Use Scratch-scli: <code>scratch start</code>
            </li>
            <li>
              Or manually watch and build the app with webpack or vite.
            </li>
            <li>
              Dont forget to adapt you config in: <code>config/react.json</code>
            </li>
            <li>
              <pre>
                <code>
                {
                  "react": {
                    "folder": "/src/React/",
                    "output": {
                      "template": "index.html",
                      "server-entry": "server.js",
                      "client-entry": "/client.js",
                      "styles": "/styles.css"
                    },
                    "dev": {
                      "url": "http://localhost:3000"
                    }
                  }
                }
                </code>
              </pre>
            </li>
          </ol>
        </ul>

        <hr/>

        <ul class='card'>
          <h3>Scratch for Web</h3>
          <ol>
            <li>
              Add a controller: <code>src/Controllers/YourController.php</code>
            </li>
            <li>
              Give him a namespace folowing your folder structure: <code>App\Controllers</code>;
            </li>
            <li>
              Use Atributes route to defclare actions: <code>#[Route('/', ['GET'])]</code>
            </li>
            <li>
              Return a Response or use the method <code>render</code> to render a <code>View</code> by extending <code>AbstractController</code>.
            </li>
          </ol>
        </ul>

        <hr/>

        <ul class='card'>
          <h3>Scratch for Restful API</h3>
          <ol>
            <li>
              Add a controller: <code>src/Controllers/YourController.php</code>
            </li>
            <li>
              Give him a namespace folowing your folder structure: <code>App\Controllers</code>;
            </li>
            <li>
              Use Atributes route to defclare actions: <code>#[Route('/', ['GET'])]</code>
            </li>
            <li>
              Return a JsonReponse or use the method <code>json</code> by extending </code>AbstractController</code>.</li>
          </ol>
        </ul>

      </section>

    HTML);
    }
    return (<<<HTML
    <section>
      <ul class='card'>
        <h3>Scratch With React</h3>
        <p>404 pages are handled by React:</p>
        <ol>
          <li>
            If you are using file based routing add a file in the pages folder: <code>_404.jsx</code>
          </li>
          <li>
            Or add a route with the path *</code> and your custom component in <code>main.js</code>
          </li>
        </ol>
        
        <hr>

        <p>
          You also have to watch or build the app:
        </p>
        <ol>
          <li>
            Use Scratch-scli: <code>scratch start</code>
          </li>
          <li>
            Or manually watch and build the app with webpack or vite.
          </li>
          <li>
            Dont forget to adapt you config in: <code>config/react.json</code>
          </li>
        </ol>
      </ul>

      <hr>

      <ul class='card'>
        <h3>Scratch for Web</h3>
        <ol>
          <li>
            Add a 404 view to be shown: <code>src/View/Errors/_404.php</code>
          </li>
          <li>
            You can also Add a 404 controller to handle 404 routes: <code>src/Controllers/Error/_404.php</code>
          </li>
        </ol>
      </ul>

      <hr>

      <ul class='card'>
        <h3>Scratch for Restful API</h3>
        <ol>
          <li>
            Add a 404 controller to handle 404 routes: <code>src/Controllers/Error/_404.php</code>
          </li>
        </ol>
      </ul>
    </section>

  HTML);
  }

 

  function render($data = []) {
    return (<<<HTML
      {$this->getHelp()}
    HTML);
  }
}
