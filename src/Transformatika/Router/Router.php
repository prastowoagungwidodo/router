<?php
namespace Transformatika\Router;

use Transformatika\Config\Config;

class Router
{

    protected $controller = '';

    protected $method = '';

    protected $args = '';

    protected $defaultController;

    protected $defaultMethod;

    function __construct()
    {
        if ($this->defaultController === null) {
            $config = new Config();
            $defaultRoute = $config->getConfig('application/defaultRoute');
            $arrayRoute = explode('/', $defaultRoute);
            $this->defaultController = $arrayRoute[0];
            $this->defaultMethod = $arrayRoute[1];
        }
        
        if (isset($_SERVER['QUERY_STRING'])) {
            $this->pathRoute($_SERVER['QUERY_STRING']);
        } else {
            $this->defaultRoute();
        }
    }

    /**
     * Sets the default controller and method defined in the config
     *
     * @return void
     */
    public function defaultRoute()
    {
        $this->controller = $this->defaultController;
        $this->method = $this->defaultMethod;
    }

    /**
     * Sets the controller and method depending on the path
     *
     * @return void
     */
    private function pathRoute($uri = '')
    {
        $parts = trim($uri, '/');
        // Explode the url
        $parts = explode('/', $parts);
        $this->segments = $parts;
        
        // The first part of the url is the controller
        $this->controller = array_shift($parts);
        
        // The second part is the controller method
        // we check if it's set
        if (isset($parts[0]))
            // Set the method to the second part
            $this->method = array_shift($parts);
        
        else
            // Default method (index) called if no method specified
            $this->method = $this->defaultMethod;
            
            // Set the args to the rest of the url parts
        $this->args = $parts;
    }

    /**
     * Render Controller
     *
     * @return mixed
     */
    public function render()
    {
        
        if (empty($this->controller)) {
            $this->defaultRoute();
        }
        
        $controller = ucfirst($this->controller).'\\Controller\\'.ucfirst($this->controller).'Controller';
        if (class_exists($controller)) {
            $controller = new $controller();
        } else {
            header('HTTP/1.0 404 Not Found');
            header('Content-Type: text/plain');
            echo "HTTP/1.0 404 Not Found\n";
            echo 'Page Not Found - Transformatika MVC';
            exit();
        }
        
        $method = $this->method;
        
        // Check if the method exists in the controller
        if (method_exists($controller, $method)) {
            
            // Call the method giving the args array
            return call_user_func_array(array(
                $controller,
                $method
            ), $this->args);
        } else {
            header('HTTP/1.0 404 Not Found');
            header('Content-Type: text/plain');
            echo "HTTP/1.0 404 Not Found\n";
            echo 'Page Not Found - Transformatika MVC';
            exit();
        }
    }
}
