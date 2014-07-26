<?php

namespace Spiffy\Inject\Generator;

use Spiffy\Inject\Injector;
use Spiffy\Inject\Metadata\Metadata;

interface Generator
{
    /**
     * @param \Spiffy\Inject\Metadata\Metadata $metadata
     * @return mixed
     */
    public function generate(Metadata $metadata);
}
