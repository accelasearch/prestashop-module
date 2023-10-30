<?php

namespace Accelasearch\Accelasearch\Dispatcher;

class Dispatcher
{
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
        $controller = new $controllerName();
        return $controller;
    }

    private function getControllerName(string $name)
    {
        $controllerName = 'Accelasearch\\Accelasearch\\Controller\\' . ucfirst($name) . 'Controller';
        if (!class_exists($controllerName))
            throw new \Exception('Controller not found: ' . $controllerName);
        return $controllerName;
    }
}