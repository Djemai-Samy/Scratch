<?php

namespace Scratch\Core\Console\Scripts;

use Scratch\Core\Console\Script;

class DevServer {
  function __construct() {
  }

  #[Script(command: 'server:{dev}')]
  function index($dev) {

    $controlFile = tempnam(sys_get_temp_dir(), 'control_');
    file_put_contents($controlFile, "running");

    $data = json_encode(
      [
        'controlFile' => $controlFile,
        'processes' => [
          [
            'name' => 'Server Entry Watch',
            'command' => 'cd ./react && yarn webpack --config webpack.server.config.js --watch --mode=development'
          ],
          [
            'name' => 'Client Entry Watch',
            'command' => 'cd ./react && yarn webpack-dev-server --mode=development'
          ],
        ]
      ]
    );
    $cwd = __DIR__ . '/../';
    // Command to execute for the server watch
    $serverCommand = 'cd ./react && yarn server:watch';
    // Command to execute for the front watch
    $frontCommand = 'cd ./react && yarn front:watch';

    //$this->launchProcess("Front watch", $frontCommand);
    //$this->launchProcess("Server watch", $serverCommand);
    $dataEsc = base64_encode($data);
    // Create a control file to communicate with Node.js processes
    var_dump(sys_get_temp_dir());
    $this->launchProcess("Hot Relowding With SSR", "node $cwd/spawn.js \"$dataEsc\"");
  }

  function launchProcess($name, $command) {
    // Set the current working directory to the root of the project
    $cwd = __DIR__ . '/../';  // Adjust the path according to your project's structure

    // Append "&" to the command to run it asynchronously on Unix-like systems
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      $command = "start /B cmd /C \"$command\"";
    } else {
      // For Unix-like systems, use nohup and & to run the command in the background
      $command = "nohup $command > /dev/null 2>&1 &";
    }

    // Open the process using popen
    $process = popen($command, 'r');

    if (is_resource($process)) {
      echo "$name is running. Press Ctrl + C to stop.\n";

      while (!feof($process)) {
        echo "$name: ";
        echo fread($process, 8192);
        flush();
        usleep(100000); // Sleep for a short time to avoid high CPU usage
      }

      // Get the exit code of the process (not available in popen)
      pclose($process);

      echo "$name process terminated.\n";
    } else {
      echo "Failed to start the process.\n";
    }
  }
}
