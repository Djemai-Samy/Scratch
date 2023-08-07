<?php

namespace Scratch\Core\Kernel;

use Exception;
use LogicException;
use ReflectionClass;
use ReflectionException;

class DependencyInjection {

  public function __construct() {
  }

  public function getConstructorDependencies($class, array &$dependenciesInstances) {

    try {
      // Retrieve the class constructor
      $reflectionClass = new ReflectionClass($class);
      $constructor = $reflectionClass->getConstructor();

      if ($constructor !== null) {
        // Get the constructor parameters
        $parameters = $constructor->getParameters();
        $dependencies = [];

        foreach ($parameters as $parameter) {
          $parameterType = $parameter->getType();

          if ($parameterType !== null && !$parameterType->isBuiltin()) {

            $dependencyClassName = $parameterType->getName();
            if (!isset($dependenciesInstances[$dependencyClassName])) {
              $deps = $this->getConstructorDependencies($dependencyClassName, $dependenciesInstances);
              $dependencyInstance = new $dependencyClassName(...$deps);
              // Recursively check for dependencies of the constructor's parameters
              $dependencies[] = $dependencyInstance;
              $dependenciesInstances[$dependencyClassName] = $dependencyInstance;
            } else {
              $dependencies[] = $dependenciesInstances[$dependencyClassName];
            }
          } else {
            // Check if the parameter has a default value
            if ($parameter->isDefaultValueAvailable()) {
              // Use the default value
              $dependencies[] = $parameter->getDefaultValue();
            } else {
              // Handle non-class constructor parameters
              // You can customize this part based on your specific requirements
              // In this example, we assume the parameter is provided at object instantiation
              $parameterName = $parameter->getName();
              $parameterValue = $_GET[$parameterName] ?? null; // Assuming the parameter value is obtained from the request query parameters ($_GET)

              if ($parameterValue !== null) {
                $dependencies[] = $parameterValue;
              } else {
                // Parameter value not provided, throw an exception or handle it based on your requirements
                throw new Exception("Parameter '$parameterName' value not provided for class '$class'");
              }
            }
          }
        }

        // Create an instance of the class with resolved dependencies and provided parameters
        return $dependencies;
      }
    } catch (LogicException $Exception) {
      die('Dependency Injection. Not gonna make it in here...');
    } catch (ReflectionException $Exception) {
      return null;
    }
  }
}
