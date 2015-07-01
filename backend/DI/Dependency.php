<?php

namespace DI;

class Dependency
{
    private $generator;
    private $object;

    public function __construct(callable $generator)
    {
        $this->generator = $generator;
    }

    public function get()
    {
        if ($this->object === null) {
            $this->object = call_user_func($this->generator);
        }

        return $this->object;
    }
}