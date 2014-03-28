<?php

namespace Spiffy\Inject;

interface ServiceFactory
{
    /**
     * @param Injector $i
     * @return mixed
     */
    public function createService(Injector $i);
}
