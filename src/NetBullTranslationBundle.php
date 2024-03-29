<?php

namespace NetBull\TranslationBundle;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use NetBull\TranslationBundle\DependencyInjection\NetBullTranslationExtension;
use NetBull\TranslationBundle\DependencyInjection\Compiler\GuesserCompilerPass;

class NetBullTranslationBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new GuesserCompilerPass);
    }

    /**
     * @return NetBullTranslationExtension|null|ExtensionInterface
     */
    public function getContainerExtension()
    {
        return new NetBullTranslationExtension();
    }
}
