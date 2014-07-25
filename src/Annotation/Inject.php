<?php
 
namespace Spiffy\Inject\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 */
final class Inject implements Annotation, MethodAnnotation
{
    /** @var string */
    public $value;
}