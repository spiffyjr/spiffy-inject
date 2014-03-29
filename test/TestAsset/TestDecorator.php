<?php

namespace Spiffy\Inject\TestAsset;

use Spiffy\Inject\Injector;
use Spiffy\Inject\ServiceDecorator;

class TestDecorator implements ServiceDecorator
{
    /**
     * {@inheritDoc}
     */
    public function decorateService(Injector $i, $instance)
    {
        $instance->didItWork = true;
    }
}
