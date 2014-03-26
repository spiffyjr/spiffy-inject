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

### Parameters

```php
use Spiffy\Inject\Injector;

$i = new Injector();

// assign a parameter is as easy as using ArrayAccess
$i['foo'] = 'bar';

// output is 'bar'
echo $i['foo'];
```

### Services

The primary purpose of Spiffy\Inject is for managing your services. You can create services in one of three ways:
 * Setting using a string class name
 * Setting the service directly
 * Creating the service through a factory closure
 * Using the array configuration

All services are set through the `nject` method regardless of which style you choose. Each style has it's own advantages and disadvantages. It's you to you to decide which is the best approach to take for your application.

```php
use Spiffy\Inject\Injector;

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

// each method listed above is identical
```

### Array Configuration

The array configuration has some additional options available to make it extremely flexible.

```php
use Spiffy\Inject\Injector;

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
$i->nject('foo', ['Foo', ['I am a string', 1]);
```
