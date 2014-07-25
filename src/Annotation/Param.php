<?php
 
namespace Spiffy\Inject\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 */
final class Param implements Annotation, MethodAnnotation
{
    /** @var string */
    public $value;
}