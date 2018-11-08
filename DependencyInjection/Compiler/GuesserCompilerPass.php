<?php

namespace NetBull\TranslationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Class GuesserCompilerPass
 * @package NetBull\TranslationBundle\DependencyInjection\Compiler
 */
class GuesserCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('netbull_translation.locale_guess_manager')) {
            return;
        }

        $definition = $container->getDefinition('netbull_translation.locale_guess_manager');
        $taggedServiceIds = $container->findTaggedServiceIds('locale_guesser');
        $neededServices = $container->getParameter('netbull_translation.guessing_order');

        foreach ($taggedServiceIds as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                if (in_array($attributes['alias'], $neededServices)) {
                    $definition->addMethodCall('addGuesser', [ new Reference($id), $attributes['alias'] ]);
                }
            }
        }
    }
}
