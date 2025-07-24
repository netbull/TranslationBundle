<?php

namespace NetBull\TranslationBundle\DependencyInjection\Compiler;

use NetBull\TranslationBundle\Guessers\LocaleGuesserManager;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class GuesserCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container): void
    {
        if (false === $container->hasDefinition(LocaleGuesserManager::class)) {
            return;
        }

        $definition = $container->getDefinition(LocaleGuesserManager::class);
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
