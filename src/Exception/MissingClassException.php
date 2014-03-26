<?php

namespace Spiffy\Inject\Exception;

class MissingClassException extends \InvalidArgumentException
{
    /**
     * @param string $className
     * @param string $serviceName
     */
    public function __construct($className, $serviceName)
    {
        parent::__construct(sprintf(
            'Class "%s" does not exist for service "%s"',
            $className,
            $serviceName
        ));
    }
}
