<?php

namespace Router;

class Router
{
    /**
     *  Collection of routes
     */
    private $routes;

    public function __construct()
    {
        $this->routes = array();
    }

    private static function error()
    {
        echo 'No such route';
    }

    /**
     * Adds a new route to the collection
     */
    public function addRoute($route, callable $callable)
    {
        $route = str_replace(array('/', '?'), array('\/', '\?'), $route);
        $this->routes['/' . $route . '$/i'] = $callable;
    }

    /**
     * Executes a route
     */
    public function execute()
    {
        // another way to nginx work too
        $requri = $_SERVER['REQUEST_URI'];
        $scrname = $_SERVER['SCRIPT_NAME'];
        $route = substr($requri, stripos($scrname, '/backend/') + 1);
        foreach ($this->routes as $pattern => $callable) {
            if (preg_match_all($pattern, $route, $matches)) {
                array_shift($matches);
                $params = array();
                foreach($matches as $match){
                    if (array_key_exists(0, $match)){
                        $params[] = $match[0];
                    }
                }

                return call_user_func_array($this->routes[$pattern], $params);
            }
        }

        return self::error();
    }
}