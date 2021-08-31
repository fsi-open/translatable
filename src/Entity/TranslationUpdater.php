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
use FSi\Component\Translatable\ConfigurationResolver;
use FSi\Component\Translatable\PropertyConfiguration;
use FSi\Component\Translatable\TranslationConfiguration;
use FSi\Component\Translatable\TranslationManager;
use FSi\Component\Translatable\TranslationProvider;

use function array_walk;

final class TranslationUpdater
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

    public function update(object $entity): void
    {
        $translatableConfiguration = $this->configurationResolver->resolveTranslatable($entity);
        $locale = $translatableConfiguration->getLocale($entity);
        Assertion::notNull(
            $locale,
            "No locale set for entity of class \"{$translatableConfiguration->getEntityClass()}\""
        );

        $translation = $this->provider->findForEntityAndLocale($entity, $locale);
        $shouldNewTranslationBeCreated = null === $translation;
        if (true === $shouldNewTranslationBeCreated) {
            $translation = $this->provider->createForEntityAndLocale($entity, $locale);
        }

        $this->updateTranslationObject(
            $translatableConfiguration->getPropertyConfigurations(),
            $entity,
            $translation,
            $translatableConfiguration->getTranslationConfiguration()
        );

        $isTranslationEmpty = $this->manager->isTranslationEmpty($translation);
        if (true === $shouldNewTranslationBeCreated && false === $isTranslationEmpty) {
            $this->manager->saveTranslation($translation);
        } elseif (false === $shouldNewTranslationBeCreated && true === $isTranslationEmpty) {
            $this->manager->removeTranslation($translation);
        }
    }

    /**
     * @param array<PropertyConfiguration> $propertiesConfigurations
     * @param object $entity
     * @param object $translation
     * @param TranslationConfiguration $translationConfiguration
     * @return void
     */
    private function updateTranslationObject(
        array $propertiesConfigurations,
        object $entity,
        object $translation,
        TranslationConfiguration $translationConfiguration
    ): void {
        array_walk(
            $propertiesConfigurations,
            function (PropertyConfiguration $configuration) use (
                $entity,
                $translation,
                $translationConfiguration
            ): void {
                $property = $configuration->getPropertyName();
                $value = $this->manager->sanitizeTranslatableValue(
                    $translation,
                    $property,
                    $configuration->getValueForEntity($entity)
                );

                $translationConfiguration->setValueForProperty($translation, $property, $value);
            }
        );
    }
}
