<?php

namespace Spiffy\Inject;

class ServiceFactory
{
    public function create($spec)
    {
        if ($spec instanceof \Closure) {
            return $this->createFromClosure($spec);
        }

        if (is_object($spec)) {
            $this->invokeObject($spec);
        }

        if (is_array($spec)) {
            $this->invokeArray($spec);
        }

        if (is_string($spec) && class_exists($spec)) {
            $this->invokeClass($spec);
        }

        return null;
    }

    protected function createFromClosure(\Closure $closure)
    {
        return $closure($this);
    }

    protected function createFromArray($name, array $array)
    {
        $class = isset($array[0]) ? $this->injectArg($array[0]) : null;

        if (!class_exists($class)) {
            throw new Exception\MissingClassException($class);
        }

        $args = isset($array[1]) ? $array[1] : [];
        $setters = isset($array[2]) ? $array[2] : [];
        if (!is_array($args)) {
            $args = [$args];
        }

        foreach ($args as &$arg) {
            $arg = $this->injectArg($arg);
        }

        $class = new \ReflectionClass($class);
        $instance = $class->newInstanceArgs($args);

        foreach ($setters as $method => $value) {
            if (method_exists($instance, $method)) {
                $instance->$method($this->injectArg($value));
            }
        }

        if (isset($this->decorators[$name])) {
            $decorator = $this->decorators[$name];
            $decorator($this, $instance);
        }

        $this->services[$name] = $instance;
    }
}
