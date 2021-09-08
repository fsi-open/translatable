<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Component\Translatable\Entity;

use Assert\Assertion;
use FSi\Component\Translatable\ClassProvider;
use FSi\Component\Translatable\ConfigurationResolver;
use FSi\Component\Translatable\PropertyConfiguration;
use FSi\Component\Translatable\TranslatableConfiguration;
use FSi\Component\Translatable\TranslationManager;
use FSi\Component\Translatable\TranslationProvider;

use function array_walk;
use function get_class;
use function sprintf;

final class TranslationLoader
{
    private ConfigurationResolver $configurationResolver;
    private TranslationProvider $provider;
    private TranslationManager $manager;
    private ClassProvider $classProvider;

    public function __construct(
        ConfigurationResolver $configurationResolver,
        TranslationProvider $provider,
        TranslationManager $manager,
        ClassProvider $classProvider
    ) {
        $this->configurationResolver = $configurationResolver;
        $this->provider = $provider;
        $this->manager = $manager;
        $this->classProvider = $classProvider;
    }

    public function loadFromLocale(object $entity, string $locale): void
    {
        $translatableConfiguration = $this->configurationResolver->resolveTranslatable($entity);
        $translatableConfiguration->setLocale($entity, $locale);

        $translation = $this->provider->findForEntityAndLocale($entity, $locale);
        if (null === $translation) {
            $this->manager->initializeTranslatableWithNoTranslation($entity);
            return;
        }

        $this->loadTranslationFields($translatableConfiguration, $entity, $translation);
    }

    public function loadFromTranslation(object $entity, object $translation): void
    {
        $translatableConfiguration = $this->configurationResolver->resolveTranslatable($entity);
        $translationConfiguration = $translatableConfiguration->getTranslationConfiguration();

        Assertion::same(
            $this->classProvider->forObject($translation),
            $translationConfiguration->getEntityClass()
        );
        Assertion::same(
            $entity,
            $translationConfiguration->getRelationValueForEntity($translation),
            sprintf(
                'Object of class "%s" has a different relation object for class "%s"',
                get_class($translation),
                get_class($entity)
            )
        );

        $locale = $translationConfiguration->getLocaleForEntity($translation);
        Assertion::notNull($locale, sprintf('No locale for entity "%s"', get_class($translation)));
        $translatableConfiguration->setLocale($entity, $locale);

        $this->loadTranslationFields($translatableConfiguration, $entity, $translation);
    }

    private function loadTranslationFields(
        TranslatableConfiguration $translatableConfiguration,
        object $entity,
        object $translation
    ): void {
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
