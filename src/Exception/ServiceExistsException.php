<?php

namespace Spiffy\Inject\Exception;

class ServiceExistsException extends \InvalidArgumentException
{
    /**
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct(sprintf(
            'The service with name "%s" already exists',
            $name
        ));
    }
}
