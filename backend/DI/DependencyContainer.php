<?php

namespace DI;

class DependencyContainer
{
    private static $instance;

    /**
     * Collection of dependencies
     */
    private $dependencies;

    private function __construct()
    {
        $this->dependencies = array();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private static function error()
    {
        echo 'No such dependency';
    }

    /**
     * Adds a dependency to collection
     *
     * @param string $ident
     * @param callable $generator
     */
    public function add($ident, callable $generator)
    {
        $this->dependencies[$ident] = new Dependency($generator);
    }

    /**
     * Gets a dependency by ident
     *
     * @param string $ident
     */
    public function get($ident)
    {
        return isset($this->dependencies[$ident]) ? $this->dependencies[$ident]->get() : self::error();
    }
}