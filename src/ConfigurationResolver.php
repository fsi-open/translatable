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
use function get_class;
use function is_array;
use function iterator_to_array;

final class ConfigurationResolver
{
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
    public function __construct(iterable $configurations)
    {
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

    public function isTranslatable(object $entity): bool
    {
        return array_key_exists(get_class($entity), $this->translatableConfigurations);
    }

    public function resolveTranslatable(object $entity): TranslatableConfiguration
    {
        $class = get_class($entity);
        Assertion::keyExists(
            $this->translatableConfigurations,
            $class,
            "\"{$class}\" is not a translatable entity"
        );

        return $this->translatableConfigurations[$class];
    }

    public function resolveTranslation(object $translation): TranslationConfiguration
    {
        $class = get_class($translation);
        Assertion::keyExists(
            $this->translationConfigurations,
            $class,
            "\"{$class}\" is not a translation entity"
        );

        return $this->translationConfigurations[$class];
    }
}
