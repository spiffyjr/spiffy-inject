<?php
 
namespace Spiffy\Inject\Loader;

use Spiffy\Inject\Annotation;
use Spiffy\Inject\Injector;
use Spiffy\Inject\Metadata\Metadata;

class ArrayLoader implements Loader
{
    /**
     * {@inheritDoc}
     */
    public function load(Injector $i, Metadata $metadata)
    {
        $i->nject(
            $metadata->getName(),
            [
                $metadata->getClassName(),
                $this->buildConstructor($metadata),
                $this->buildMethods($metadata)
            ]
        );
    }

    /**
     * @param Metadata $metadata
     * @return array
     */
    private function buildConstructor(Metadata $metadata)
    {
        $constructor = [];
        foreach ($metadata->getConstructor() as $annotation) {
            $value = $this->prepareValueFromAnnotation($annotation);

            if (!$value) {
                continue;
            }

            $constructor[] = $value;
        }
        
        return $constructor;
    }

    /**
     * @param Metadata $metadata
     * @return array
     */
    private function buildMethods(Metadata $metadata)
    {
        $methods = [];
        foreach ($metadata->getMethods() as $methodName => $annotations) {
            foreach ($annotations as $annotation) {
                $value = $this->prepareValueFromAnnotation($annotation);

                if (!$value) {
                    continue;
                }

                $methods[$methodName] = $value;
            }
        }
        
        return $methods;
    }

    /**
     * @param mixed $annotation
     * @return null|string
     */
    private function prepareValueFromAnnotation($annotation)
    {
        if ($annotation instanceof Annotation\Inject) {
            return '@' . $annotation->value;
        } elseif ($annotation instanceof Annotation\Param) {
            return '$' . $annotation->value;
        }
        return null;
    }
}
