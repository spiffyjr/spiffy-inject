<?php

namespace Spiffy\Inject\Exception;

class RecursiveDependencyException extends \RuntimeException
{
    public function __construct($name, array $graph)
    {
        $graph[] = $name;

        $msg = sprintf(
            'Dependency recursion detected for "%s": "%s"',
            $name,
            implode('->', $graph)
        );
        parent::__construct($msg);
    }
}
