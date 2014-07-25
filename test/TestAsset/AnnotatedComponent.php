<?php
 
namespace Spiffy\Inject\TestAsset;

use Spiffy\Inject\Annotation as Injector;

/**
 * @Injector\Component("inject.test-asset.annotated-component")
 */
class AnnotatedComponent
{
    /** @var \StdClass */
    private $foo;
    /** @var array */
    private $params;
    /** @var array */
    private $setter;
    
    /**
     * @Injector\Method({@Injector\Inject("foo"), @Injector\Param("params")})
     */
    public function __construct(\StdClass $foo, array $params)
    {
        $this->foo = $foo;
        $this->params = $params;
    }

    /**
     * @Injector\Method({@Injector\Param("setter")})
     * 
     * @param array $setter
     */
    public function setSetter($setter)
    {
        $this->setter = $setter;
    }

    /**
     * @return array
     */
    public function getSetter()
    {
        return $this->setter;
    }

    /**
     * @return \StdClass
     */
    public function getFoo()
    {
        return $this->foo;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }
}
