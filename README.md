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
 * Setting the service directly
 * Creating the service through a factory closure
 * Using the array configuration

```php
use Spiffy\Inject\Injector;

$i = new Injector();
```
