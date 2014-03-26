<?php

namespace Spiffy\Inject;

/**
 * @coversDefaultClass \Spiffy\Inject\Injector
 */
class InjectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Injector
     */
    protected $i;

    /**
     * @covers ::set, \Spiffy\Inject\Exception\ServiceExistsException::__construct
     * @expectedException \Spiffy\Inject\Exception\ServiceExistsException
     * @expectedExceptionMessage The service with name "foo" already exists
     */
    public function testSetThrowsExceptionForDuplicates()
    {
        $i = $this->i;
        $i->nject('foo', function() {});
    }

    /**
     * @covers ::set
     */
    public function testSet()
    {
        $i = new Injector();
        $i->set('foo', 'foo');
        $i->set('bar', 'bar');

        $refl = new \ReflectionClass($i);
        $prop = $refl->getProperty('specs');
        $prop->setAccessible(true);

        $specs = $prop->getValue($i);
        $this->assertCount(2, $specs);
        $this->assertSame('foo', $specs['foo']);
        $this->assertSame('bar', $specs['bar']);
    }

    /**
     * @covers ::decorate
     */
    public function testDecorate()
    {
        $i = $this->i;
        $i->decorate('foo', function() {});
        $i->decorate('bar', function() {});

        $refl = new \ReflectionClass($i);
        $prop = $refl->getProperty('decorators');
        $prop->setAccessible(true);

        $specs = $prop->getValue($i);
        $this->assertCount(2, $specs);
    }

    /**
     * @covers ::wrap
     */
    public function testWrap()
    {
        $i = $this->i;
        $i->wrap('foo', function() {});
        $i->wrap('bar', function() {});

        $refl = new \ReflectionClass($i);
        $prop = $refl->getProperty('wrappers');
        $prop->setAccessible(true);

        $specs = $prop->getValue($i);
        $this->assertCount(2, $specs);
    }

    /**
     * @covers ::nject
     */
    public function testNject()
    {
        $i = new Injector();
        $i->nject('foo', 'foo');
        $i->nject('bar', 'bar');

        $refl = new \ReflectionClass($i);
        $prop = $refl->getProperty('specs');
        $prop->setAccessible(true);

        $specs = $prop->getValue($i);
        $this->assertCount(2, $specs);
        $this->assertSame('foo', $specs['foo']);
        $this->assertSame('bar', $specs['bar']);
    }

    /**
     * @covers ::nvoke
     */
    public function testNvoke()
    {
        $i = $this->i;

        $this->assertSame($i->get('foo'), $i->nvoke('foo'));
    }

    /**
     * @covers ::offsetExists, ::offsetGet, ::offsetSet, ::offsetUnset
     */
    public function testArrayAcces()
    {
        $i = $this->i;

        $i->offsetSet('foo', 'bar');
        $this->assertTrue($i->offsetExists('foo'));
        $this->assertSame('bar', $i->offsetGet('foo'));

        $i->offsetUnset('foo');
        $this->assertFalse($i->offsetExists('foo'));
    }

    /**
     * @covers ::getParamIdentifier, ::setParamIdentifier
     */
    public function testParamIdentifier()
    {
        $value = '@@';
        $i = $this->i;
        $i->setParamIdentifier($value);

        $this->assertSame($value, $i->getParamIdentifier());
    }


    /**
     * @covers ::getServiceIdentifier, ::setServiceIdentifier
     */
    public function testServiceIdentifier()
    {
        $value = '@@';
        $i = $this->i;
        $i->setServiceIdentifier($value);

        $this->assertSame($value, $i->getServiceIdentifier());
    }

    /**
     * @covers ::get, \Spiffy\Inject\Exception\RecursiveDependencyException
     * @expectedException \Spiffy\Inject\Exception\RecursiveDependencyException
     * @expectedExceptionMessage Dependency recursion detected for "recursion": "recursion->recursion"
     */
    public function testGetThrowsExceptionForRecursion()
    {
        $this->i->get('recursion');
    }

    /**
     * @covers ::get, \Spiffy\Inject\Exception\RecursiveDependencyException
     * @expectedException \Spiffy\Inject\Exception\ServiceDoesNotExistException
     * @expectedExceptionMessage The service with name "doesnotexist" does not exist
     */
    public function testGetThrowsExceptionForMissingService()
    {
        $this->i->get('doesnotexist');
    }

    /**
     * @covers ::get
     */
    public function testGetReturnsEarlyWhenServiceExists()
    {
        $value = new \StdClass();

        $i = $this->i;
        $i->set('early', $value);

        $first = $i->get('early');
        $second = $i->get('early');

        $this->assertSame($value, $first);
        $this->assertSame($second, $first);
    }

    /**
     * @covers ::get
     */
    public function testGetUnsetSpecOnceComplete()
    {
        $i = $this->i;
        $refl = new \ReflectionClass($i);
        $prop = $refl->getProperty('specs');
        $prop->setAccessible(true);

        $this->assertCount(3, $prop->getValue($i));

        $i->get('foo');
        $this->assertCount(2, $prop->getValue($i));

        $i->get('bar');
        $this->assertCount(1, $prop->getValue($i));
    }

    /**
     * @covers ::create
     */
    public function testCreateHandlesClosures()
    {
        $i = $this->i;
        $i->set('closure', function() {
            return 'closure';
        });
        $this->assertSame('closure', $i->get('closure'));
    }

    /**
     * @covers ::create
     */
    public function testCreateHandlesObjects()
    {
        $object = new \StdClass();

        $i = $this->i;
        $i->set('object', $object);
        $this->assertSame($object, $i->get('object'));
    }

    /**
     * @covers ::create
     */
    public function testCreateHandlesClassStrings()
    {
        $i = $this->i;
        $i->set('stdclass', 'StdClass');
        $this->assertInstanceOf('StdClass', $i->get('stdclass'));
    }

    public function testCreateHandlesArrays()
    {
        $this->fail('implement');
    }


    public function testIntrospectionReplacesServices()
    {
        $this->fail('implement');
    }

    public function testIntrospectionReplacesParams()
    {
        $this->fail('implement');
    }

    /**
     * @covers ::create, \Spiffy\Inject\Exception\InvalidServiceException::__construct
     * @expectedException \Spiffy\Inject\Exception\InvalidServiceException
     * @expectedExceptionMessage Creating service "invalid" failed: the service spec is invalid
     */
    public function testCreateThrowsExceptionForInvalidServiceSpec()
    {
        $i = $this->i;
        $i->set('invalid', true);
        $i->get('invalid');
    }

    /**
     * @covers ::create, ::wrapService
     */
    public function testWrappersModifiesOriginalInstance()
    {
        $object = new \StdClass();
        $i = $this->i;
        $i->set('wrapper', $object);
        $i->wrap('wrapper', function(Injector $i, $name, $callable) {
            $object = $callable();
            $object->foo = 'bar';

            return $object;
        });

        $result = $i->get('wrapper');
        $this->assertSame($object, $result);
        $this->assertSame('bar', $object->foo);
    }

    public function testWrappersReturnNewInstance()
    {
        $this->fail('implement');
    }

    public function testDecoratorsModifyInstance()
    {
        $this->fail('implement');
    }

    protected function setUp()
    {
        $i = $this->i = new Injector();
        $i->nject('foo', new \StdClass());
        $i->nject('bar', new \StdClass());
        $i->nject('recursion', function(Injector $i) {
            return $i->get('recursion');
        });
    }
}
