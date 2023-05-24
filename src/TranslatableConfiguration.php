<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Component\Translatable;

final class TranslatableConfiguration
{
    /**
     * @var class-string
     */
    private string $translatableClass;
    private string $localeField;
    private bool $disabledAutoTranslationsUpdate;
    private TranslationConfiguration $translationsConfiguration;
    private ?PropertyConfiguration $localeFieldReflection;
    /**
     * @var array<string, PropertyConfiguration>
     */
    private array $propertyConfigurations;

    /**
     * @param class-string $translatableClass
     * @param string $localeField
     * @param bool $disabledAutoTranslationsUpdate
     * @param class-string $translationsClass
     * @param string $translationsLocaleField
     * @param string $translationsPropertyField
     * @param array<string> $properties
     */
    public function __construct(
        string $translatableClass,
        string $localeField,
        bool $disabledAutoTranslationsUpdate,
        string $translationsClass,
        string $translationsLocaleField,
        string $translationsPropertyField,
        array $properties
    ) {
        $this->translatableClass = $translatableClass;
        $this->localeField = $localeField;
        $this->disabledAutoTranslationsUpdate = $disabledAutoTranslationsUpdate;
        $this->localeFieldReflection = null;
        $this->translationsConfiguration = new TranslationConfiguration(
            $translationsClass,
            $translationsLocaleField,
            $translationsPropertyField,
            $properties
        );

        $this->propertyConfigurations = array_reduce(
            $properties,
            static function (array $accumulator, string $property) use ($translatableClass): array {
                $accumulator[$property] = new PropertyConfiguration($translatableClass, $property);
                return $accumulator;
            },
            []
        );
    }

    public function isPropertyTranslatable(string $property): bool
    {
        return array_key_exists($property, $this->propertyConfigurations);
    }

    public function isDisabledAutoTranslationsUpdate(): bool
    {
        return $this->disabledAutoTranslationsUpdate;
    }

    /**
     * @return class-string
     */
    public function getEntityClass(): string
    {
        return $this->translatableClass;
    }

    public function getLocale(object $entity): ?string
    {
        return $this->getLocaleFieldReflection()->getValueForEntity($entity);
    }

    public function setLocale(object $entity, string $locale): void
    {
        $this->getLocaleFieldReflection()->setValueForEntity($entity, $locale);
    }

    public function getTranslationConfiguration(): TranslationConfiguration
    {
        return $this->translationsConfiguration;
    }

    /**
     * @return array<PropertyConfiguration>
     */
    public function getPropertyConfigurations(): array
    {
        return $this->propertyConfigurations;
    }

    private function getLocaleFieldReflection(): PropertyConfiguration
    {
        if (null === $this->localeFieldReflection) {
            $this->localeFieldReflection = new PropertyConfiguration(
                $this->translatableClass,
                $this->localeField
            );
        }

        return $this->localeFieldReflection;
    }
}
