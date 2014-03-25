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
     * @param string $name
     * @param mixed $value
     * @throws Exception\ServiceExistsException
     */
    public function set($name, $value)
    {
        if (isset($this->services[$name])) {
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
        $this->decorators[$name] = $decorator;
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

        if ($spec instanceof \Closure) {
            return $spec($this);
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
    }

    /**
     * @param string $name
     * @param array $array
     * @return object
     * @throws Exception\MissingClassException
     */
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

        return $instance;
    }

    /**
     * @param string $arg
     * @return mixed
     */
    protected function injectArg($arg)
    {
        if ($arg[0] == '@') {
            $arg = $this->get(substr($arg, 1));
        } else if ($arg[0] == '$') {
            $key = substr($arg, 1);
            $arg = isset($this->params[$key]) ? $this->params[$key] : null;
        }
        return $arg;
    }
}
