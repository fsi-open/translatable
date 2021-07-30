<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Component\Translatable\Entity;

use FSi\Component\Translatable\ConfigurationResolver;
use FSi\Component\Translatable\PropertyConfiguration;
use FSi\Component\Translatable\TranslationManager;
use FSi\Component\Translatable\TranslationProvider;

final class TranslationLoader
{
    private ConfigurationResolver $configurationResolver;
    private TranslationProvider $provider;
    private TranslationManager $manager;

    public function __construct(
        ConfigurationResolver $configurationResolver,
        TranslationProvider $provider,
        TranslationManager $manager
    ) {
        $this->configurationResolver = $configurationResolver;
        $this->provider = $provider;
        $this->manager = $manager;
    }

    public function load(object $entity, string $locale): void
    {
        $translatableConfiguration = $this->configurationResolver->resolveTranslatable($entity);
        $translatableConfiguration->setLocale($entity, $locale);

        $translation = $this->provider->findForEntityAndLocale($entity, $locale);
        if (null === $translation) {
            $this->manager->initializeTranslatableWithNoTranslation($entity);
            return;
        }

        $propertiesConfigurations = $translatableConfiguration->getPropertyConfigurations();
        $translationConfiguration = $translatableConfiguration->getTranslationConfiguration();
        array_walk(
            $propertiesConfigurations,
            function (PropertyConfiguration $configuration) use (
                $entity,
                $translation,
                $translationConfiguration
            ): void {
                $value = $translationConfiguration->getValueForProperty(
                    $translation,
                    $configuration->getPropertyName()
                );

                $configuration->setValueForEntity(
                    $entity,
                    $this->manager->sanitizeTranslationValue($value)
                );
            }
        );
    }
}
