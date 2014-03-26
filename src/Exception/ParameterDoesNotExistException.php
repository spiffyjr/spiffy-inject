<?php

namespace Spiffy\Inject\Exception;

class ParameterDoesNotExistException extends \InvalidArgumentException
{
    /**
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct(sprintf(
            'The parameter with name "%s" does not exist',
            $name
        ));
    }
}
