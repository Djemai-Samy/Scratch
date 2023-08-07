<?php
namespace Scratch\Core\Config;

use Exception;
use Scratch\Core\DataBag;

class Config
{

    protected $configFile;
    protected $dataBag;

    public function __construct($configFile)
    {
        $this->configFile = $configFile;
        $data = $this->read();
        
        $this->dataBag = new DataBag($data ? $data : []);
    }

    public function read()
    {
        if (!file_exists($this->configFile)) {
          return null;
        }

        $configData = file_get_contents($this->configFile);
        return json_decode($configData, true);
    }

    public function write(array $data)
    {
        $jsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($this->configFile, $jsonData);
        $this->dataBag = new DataBag($data);
    }

    public function get($key, $default = null)
    {
        return $this->dataBag->get($key, $default);
    }

    public function set($key, $value)
    {
        $this->dataBag->set($key, $value);
    }

    public function all()
    {
        return $this->dataBag->all();
    }
}
