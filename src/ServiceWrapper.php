<?php

namespace Spiffy\Inject;

interface ServiceWrapper
{
    /**
     * Wraps the provided instance.
     *
     * @param Injector $i
     * @param string $instance
     * @param callable $callable
     * @return mixed
     */
    public function wrapService(Injector $i, $name, $callable);
}
