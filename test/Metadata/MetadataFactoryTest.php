<?php
 
namespace Spiffy\Inject\Metadata;

use Spiffy\Inject\Annotation;

/**
 * @coversDefaultClass \Spiffy\Inject\Metadata\MetadataFactory
 */
class MetadataFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var MetadataFactory */
    private $factory;

    /**
     * @covers ::__construct, ::getMetadataForClass
     */
    public function testGetMetadataForClass()
    {
        $f = $this->factory;
        $md = $f->getMetadataForClass('Spiffy\Inject\TestAsset\AnnotatedComponent');
        
        $this->assertInstanceOf('Spiffy\Inject\Metadata\ClassMetadata', $md);
        
        $expected = new ClassMetadata('Spiffy\Inject\TestAsset\AnnotatedComponent');
        $foo = new Annotation\Inject();
        $foo->value = 'foo';

        $params = new Annotation\Param();
        $params->value = 'params';
        
        $expected->setConstructor([$foo, $params]);
        $expected->setName('inject.test-asset.annotated-component');
        
        $setter = new Annotation\Param();
        $setter->value = 'setter';
        
        $expected->addMethod('setSetter', [$setter]);
        
        $this->assertEquals($expected, $md);
    }

    /**
     * @expectedException \Spiffy\Inject\Metadata\Exception\InvalidComponentException
     * @expectedExceptionMessage Class "Spiffy\Inject\TestAsset\ConstructorParams" is not an injectable component
     */
    public function testGetMetadataForClassThrowsExceptionOnInvalidClass()
    {
        $f = $this->factory;
        $f->getMetadataForClass('Spiffy\Inject\TestAsset\ConstructorParams');
    }
    
    protected function setUp()
    {
        $this->factory = new MetadataFactory();
    }
}
