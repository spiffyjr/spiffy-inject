<?php

namespace Spiffy\Inject;

use Spiffy\Inject\TestAsset\TestDecorator;
use Spiffy\Inject\TestAsset\TestWrapper;

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
        $i = $this->i;
        $i->nject('recursion', function(Injector $i) {
            return $i->get('recursion');
        });
        $i->get('recursion');
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
        $i = new Injector();
        $i->nject('foo', function() {});
        $i->nject('bar', function() {});
        $i->nject('baz', function() {});

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

    /**
     * @covers ::create
     */
    public function testCreateHandlesServiceFactories()
    {
        $i = $this->i;
        $i->set('factory', new TestAsset\TestFactory());
        $this->assertInstanceOf('StdClass', $i->get('factory'));
    }

    /**
     * @covers ::create, ::createFromArray, \Spiffy\Inject\Exception\MissingClassException::__construct
     * @expectedException \Spiffy\Inject\Exception\MissingClassException
     * @expectedExceptionMessage Class "Missing\Class" does not exist for service "doesnotexist"
     */
    public function testCreateFromArrayThrowsExceptionOnInvalidClass()
    {
        $i = $this->i;
        $i->nject('doesnotexist', ['Missing\Class']);
        $i->nvoke('doesnotexist');
    }

    /**
     * @covers ::create, ::createFromArray
     */
    public function testCreateHandlesArraysCreatesBasicClass()
    {
        $i = $this->i;
        $i->nject('array', ['StdClass']);

        $this->assertInstanceOf('StdClass', $i->nvoke('array'));
    }

    /**
     * @covers ::create, ::createFromArray
     */
    public function testCreateHandlesArraysCreatesParameterizedClass()
    {
        $i = $this->i;
        $i->nject(
            'array',
            [
                'Spiffy\Inject\TestAsset\ConstructorParams',
                [
                    'foogly',
                    'boogly'
                ]
            ]
        );

        $result = $i->nvoke('array');
        $this->assertInstanceOf('Spiffy\Inject\TestAsset\ConstructorParams', $result);
        $this->assertSame('foogly', $result->getFoo());
        $this->assertSame('boogly', $result->getBar());
    }

    /**
     * @covers ::create, ::createFromArray, ::introspect
     */
    public function testCreateHandlesArraysCreatesParameterizedClassFromParameters()
    {
        $i = $this->i;
        $i['boogly'] = 'woogly';
        $i->nject(
            'array',
            [
                'Spiffy\Inject\TestAsset\ConstructorParams',
                [
                    'foogly',
                    '$boogly'
                ]
            ]
        );

        $result = $i->nvoke('array');
        $this->assertInstanceOf('Spiffy\Inject\TestAsset\ConstructorParams', $result);
        $this->assertSame('foogly', $result->getFoo());
        $this->assertSame('woogly', $result->getBar());
    }

    /**
     * @covers ::create, ::createFromArray, ::introspect
     */
    public function testCreateHandlesArraysCreatesParameterizedClassFromService()
    {
        $foogly = new \StdClass();
        $i = $this->i;
        $i->nject('foogly', $foogly);
        $i->nject(
            'array',
            [
                'Spiffy\Inject\TestAsset\ConstructorParams',
                [
                    '@foogly',
                    'boogly'
                ]
            ]
        );

        $result = $i->nvoke('array');
        $this->assertInstanceOf('Spiffy\Inject\TestAsset\ConstructorParams', $result);
        $this->assertSame($foogly, $result->getFoo());
        $this->assertSame('boogly', $result->getBar());
    }

    /**
     * @covers ::create, ::createFromArray, ::introspect
     */
    public function testCreateHandlesArraysCreatesParameterizedClassWithSetters()
    {
        $boogly = new \StdClass();

        $i = $this->i;
        $i['foogly'] = 'foogly';
        $i->nject('boogly', $boogly);
        $i->nject(
            'array',
            [
                'Spiffy\Inject\TestAsset\ConstructorParams',
                [
                    'foo',
                    'bar'
                ],
                [
                    'setFoo' => '$foogly',
                    'setBar' => '@boogly'
                ]
            ]
        );

        $result = $i->nvoke('array');
        $this->assertInstanceOf('Spiffy\Inject\TestAsset\ConstructorParams', $result);
        $this->assertSame('foogly', $result->getFoo());
        $this->assertSame($boogly, $result->getBar());
    }

    /**
     * @covers ::introspect, \Spiffy\Inject\Exception\ParameterDoesNotExistException
     * @expectedException \Spiffy\Inject\Exception\ParameterDoesNotExistException
     * @expectedExceptionMessage The parameter with name "param" does not exist
     */
    public function testIntrospectThrowsExceptionForInvalidParameter()
    {
        $i = $this->i;
        $i->nject('paramexception', ['StdClass', '$param']);
        $i->nvoke('paramexception');
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

    /**
     * @covers ::create, ::wrapService
     */
    public function testCallableWrappersReturnNewInstance()
    {
        $object = new \StdClass();
        $i = $this->i;
        $i->set('wrapper', $object);
        $i->wrap('wrapper', function() {
            return [];
        });

        $result = $i->get('wrapper');
        $this->assertInternalType('array', $result);
    }

    /**
     * @covers ::create, ::wrapService
     */
    public function testServiceWrappersReturnNewInstance()
    {
        $object = new \StdClass();
        $i = $this->i;
        $i->set('wrapper', $object);
        $i->wrap('wrapper', new TestWrapper());

        $result = $i->get('wrapper');
        $this->assertSame($object, $result->original);
        $this->assertSame('wrapper', $result->name);
        $this->assertTrue($result->didItWork);
    }

    /**
     * @covers ::create, ::decorateService
     */
    public function testCallableDecoratorsModifyInstance()
    {
        $object = new \StdClass();
        $i = $this->i;
        $i->set('decorate', $object);
        $i->decorate('decorate', function(Injector $i, \StdClass $obj) {
            $obj->foo = 'bar';

            return $obj;
        });

        $result = $i->get('decorate');
        $this->assertSame($object, $result);
        $this->assertSame('bar', $object->foo);
    }

    /**
     * @covers ::create, ::decorateService
     */
    public function testServiceDecoratorsModifyInstance()
    {
        $object = new \StdClass();
        $object->value = __FUNCTION__;
        $i = $this->i;
        $i->set('decorate', $object);
        $i->decorate('decorate', new TestDecorator());

        $result = $i->get('decorate');
        $this->assertSame($object, $result);
        $this->assertTrue($result->didItWork);
    }

    protected function setUp()
    {
        $i = $this->i = new Injector();
        $i->nject('foo', new \StdClass());
        $i->nject('bar', new \StdClass());
    }
}
