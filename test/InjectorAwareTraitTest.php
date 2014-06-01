<?php

namespace Spiffy\Inject;

/**
 * @covers \Spiffy\Inject\InjectorAwareTrait
 */
class InjectorAwareTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::getInjector, ::setInjector
     */
    public function testSetGetInjector()
    {
        $i = new Injector();

        $t = $this->getObjectForTrait('Spiffy\Inject\InjectorAwareTrait');
        $t->setInjector($i);
        $this->assertSame($i, $t->getInjector());
    }
}
