<?php

namespace Spiffy\Inject\Loader;

use Spiffy\Inject\Injector;
use Spiffy\Inject\Metadata\Metadata;

interface Loader
{
    /**
     * @param \Spiffy\Inject\Injector $i
     * @param \Spiffy\Inject\Metadata\Metadata $metadata
     * @return void
     */
    public function load(Injector $i, Metadata $metadata);
}
