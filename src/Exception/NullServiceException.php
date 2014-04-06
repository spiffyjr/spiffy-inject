<?php

namespace Spiffy\Inject\Exception;

class NullServiceException extends \InvalidArgumentException
{
    /**
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct(sprintf(
            'Creating service "%s" failed: the service result was null',
            $name
        ));
    }
}
