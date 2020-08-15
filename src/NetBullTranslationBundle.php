<?php

namespace NetBull\TranslationBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use NetBull\TranslationBundle\DependencyInjection\NetBullTranslationExtension;
use NetBull\TranslationBundle\DependencyInjection\Compiler\GuesserCompilerPass;

/**
 * Class NetBullTranslationBundle
 * @package NetBull\TranslationBundle
 */
class NetBullTranslationBundle extends Bundle
{
    /**
     * Add CompilerPass
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new GuesserCompilerPass);
    }

    /**
     * @return NetBullTranslationExtension|null|\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
     */
    public function getContainerExtension()
    {
        return new NetBullTranslationExtension();
    }
}
