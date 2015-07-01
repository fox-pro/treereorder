<?php

namespace DI;

class DependencyInjection
{
    private $container;

    public function __construct()
    {
        $this->container = DependencyContainer::getInstance();
    }

    /**
     * Adds a service to DI
     *
     * @param string $ident
     * @param callable $generator
     */
    public function add($ident, callable $generator)
    {
        $this->container->add($ident, $generator);
    }

    /**
     * Gets the dependency by ident
     *
     * @param string $ident
     */
    public function get($ident)
    {
        return $this->container->get($ident);
    }
}