<?php

namespace Spiffy\Inject\Exception;

class ParameterKeyDoesNotExistException extends \InvalidArgumentException
{
    /**
     * @param string $name
     * @param int $key
     */
    public function __construct($name, $key)
    {
        parent::__construct(sprintf(
            'The key "%s" does not exist in parameter "%s"',
            $key,
            $name
        ));
    }
}
