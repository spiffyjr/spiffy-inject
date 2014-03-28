<?php

namespace Spiffy\Inject\TestAsset;

use Spiffy\Inject\Injector;
use Spiffy\Inject\ServiceFactory;

class TestFactory implements ServiceFactory
{
    /**
     * {@inheritDoc}
     */
    public function createService(Injector $i)
    {
        return new \StdClass();
    }
}
