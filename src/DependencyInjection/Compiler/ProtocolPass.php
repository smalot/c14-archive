<?php

namespace Carbon14\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ProtocolPass
 * @package Carbon14\DependencyInjection\Compiler
 */
class ProtocolPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('protocol_manager')) {
            return;
        }

        $definition = $container->findDefinition('protocol_manager');

        $taggedServices = $container->findTaggedServiceIds('carbon14.protocol');

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
