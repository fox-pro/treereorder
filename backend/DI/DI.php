<?php

namespace DI;

class DI
{
    protected $di;

    public function __construct()
    {
        $this->di = new DependencyInjection();
    }
}