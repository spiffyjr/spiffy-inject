<?php

namespace Spiffy\Inject;

final class Injector implements \ArrayAccess
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
     * @param string $name
     * @return mixed
     */
    public function nvoke($name)
    {
        return $this->get($name);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return array_key_exists($name, $this->services) || array_key_exists($name, $this->specs);
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
        if (array_key_exists($name, $this->services)) {
            return $this->services[$name];
        } elseif (in_array($name, $this->retrieving)) {
            throw new Exception\RecursiveDependencyException($name, $this->retrieving);
        } elseif (!array_key_exists($name, $this->specs)) {
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
        return $this->offsetExists($offset) ? $this->params[$offset] : null;
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

        $callback = $this->createInstanceCallback($name, $spec);
        $instance = $this->wrapService($name, $callback);

        if (null === $instance) {
            $instance = $callback();
        }

        $this->decorateService($name, $instance);

        return $instance;
    }

    /**
     * Creates the instance callback which is passed to the wrappers if they exist.
     * 
     * @param string $name
     * @param mixed $spec
     * @return \Closure
     */
    protected function createInstanceCallback($name, $spec)
    {
        if (is_string($spec) && class_exists($spec)) {
            $spec = new $spec();
        }

        $callback = function () use ($name, $spec) {
            if ($spec instanceof \Closure) {
                return $spec($this);
            } elseif ($spec instanceof ServiceFactory) {
                return $spec->createService($this);
            } elseif (is_object($spec)) {
                return $spec;
            } elseif (is_array($spec)) {
                return $this->createFromArray($name, $spec);
            } elseif (is_null($spec)) {
                throw new Exception\NullServiceException($name);
            }

            throw new Exception\InvalidServiceException($name);
        };

        return $callback;
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
            if ($decorator instanceof ServiceDecorator) {
                $decorator->decorateService($this, $instance);
            } else {
                $decorator($this, $instance);
            }
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
        $spec = $this->getDefaultIfUnset($array, 0, null);
        $args = $this->getDefaultIfUnset($array, 1, []);
        
        $instance = $this->createInstanceFromClass($name, $spec, $args);
        
        if ($instance instanceof ServiceFactory) {
            $instance = $instance->createService($this);
        }
        
        $this->injectSetterDependencies($instance, $this->getDefaultIfUnset($array, 2, []));

        return $instance;
    }

    /**
     * @param array $array
     * @param int $index
     * @param $default
     * @return mixed
     */
    protected function getDefaultIfUnset(array $array, $index, $default)
    {
        if (isset($array[$index])) {
            return $array[$index];
        }
        return $default;
    }
    
    /**
     * @param object $object
     * @param array $setters
     * @return object
     */
    protected function injectSetterDependencies($object, array $setters)
    {
        foreach ($setters as $method => $value) {
            if (!method_exists($object, $method)) {
                continue;
            }
            $object->$method($this->introspect($value));
        }
        
        return $object;
    }

    /**
     * @param $name
     * @param string $class
     * @param string|array $args
     * @throws Exception\MissingClassException
     * @return object
     */
    protected function createInstanceFromClass($name, $class, $args)
    {
        $class = $this->introspect($class);

        if (!class_exists($class)) {
            throw new Exception\MissingClassException($class, $name);
        }
        
        if (!is_array($args)) {
            $args = [$args];
        }
        
        $class = new \ReflectionClass($class);
        return $class->newInstanceArgs($this->introspectArgs($args));        
    }

    /**
     * @param array $args
     * @return array
     */
    protected function introspectArgs(array $args)
    {
        $args = (array) $args;
        foreach ($args as &$arg) {
            $arg = $this->introspect($arg);
        }
        
        return $args;
    }

    /**
     * @param string $value
     * @throws Exception\ParameterDoesNotExistException
     * @throws Exception\ParameterKeyDoesNotExistException
     * @return mixed
     */
    protected function introspect($value)
    {
        if ($value[0] !== $this->paramIdentifier && $value[0] !== $this->serviceIdentifier) {
            return $value;
        }

        $identifier = $value[0];
        $name = substr($value, 1);

        if ($identifier == $this->serviceIdentifier) {
            return $this->get($name);
        }

        return $this->getParameters($name);
    }

    /**
     * @param string $name
     * @return mixed|null
     * @throws Exception\ParameterDoesNotExistException
     * @throws Exception\ParameterKeyDoesNotExistException
     */
    protected function getParameters($name)
    {
        $paramString = '';

        // split the foo and [baz][bar] from foo[baz][bar]
        // foo becomes the root name and [baz][bar] are the keys in the root we're looking for
        if (preg_match('@([^\[]+)\[[^\]]+\]@', $name, $matches)) {
            $paramString = str_replace($matches[1], '', $name);
            $name = $matches[1];
        }

        if (!$this->offsetExists($name)) {
            throw new Exception\ParameterDoesNotExistException($name);
        }

        return $this->getValueFromParameterString($name, $paramString, $this->offsetGet($name));
    }

    /**
     * Iterate through the param string traversing the [baz][bar] keys until we have 
     * the final value.
     * 
     * @param string $name
     * @param string $paramString
     * @param string $value
     * @return mixed
     * @throws Exception\ParameterKeyDoesNotExistException
     */
    protected function getValueFromParameterString($name, $paramString, $value)
    {
        $original = $paramString;

        while (preg_match('@^(\[([^\]]+)\])@', $paramString, $matches)) {
            $key = $matches[2];
            $paramString = str_replace($matches[1], '', $paramString);

            if (!isset($value[$key])) {
                throw new Exception\ParameterKeyDoesNotExistException($name, $original);
            }

            $value = $value[$key];

            if (empty($paramString)) {
                break;
            }
        }
        
        return $value;
    }
}
