<?php
 
namespace Spiffy\Inject\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
final class Method
{
    /** @var array<Spiffy\Inject\Annotation\MethodAnnotation> */
    public $params = [];
}
