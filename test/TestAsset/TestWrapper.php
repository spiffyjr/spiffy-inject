<?php

namespace Spiffy\Inject\TestAsset;

use Spiffy\Inject\Injector;
use Spiffy\Inject\ServiceWrapper;

class TestWrapper implements ServiceWrapper
{
    /**
     * {@inheritDoc}
     */
    public function wrapService(Injector $i, $name, $callable)
    {
        $instance = $callable();
        $wrapped = new \StdClass();
        $wrapped->original = $instance;
        $wrapped->name = $name;
        $wrapped->didItWork = true;

        return $wrapped;
    }
}
