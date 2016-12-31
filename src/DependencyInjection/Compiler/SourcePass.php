<?php

namespace Carbon14\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class SourcePass
 * @package Carbon14\DependencyInjection\Compiler
 */
class SourcePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('source_manager')) {
            return;
        }

        $definition = $container->findDefinition('source_manager');

        $taggedServices = $container->findTaggedServiceIds('carbon14.source');

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition->addMethodCall(
                  'register',
                  array(
                    $attributes['type'],
                    new Reference($id),
                  )
                );
            }
        }
    }
}
