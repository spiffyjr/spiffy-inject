<?php

namespace Spiffy\Inject;

interface ServiceDecorator
{
    /**
     * Decorates the provided instance.
     *
     * @param Injector $i
     * @param mixed $instance
     * @return mixed
     */
    public function decorateService(Injector $i, $instance);
}
