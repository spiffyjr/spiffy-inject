<?php

namespace Spiffy\Inject;

trait InjectorAwareTrait
{
    /**
     * @var \Spiffy\Inject\Injector|null
     */
    private $injector;

    /**
     * @return \Spiffy\Inject\Injector
     */
    final public function getInjector()
    {
        return $this->injector;
    }

    /**
     * @param Injector $injector
     * @return void
     */
    final public function setInjector(Injector $injector)
    {
        $this->injector = $injector;
    }
}
