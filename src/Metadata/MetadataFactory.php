<?php

namespace Spiffy\Inject\Metadata;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Spiffy\Inject\Annotation;

final class MetadataFactory
{
    /** @var ClassMetadata[] */
    private $loadedMetadata;
    /** @var \Doctrine\Common\Annotations\AnnotationReader */
    private $reader;
        
    public function __construct()
    {
        $this->reader = new AnnotationReader();
        
        foreach (glob(__DIR__ . '/../Annotation/*.php') as $file) {
            AnnotationRegistry::registerFile($file);
        }
    }

    /**
     * @param string $className
     * @return ClassMetadata
     */
    public function getMetadataForClass($className)
    {
        if (!isset($this->loadedMetadata[$className])) {
            $this->loadMetadata($className);
        }
        return $this->loadedMetadata[$className];
    }

    /**
     * @param string $className
     * @throws Exception\InvalidComponentException
     */
    private function loadMetadata($className)
    {
        $md = new ClassMetadata($className);
        $reflClass = $md->getReflectionClass();
        
        $serviceAnnotation = $this->reader->getClassAnnotation($reflClass, 'Spiffy\\Inject\Annotation\\Component');
        
        if (!$serviceAnnotation instanceof Annotation\Component) {
            throw new Exception\InvalidComponentException(sprintf(
                'Class "%s" is not an injectable component: did you forget the @Component annotation?',
                $className
            ));
        }
        
        $md->setName($serviceAnnotation->name);
       
        foreach ($reflClass->getMethods() as $reflMethod) {
            $methodAnnotations = $this->reader->getMethodAnnotations($reflMethod);
            
            /** @var \Spiffy\Inject\Annotation\Method $annotation */
            foreach ($methodAnnotations as $annotation) {
                if ($reflMethod->isConstructor()) {
                    $md->setConstructor($annotation->params);
                    continue;
                }
                
                $md->addMethod($reflMethod->getName(), $annotation->params);
            }
        }

        $this->loadedMetadata[$className] = $md;
    }
}
