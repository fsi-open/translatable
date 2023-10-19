<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Component\Translatable;

use Assert\Assertion;

use function array_key_exists;
use function array_walk;
use function is_array;
use function is_object;
use function iterator_to_array;

final class ConfigurationResolver
{
    private ClassProvider $classProvider;
    /**
     * @var array<class-string, TranslatableConfiguration>
     */
    private array $translatableConfigurations = [];
    /**
     * @var array<class-string, TranslationConfiguration>
     */
    private array $translationConfigurations = [];

    /**
     * @param iterable<TranslatableConfiguration> $configurations
     */
    public function __construct(ClassProvider $classProvider, iterable $configurations)
    {
        $this->classProvider = $classProvider;

        if (false === is_array($configurations)) {
            $configurations = iterator_to_array($configurations);
        }

        array_walk(
            $configurations,
            function (TranslatableConfiguration $configuration): void {
                $this->translatableConfigurations[$configuration->getEntityClass()] = $configuration;
            }
        );

        array_walk(
            $configurations,
            function (TranslatableConfiguration $configuration): void {
                $translationConfiguration = $configuration->getTranslationConfiguration();
                $translationClass = $translationConfiguration->getEntityClass();
                $this->translationConfigurations[$translationClass] = $translationConfiguration;
            }
        );
    }

    /**
     * @param object|class-string<object> $entityOrClass
     */
    public function isTranslatable($entityOrClass): bool
    {
        return array_key_exists(
            true === is_object($entityOrClass)
                ? $this->classProvider->forObject($entityOrClass)
                : $entityOrClass,
            $this->translatableConfigurations
        );
    }

    /**
     * @param object|class-string<object> $entityOrClass
     */
    public function isTranslation($entityOrClass): bool
    {
        return array_key_exists(
            true === is_object($entityOrClass)
                ? $this->classProvider->forObject($entityOrClass)
                : $entityOrClass,
            $this->translationConfigurations
        );
    }

    /**
     * @param object|class-string<object> $entityOrClass
     */
    public function resolveTranslatable($entityOrClass): TranslatableConfiguration
    {
        if (true === is_object($entityOrClass)) {
            $entityOrClass = $this->classProvider->forObject($entityOrClass);
        }

        Assertion::true(
            $this->isTranslatable($entityOrClass),
            "\"{$entityOrClass}\" is not a translatable entity"
        );

        return $this->translatableConfigurations[$entityOrClass];
    }

    /**
     * @param object|class-string<object> $translationOrClass
     */
    public function resolveTranslation($translationOrClass): TranslationConfiguration
    {
        if (true === is_object($translationOrClass)) {
            $translationOrClass = $this->classProvider->forObject($translationOrClass);
        }

        Assertion::true(
            $this->isTranslation($translationOrClass),
            "\"{$translationOrClass}\" is not a translation entity"
        );

        return $this->translationConfigurations[$translationOrClass];
    }
}
