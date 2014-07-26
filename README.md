# Spiffy\Inject

[![Build Status](https://travis-ci.org/spiffyjr/spiffy-inject.svg)](https://travis-ci.org/spiffyjr/spiffy-inject)
[![Code Coverage](https://scrutinizer-ci.com/g/spiffyjr/spiffy-inject/badges/coverage.png?s=dfad664d97975d1d7a65b8b24506cda9769e44f9)](https://scrutinizer-ci.com/g/spiffyjr/spiffy-inject/)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/spiffyjr/spiffy-inject/badges/quality-score.png?s=d85152028d13ee4af9482d457f1e6b06f3d0b348)](https://scrutinizer-ci.com/g/spiffyjr/spiffy-inject/)

## Installation

Spiffy\Inject can be installed using composer which will setup any autoloading for you.

`composer require spiffy/spiffy-inject`

Additionally, you can download or clone the repository and setup your own autoloading.

## Introduction

Spiffy\Inject is a light-weight, HHVM compatible, and dependency lite dependency injection (DI) container. You can read
more about DI on [Wikipedia](http://en.wikipedia.org/wiki/Dependency_injection) or
[Martin Fowler's](http://martinfowler.com/articles/injection.html) website. Spiffy\Inject aims to help you manage
your parameters and services.

## Parameters

```php
use Spiffy\Inject\Injector;

$i = new Injector();

// assign a parameter is as easy as using ArrayAccess
$i['foo'] = 'bar';

// output is 'bar'
echo $i['foo'];
```

## Services

The primary purpose of Spiffy\Inject is for managing your services. You can create services in one of three ways:
 * Setting using a string class name
 * Setting the service directly
 * Creating the service through a factory closure
 * Using the array configuration
 * Using an object that implements ServiceFactory
 * Using annotations combined with a generator.

All services are set through the `nject` method regardless of which style you choose. Each style has it's own advantages and disadvantages. It's you to you to decide which is the best approach to take for your application.


### Setting Services

```php
$i = new Injector();

// setting using the string class name
$i->nject('foo', 'StdClass');

// setting the service directly
$i->nject('foo', new \StdClass());

// setting the service using a closure factory
$i->nject('foo', function() {
  return new \StdClass();
});

// setting the service using array configuration
$i->nject('foo', ['StdClass']);

// setting the service using an object that implements ServiceFactory
class StdClassFactory implements ServiceFactory
{
    public function createService(Injector $i)
    {
        return new \StdClass();
    }
}
$i->nject('foo', new StdClassFactory());

// each method listed above is identical
```

### Testing if a service exists

```php
// false
$i->has('foo');

$i->nject('foo', new \StdClass());

// true
$i->has('foo');
```

### Getting Services

```php
// assuming the configuration from 'Setting Services' above
// the following retrieves the 'foo' service
$foo = $i->nvoke('foo');
```

## Array Configuration

The array configuration has some additional options available to make it extremely flexible.

### Constructor injection

```php
$i = new Injector();

// you can pass constructor parameters to the service
class Foo
{
    public function __construct($string, $int)
    {
        $this->string = $string;
        $this->int = $int;
    }
}

// the resulting object will have 'string set to 'I am a string'
// and 'int' set to '1'
$i->nject('foo', ['Foo', ['I am a string', 1]]);
```

### Setter injection

```php
$i = new Injector();

// you can pass constructor parameters to the service
class Foo
{
    public function __construct($int)
    {
        $this->int = $int;
    }

    public function setString($string)
    {
        $this->string = $string;
    }
}

// the resulting object will have 'string set to 'I am a string'
// and 'int' set to '1'
$i->nject('foo', ['Foo',[1],['setString' => 'string']]);
```

### Referencing other services and parameters

Spiffy\Inject's array configuration includes the ability to reference other services when the string is prepended with
special characters. By default you reference services with the `@` symbol and parameters with the `$` symbol. These can
be modified using the `setServiceIdentifier` and `setParamIdentifier` methods respectively.

```php
$i = new Injector();

class Bar
{
}

class Foo
{
    public function __construct(Bar $bar)
    {
        $this->bar = $bar;
    }

    public function setBaz($baz)
    {
        $this->baz = $baz;
    }
}

// set the 'baz' parameter to 'boogly'
$i['baz'] = 'boogly';

// set the 'bar' service to an instance of \Bar
$i->nject('bar', new \Bar());

// create the foo service using array configuration and parameter/service references
$i->nject('foo', ['Foo',['@bar'],['setBaz' => '$baz']]);

// the resulting Foo service would have '$this->bar' set to '\Bar' and '$this->baz' set to 'boogly'
```

### ServiceFactories

If you return an instance of `Spiffy\Inject\ServiceFactory` from an array configuration it will automatically create
the instance for you and return that instead. This let's you inject parameters into Service Factories easily and reuse
factories while still returning the instance you want.

```php
class ArrayObjectFactory implements ServiceFactory
{
    private $defaults;

    public function __construct(array $defaults)
    {
        $this->defaults = $defaults;
    }

    public function createService(Injector $i)
    {
        return new \ArrayObject($this->defaults);
    }
}

$i = new Injector();

// Result is an ArrayObject and *not* an ArrayObjectFactory
$i->nject('ArrayObject', ['ArrayObjectFactory', [['foo' => 'bar']]);
```

### Annotations combined with a generator

SpiffyInject provides annotations that you can use to assist in creating configurations for services.

```php
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
```

Now that you have an annotated class use the metadata factory to create metadata for the class and a generator
to generate the Injector configuration.

```php

use Spiffy\Inject\Generator;
use Spiffy\Inject\Metadata;

$mdf = new Metadata\MetadataFactory();
$md = $mdf->getMetadataForClass('Spiffy\Inject\TestAsset\AnnotatedComponent');

$generator = new Generator\ArrayGenerator();

$i = new Injector();
$i->nject($md->getName(), $generator->generate($md));

$i->nvoke($md->getName()); // instanceof Spiffy\Inject\TestAsset\AnnotatedComponent
```

Reading files, creating metadata, and generating configuration is a **heavy** process and is not intended for production.
You should cache the results of the generation and use the cache in production.

## Decorating your services

Sometimes you want to over-ride the services set your DI container without modifying the original configuration. Spiffy\Inject handles this by providing you with two types of decorators.

### Decorate

The `decorate` method allows you to take the service created and apply any modifications to it prior to having it returned. The decorate closure receives the injector and service as arguments.

```php
$i = new Injector();
$i->nject('foo', new \StdClass());
$i->decorate('foo', function(Injector $i, \StdClass $foo) {
    $foo->bar = 'bar';
    $foo->baz = 'baz';
});

$foo = $i->nvoke('foo');

// output is 'barbaz';
echo $foo->bar;
echo $foo->baz;
```

Alternatively, you can provide an instance of a class implementing the `Spiffy\Inject\ServiceDecorator` interface:

```php

namespace My;

use Spiffy\Inject\Injector;
use Spiffy\Inject\ServiceDecorator;

class FooDecorator implements ServiceDecorator
{
    public function decorateService(Injector $i, $instance)
    {
        $instance->bar = 'bar';
        $instance->baz = 'baz';
    }
}
```

```php
use Spiffy\Inject\Injector;

$i = new Injector();
$i->nject('foo', new \StdClass());
$i->decorate('foo', new \My\FooDecorator());

$foo = $i->nvoke('foo');

// output is 'barbaz';
echo $foo->bar;
echo $foo->baz;
```

### Wrap

The `wrap` method is much more powerful than `decorate`. Wrapping let's you completely change the object that's created or completely bypass the original configuration. The wrap closure receives three arguments: the injector, the name of the service, and the callable that creates the service.

```php
$i = new Injector();
$i->nject('foo', new \StdClass());

// if we use the $callable available to the closure we receive an instance of the original service
// the \StdClass object would have two properties: 'bar' and 'name'
// the values would be 'bar' and 'foo' respectively
$i->wrap('foo', function(Injector $i, $name, $callable) {
    $foo = $callable();
    $foo->bar = 'bar';
    $foo->name = $name;

    return $foo;
});

// we can completely override the original service configuration by skipping the callable
$i->wrap('foo', function(Injector $i, $name, $callable) {
    return new \ArrayObject();
});

// output is 'ArrayObject'
echo get_class($i->nvoke('foo'));
```

Alternatively, you can provide an instance of a class implementing the `Spiffy\Inject\ServiceWrapper` interface:

```php
namespace My;

use Spiffy\Inject\Injector;
use Spiffy\Inject\ServiceWrapper;

class FooWrapper implements ServiceWrapper
{
    public function wrapService(Injector $i, $name, $callable)
    {
        $foo = $callable();
        $foo->bar = 'bar';
        $foo->name = $name;

        return $foo;
    }
}
```

```php
use Spiffy\Inject\Injector;

$i = new Injector();
$i->nject('foo', new \StdClass());
$i->wrap('foo', new \My\FooWrapper());

$foo = $i->nvoke('foo');

echo $foo->bar; // outputs 'bar'
echo $foo->name; // outputs 'foo'
```


## Why nvoke and nject?

Because it's damn cute, that's why! If you prefer, though, you can use `set()` instead as `nject()` and `get()` instead of `nvoke()`.
