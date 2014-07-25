<?php

namespace Spiffy\Inject\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
final class Component
{
    /** @var string */
    public $name;
}