<?php

namespace Scratch\Core\Kernel;

class Container {

  private $depsInstances = [];
  private $mapper = [];
  private DependencyInjection $dependencyInjector;

  public function __construct() {
    $this->dependencyInjector = new DependencyInjection();
    $this->depsInstances['Scratch\\Core\\Kernel\\Container'] = $this;
  }
  public function addDependencies(string $className, $name = null): object|null {

    if (!class_exists($className)) {
      return null;
    }

    $deps = $this->dependencyInjector->getConstructorDependencies(
      $className,
      $this->depsInstances
    );
    $depsInstances = $deps && count($deps) > 0 ? $deps : []; 
    $object = new ($className)(...$depsInstances);
    $this->depsInstances[$className] = $object;
    if ($name) {
      $this->mapper[$name] = $className;
    }
    return $object;
  }

  public function loadDependencies(string $className) {
  }

  /**
   * Get the value of depsInstances
   */
  public function getDepsInstances() {
    return $this->depsInstances;
  }
  /**
   * Get the value of depsInstances
   */
  public function getDepInstance(string $className, string $name = null): object|null {
    if (isset($this->depsInstances[$className])) {
      
      if ($name && !isset($mapper[$name])) {
        $this->mapper[$name] = $className;
      }
      
      return $this->depsInstances[$className];
    }
    // If the instance doesn't exist, create it and store it in the depsInstances array
    $object = $this->addDependencies($className, $name);
    return $object;
  }

  /**
   * Get the value of an instance by name
   */
  public function getDepInstanceByName(string $name): object|null {
    if (isset($this->mapper[$name])) {
      return $this->getDepInstance($this->mapper[$name]);
    }
    return null;
  }
}
