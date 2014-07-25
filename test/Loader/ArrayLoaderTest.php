<?php

namespace Spiffy\Inject\Loader;

use Spiffy\Inject\Annotation;
use Spiffy\Inject\Injector;
use Spiffy\Inject\Metadata\ClassMetadata;
use Spiffy\Inject\Metadata\MetadataFactory;

/**
 * @coversDefaultClass \Spiffy\Inject\Loader\ArrayLoader
 */
class ArrayLoaderTest extends \PHPUnit_Framework_TestCase 
{
    /** @var Injector */
    private $i;
    /** @var ArrayLoader */
    private $l;

    /**
     * @covers ::load
     * @covers ::buildConstructor
     * @covers ::buildMethods
     * @covers ::prepareValueFromAnnotation
     */
    public function testLoadForValidComponent()
    {
        $l = $this->l;
        $i = $this->i;
        
        $i->nject('foo', new \StdClass());
        $i['params'] = [];
        $i['setter'] = 'setter';
        
        $mdf = new MetadataFactory();
        $md = $mdf->getMetadataForClass('Spiffy\Inject\TestAsset\AnnotatedComponent');
        
        $l->load($i, $md);
        
        $this->assertTrue($i->has('inject.test-asset.annotated-component'));

        /** @var \Spiffy\Inject\TestAsset\AnnotatedComponent $result */
        $result = $i->nvoke('inject.test-asset.annotated-component');
        
        $this->assertInstanceOf('Spiffy\Inject\TestAsset\AnnotatedComponent', $result);
        $this->assertSame($result->getSetter(), $i['setter']);
        $this->assertSame($result->getParams(), $i['params']);
        $this->assertSame($result->getFoo(), $i->nvoke('foo'));
    }

    /**
     * @covers ::load
     * @covers ::buildConstructor
     * @covers ::buildMethods
     * @covers ::prepareValueFromAnnotation
     */
    public function testLoadForInvalidComponent()
    {
        $md = new ClassMetadata('Spiffy\Inject\TestAsset\ConstructorParams');
        $md->setConstructor([new Annotation\Method()]);
        $md->addMethod('setSetter', [new Annotation\Method()]);
        $md->setName('foo');
        
        $l = $this->l;
        $l->load($this->i, $md);
        
        $this->assertTrue($this->i->has('foo'));
    }
    
    protected function setUp()
    {
        $this->i = new Injector();
        $this->l = new ArrayLoader();
    }
}
