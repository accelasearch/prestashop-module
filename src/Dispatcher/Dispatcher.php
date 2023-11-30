<?php

namespace Accelasearch\Accelasearch\Dispatcher;

class Dispatcher
{

    private $module;
    public function __construct(\Module $module)
    {
        $this->module = $module;
    }

    /**
     * Handles the request by getting the controller for the given name and calling its handleRequest method.
     * 
     * It call the method handleRequest of class named Accelasearch\Accelasearch\Controller\{name}Controller
     *
     * @param string $name The name of the controller to handle the request.
     * @return void
     */
    public function handleRequest(string $name)
    {
        $controller = $this->getController($name);
        $controller->handleRequest();
    }

    private function getController(string $name)
    {
        $name = str_replace('ajaxProcess', '', $name);
        $controllerName = $this->getControllerName($name);
        $controller = new $controllerName($this->module);
        return $controller;
    }

    private function getControllerName(string $name)
    {
        $controllerName = 'Accelasearch\\Accelasearch\\Controller\\' . ucfirst($name) . 'Controller';
        $controllerTestName = 'Accelasearch\\Accelasearch\\Controller\\Test\\' . ucfirst($name) . 'Controller';
        if (!class_exists($controllerName) && !class_exists($controllerTestName))
            throw new \Exception('Controller not found: ' . $controllerName);
        return class_exists($controllerName) ? $controllerName : $controllerTestName;
    }
}