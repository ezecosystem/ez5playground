<?php

namespace GGGeek\eZ5Playground\EezRESTAPIBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use GGGeek\eZ5Playground\EezRESTAPIBundle\DependencyInjection\Compiler\ViewCompilerPass;

class GGGeekEZ5EezRESTAPIBundle extends Bundle
{
    /// This is needed to allow us to load "views" by tag
    public function build( ContainerBuilder $container )
    {
        parent::build( $container );

        $container->addCompilerPass( new ViewCompilerPass() );
    }
}
