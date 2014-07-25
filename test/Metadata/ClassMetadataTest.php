<?php

namespace Spiffy\Inject\Metadata;

/**
 * @coversDefaultClass \Spiffy\Inject\Metadata\ClassMetadata
 */
class ClassMetadataTest extends \PHPUnit_Framework_TestCase 
{
    /** @var ClassMetadata */
    private $md;

    /**
     * @covers ::__construct
     * @covers ::getClassName
     */
    public function testGetClassName()
    {
        $this->assertSame('Spiffy\Inject\TestAsset\AnnotatedComponent', $this->md->getClassName());
    }

    /**
     * @covers ::getReflectionClass
     */
    public function testGetReflectionClass()
    {
        $this->assertEquals(
            new \ReflectionClass('Spiffy\Inject\TestAsset\AnnotatedComponent'),
            $this->md->getReflectionClass()
        );
    }

    /**
     * @covers ::addMethod
     */
    public function testAddValue()
    {
        $md = $this->md;
        $md->addMethod('setValue', ['testing']);
        
        $this->assertArrayHasKey('setValue', $md->getMethods());
        $this->assertSame(['testing'], $md->getMethods()['setValue']);
    }

    /**
     * @dataProvider accessorMutatorProvider
     */
    public function testAccessorsMutators($method, $value)
    {
        $md = $this->md;
        
        $mutator = 'set' . ucfirst($method);
        $accessor = 'get' . ucfirst($method);
        
        $md->{$mutator}($value);
        $this->assertSame($value, $md->{$accessor}());
    }
    
    public function accessorMutatorProvider()
    {
        return [
            ['name', 'name'],
            ['constructor', 'constructor'],
            ['methods', ['setValue' => 'value']],
        ];
    }
    
    protected function setUp()
    {
        $this->md = new ClassMetadata('Spiffy\Inject\TestAsset\AnnotatedComponent');
    }
}
 