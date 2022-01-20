<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Component\Translatable\Integration\Symfony\DependencyInjection;

use FSi\Component\Translatable\ConfigurationResolver;
use FSi\Component\Translatable\TranslatableConfiguration;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

use function sprintf;

final class TranslatableExtension extends Extension
{
    public function getAlias(): string
    {
        return 'fsi_translatable';
    }

    /**
     * @param array{
     *   entities: array<class-string, array{
     *     localeField: string,
     *     disabledAutoTranslationsUpdate: bool,
     *     translation: array{
     *       class: class-string,
     *       localeField: string,
     *       relationField: string
     *     },
     *     fields: array<string>
     *   }>
     * } $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader(
            $container,
            new FileLocator(sprintf('%s/../Resources/config/services', __DIR__))
        );
        $loader->load('services.xml');

        /**
         * @var array{
         *   entities: array<class-string, array{
         *     localeField: string,
         *     disabledAutoTranslationsUpdate: bool,
         *     translation: array{
         *       class: class-string,
         *       localeField: string,
         *       relationField: string
         *     },
         *     fields: array<string>
         *   }>
         * } $configuration
         */
        $configuration = $this->processConfiguration(new Configuration(), $configs);
        $entityConfigurations = $this->createEntitiesFieldsConfigurations($configuration);

        $resolverDefinition = $container->getDefinition(ConfigurationResolver::class);
        $resolverDefinition->replaceArgument('$configurations', $entityConfigurations);
    }

    /**
     * @param array{
     *   entities: array<class-string, array{
     *     localeField: string,
     *     disabledAutoTranslationsUpdate: bool,
     *     translation: array{
     *       class: class-string,
     *       localeField: string,
     *       relationField: string
     *     },
     *     fields: array<string>
     *   }>
     * } $configuration
     * @return array<Definition>
     */
    private function createEntitiesFieldsConfigurations($configuration): array
    {
        $fieldsConfiguration = [];
        foreach ($configuration['entities'] as $class => $entityConfiguration) {
            $definition = new Definition(TranslatableConfiguration::class);
            $definition->setPublic(false);
            $definition->setArguments([
                $class,
                $entityConfiguration['localeField'],
                $entityConfiguration['disabledAutoTranslationsUpdate'],
                $entityConfiguration['translation']['class'],
                $entityConfiguration['translation']['localeField'],
                $entityConfiguration['translation']['relationField'],
                $entityConfiguration['fields']
            ]);

            $fieldsConfiguration[] = $definition;
        }

        return $fieldsConfiguration;
    }
}
