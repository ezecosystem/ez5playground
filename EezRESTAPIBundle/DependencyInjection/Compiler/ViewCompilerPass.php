<?php
/**
 * Created by PhpStorm.
 * User: gaetano.giunta
 * Date: 22/02/14
 * Time: 17.47
 */

namespace GGGeek\eZ5Playground\EezRESTAPIBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class ViewCompilerPass implements CompilerPassInterface
{
    public function process( ContainerBuilder $container )
    {
        if ( !$container->hasDefinition( 'eezrestapi.controller.rest' ) )
        {
            return;
        }

        $controller = $container->getDefinition(
            'eezrestapi.controller.rest'
        );

        foreach ( $container->findTaggedServiceIds( 'gggeek_eezrestapi.view' ) as $id => $tagAttributes )
        {
            foreach ($tagAttributes as $attributes)
            {
                $controller->addMethodCall(
                    'addView',
                    array( new Reference( $id ), $attributes["alias"] )
                );
            }
        }
    }
}
