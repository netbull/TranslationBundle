<?php

namespace NetBull\TranslationBundle\DependencyInjection;

use Exception;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class NetBullTranslationExtension extends Extension
{
    /**
     * @param array $configs
     * @param ContainerBuilder $container
     * @return void
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        if (!$config['disable_locale_listeners']) {
            $this->bindParameters($container, 'netbull_translation', $config);

            // Fallback for missing intl extension
            $intlExtensionInstalled = extension_loaded('intl');
            $container->setParameter('netbull_translation.intl_extension_installed', $intlExtensionInstalled);
            $iso3166 = $iso639one = $iso639two = $localeScript = [];

            if (!$intlExtensionInstalled) {
                $yamlParser = new Parser();
                $file = new FileLocator(__DIR__ . '/../Resources/config/locale');
                $iso3166 = $yamlParser->parse(file_get_contents($file->locate('iso3166-1-alpha-2.yaml')));
                $iso639one = $yamlParser->parse(file_get_contents($file->locate('iso639-1.yaml')));
                $iso639two = $yamlParser->parse(file_get_contents($file->locate('iso639-2.yaml')));
                $localeScript = $yamlParser->parse(file_get_contents($file->locate('locale_script.yaml')));
            }

            $container->setParameter('netbull_translation.intl_extension_fallback.iso3166', $iso3166);
            $mergedValues = array_merge($iso639one, $iso639two);
            $container->setParameter('netbull_translation.intl_extension_fallback.iso639', $mergedValues);
            $container->setParameter('netbull_translation.intl_extension_fallback.script', $localeScript);

            $loader->load('validators.yaml');
            $loader->load('guessers.yaml');
            $loader->load('listeners.yaml');
            $loader->load('other.yaml');
        }

        $loader->load('forms.yaml');
        $container->getDefinition('netbull_translation.type.translations')
            ->replaceArgument(2, $config['allowed_locales']);

        $loader->load('services.yaml');
    }

    /**
     * @param ContainerBuilder $container
     * @param $name
     * @param $config
     */
    public function bindParameters(ContainerBuilder $container, $name, $config)
    {
        if (is_array($config) && empty($config[0])) {
            foreach ($config as $key => $value) {
                if (in_array($key, ['locale_map', 'country_map'])) {
                    //need a assoc array here
                    $container->setParameter($name . '.' . $key, $value);
                } else {
                    $this->bindParameters($container, $name . '.' . $key, $value);
                }
            }
        } else {
            $container->setParameter($name, $config);
        }
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return 'netbull_translation';
    }
}
