<?php

namespace Spiffy\Inject;

interface InjectorAware
{
    /**
     * @return \Spiffy\Inject\Injector
     */
    public function getInjector();

    /**
     * @param Injector $injector
     * @return void
     */
    public function setInjector(Injector $injector);
}
