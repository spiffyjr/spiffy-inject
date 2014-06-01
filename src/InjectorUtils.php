<?php

namespace Spiffy\Inject;

abstract class InjectorUtils
{
    /**
     * @param Injector $i
     * @param mixed $input
     * @return mixed
     */
    final public static function get(Injector $i, $input)
    {
        if (is_string($input)) {
            if ($i->has($input)) {
                return $i->nvoke($input);
            }

            if (class_exists($input)) {
                return new $input();
            }
        }
        return null;
    }
}
