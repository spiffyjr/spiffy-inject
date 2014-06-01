<?php

namespace Spiffy\Inject;

/**
 * @coversDefaultClass \Spiffy\Inject\InjectorUtils
 */
class InjectorUtilsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::get
     */
    public function testGetReturnsNullIfNothingMatches()
    {
        $this->assertNull(InjectorUtils::get(new Injector(), 'foobar'));
    }

    /**
     * @covers ::get
     */
    public function testGetReturnsClassIfServiceDoesNotExist()
    {
        $result = InjectorUtils::get(new Injector(), 'Spiffy\Inject\Injector');
        $this->assertInstanceOf('Spiffy\Inject\Injector', $result);
    }

    /**
     * @covers ::get
     */
    public function testGetReturnsServiceIfItExists()
    {
        $obj = new \StdClass();
        $i = new Injector();
        $i->set('foo', $obj);

        $this->assertSame($obj, InjectorUtils::get($i, 'foo'));
    }
}
