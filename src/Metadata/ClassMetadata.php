<?php

namespace Spiffy\Inject\Metadata;

final class ClassMetadata implements Metadata
{
    /** @var \ReflectionClass */
    private $reflectionClass;

    /** @var string */
    private $className;
    /** @var string */
    private $name;
    /** @var array */
    private $constructor = [];
    /** @var array */
    private $methods = [];

    /**
     * @param string $className
     */
    public function __construct($className)
    {
        $this->className = $className;
        $this->reflectionClass = new \ReflectionClass($className);
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return \ReflectionClass
     */
    public function getReflectionClass()
    {
        return $this->reflectionClass;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param array $constructor
     */
    public function setConstructor($constructor)
    {
        $this->constructor = $constructor;
    }

    /**
     * {@inheritDoc}
     */
    public function getConstructor()
    {
        return $this->constructor;
    }

    /**
     * {@inheritDoc}
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @param array $methods
     */
    public function setMethods($methods)
    {
        $this->methods = $methods;
    }

    /**
     * @param string $name
     * @param array $params
     */
    public function addMethod($name, array $params)
    {
        $this->methods[$name] = $params;
    }
}
