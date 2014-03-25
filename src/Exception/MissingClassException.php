<?php

namespace Spiffy\Inject\Exception;

class MissingClassException extends \InvalidArgumentException
{
    /**
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct(sprintf(
            'The class with name "%s" does not exist',
            $name
        ));
    }
}
