<?php

namespace Spiffy\Inject\Exception;

class InvalidServiceException extends \InvalidArgumentException
{
    /**
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct(sprintf(
            'Adding service "%s" failed: the "service" given is invalid',
            $name
        ));
    }
}
