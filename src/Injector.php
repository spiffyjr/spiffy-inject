<?php

namespace Spiffy\Inject;

class Injector implements \ArrayAccess
{
    /**
     * @var string
     */
    protected $serviceIdentifier = '@';

    /**
     * @var string
     */
    protected $paramIdentifier = '$';

    /**
     * @var array
     */
    protected $params = [];

    /**
     * @var array
     */
    protected $services = [];

    /**
     * @var array
     */
    protected $decorators = [];

    /**
     * @var array
     */
    protected $wrappers = [];

    /**
     * @var array
     */
    protected $retrieving = [];

    /**
     * @var string
     */
    protected $parent;

    /**
     * @var array
     */
    protected $specs = [];

    /**
     * Proxy to set. Why you ask? Because $i->nject() is so damn cute, that's why.
     *
     * @see set
     */
    public function nject($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * Proxy to get. Why you ask? Because $i->nvoke() is so damn cute, that's why.
     *
     * @see get
     */
    public function nvoke($name)
    {
        return $this->get($name);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @throws Exception\ServiceExistsException
     */
    public function set($name, $value)
    {
        if (isset($this->specs[$name])) {
            throw new Exception\ServiceExistsException($name);
        }
        $this->specs[$name] = $value;
    }

    /**
     * @param string $name
     * @throws Exception\RecursiveDependencyException
     * @throws Exception\ServiceDoesNotExistException
     * @throws Exception\InvalidServiceException
     * @return mixed
     */
    public function get($name)
    {
        if (isset($this->services[$name])) {
            return $this->services[$name];
        }

        if (in_array($name, $this->retrieving)) {
            throw new Exception\RecursiveDependencyException($name, $this->retrieving);
        }

        if (!isset($this->specs[$name])) {
            throw new Exception\ServiceDoesNotExistException($name);
        }

        $this->parent = $name;
        $this->retrieving[] = $name;
        $this->services[$name] = $this->create($name);

        unset($this->specs[$name]);
        unset($this->retrieving[$name]);
        $this->parent = null;

        return $this->services[$name];
    }

    /**
     * @param string $name
     * @param mixed $decorator
     */
    public function decorate($name, $decorator)
    {
        $this->decorators[$name][] = $decorator;
    }

    /**
     * @param string $name
     * @param mixed $wrapper
     */
    public function wrap($name, $wrapper)
    {
        $this->wrappers[$name][] = $wrapper;
    }

    /**
     * {@inheritDoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->params[$offset]);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetGet($offset)
    {
        return $this->params[$offset];
    }

    /**
     * {@inheritDoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->params[$offset] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->params[$offset]);
    }

    /**
     * @param string $paramIdentifier
     */
    public function setParamIdentifier($paramIdentifier)
    {
        $this->paramIdentifier = $paramIdentifier;
    }

    /**
     * @return string
     */
    public function getParamIdentifier()
    {
        return $this->paramIdentifier;
    }

    /**
     * @param string $serviceIdentifier
     */
    public function setServiceIdentifier($serviceIdentifier)
    {
        $this->serviceIdentifier = $serviceIdentifier;
    }

    /**
     * @return string
     */
    public function getServiceIdentifier()
    {
        return $this->serviceIdentifier;
    }

    /**
     * @param string $name
     * @return object
     * @throws Exception\InvalidServiceException
     */
    protected function create($name)
    {
        $spec = $this->specs[$name];

        $callback = function () use ($name, $spec) {
            if ($spec instanceof \Closure) {
                return $spec($this);
            }

            if ($spec instanceof ServiceFactory) {
                return $spec->createService($this);
            }

            if (is_object($spec)) {
                return $spec;
            }

            if (is_array($spec)) {
                return $this->createFromArray($name, $spec);
            }

            if (is_string($spec) && class_exists($spec)) {
                return new $spec();
            }

            throw new Exception\InvalidServiceException($name);
        };

        $instance = $this->wrapService($name, $callback);

        if (null === $instance) {
            $instance = $callback();
        }

        $this->decorateService($name, $instance);

        return $instance;
    }

    /**
     * @param string $name
     * @param \Closure $callback
     * @return mixed
     */
    protected function wrapService($name, \Closure $callback)
    {
        if (!isset($this->wrappers[$name])) {
            return null;
        }

        $instance = null;
        foreach ($this->wrappers[$name] as $wrapper) {
            if ($wrapper instanceof ServiceWrapper) {
                $instance = $wrapper->wrapService($this, $name, $callback);
            } else {
                $instance = $wrapper($this, $name, $callback);
            }
        }

        return $instance;
    }

    /**
     * @param string $name
     * @param object $instance
     */
    protected function decorateService($name, $instance)
    {
        if (!isset($this->decorators[$name])) {
            return;
        }

        foreach ($this->decorators[$name] as $decorator) {
            $decorator($this, $instance);
        }
    }

    /**
     * @param string $name
     * @param array $array
     * @return object
     * @throws Exception\MissingClassException
     */
    protected function createFromArray($name, array $array)
    {
        $class = isset($array[0]) ? $this->introspect($array[0]) : null;

        if (!class_exists($class)) {
            throw new Exception\MissingClassException($class, $name);
        }

        $args = isset($array[1]) ? $array[1] : [];
        $setters = isset($array[2]) ? $array[2] : [];

        $args = (array) $args;
        foreach ($args as &$arg) {
            $arg = $this->introspect($arg);
        }

        $class = new \ReflectionClass($class);
        $instance = $class->newInstanceArgs($args);

        $setters = (array) $setters;
        foreach ($setters as $method => $value) {
            if (method_exists($instance, $method)) {
                $instance->$method($this->introspect($value));
            }
        }

        return $instance;
    }

    /**
     * @param string $value
     * @throws Exception\ParameterDoesNotExistException
     * @return mixed
     */
    protected function introspect($value)
    {
        $identifiers = implode('', [$this->paramIdentifier, $this->serviceIdentifier]);
        $regex = sprintf('/^([%s])(.*)/', preg_quote($identifiers));

        if (!preg_match($regex, $value, $matches)) {
            return $value;
        }

        $identifier = $matches[1];
        $name = $matches[2];

        if ($identifier == $this->serviceIdentifier) {
            return $this->get($name);
        }

        if (!$this->offsetExists($name)) {
            throw new Exception\ParameterDoesNotExistException($name);
        }

        return $this->offsetGet($name);
    }
}
