<?php
 
namespace Spiffy\Inject\Finder;

use Symfony\Component\Finder\Finder;

class ComponentFinder extends Finder
{
    public function __construct()
    {
        parent::__construct();
        
        $this->name('*.php')
             ->contains('Spiffy\\Inject\\Annotation')
             ->files();
    }
}
