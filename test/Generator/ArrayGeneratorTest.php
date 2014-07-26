<?php

namespace Spiffy\Inject\Generator;

use Spiffy\Inject\Annotation;
use Spiffy\Inject\Metadata\ClassMetadata;
use Spiffy\Inject\Metadata\MetadataFactory;

/**
 * @coversDefaultClass \Spiffy\Inject\Generator\ArrayGenerator
 */
class ArrayGeneratorTest extends \PHPUnit_Framework_TestCase 
{
    /** @var ArrayGenerator */
    private $g;

    /**
     * @covers ::generate
     * @covers ::buildConstructor
     * @covers ::buildMethods
     * @covers ::prepareValueFromAnnotation
     */
    public function testGenerateForValidComponent()
    {
        $g = $this->g;
        
        $mdf = new MetadataFactory();
        $md = $mdf->getMetadataForClass('Spiffy\Inject\TestAsset\AnnotatedComponent');
        
        $this->assertSame([
            'Spiffy\Inject\TestAsset\AnnotatedComponent',
            ['@foo', '$params'],
            ['setSetter' => '$setter']
        ], $g->generate($md));
    }

    /**
     * @covers ::generate
     * @covers ::buildConstructor
     * @covers ::buildMethods
     * @covers ::prepareValueFromAnnotation
     */
    public function testGenerateForInvalidComponent()
    {
        $md = new ClassMetadata('Spiffy\Inject\TestAsset\ConstructorParams');
        $md->setConstructor([new Annotation\Method()]);
        $md->addMethod('setSetter', [new Annotation\Method()]);
        $md->setName('foo');
        
        $g = $this->g;
        
        $this->assertSame(['Spiffy\Inject\TestAsset\ConstructorParams', [], []], $g->generate($md));
    }
    
    protected function setUp()
    {
        $this->g = new ArrayGenerator();
    }
}
