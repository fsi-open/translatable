<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Component\Translatable\Integration\Doctrine\ORM;

use Assert\Assertion;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\PersistentCollection;
use Doctrine\Persistence\ManagerRegistry;
use FSi\Component\Translatable;
use FSi\Component\Translatable\ConfigurationResolver;
use FSi\Component\Translatable\Integration\Doctrine\ORM\Entity\CollectionSynchronizer;
use FSi\Component\Translatable\Integration\Doctrine\ORM\Entity\EmptyEmbeddableVerifier;
use FSi\Component\Translatable\PropertyConfiguration;

use function array_reduce;
use function array_walk;
use function count;
use function get_class;
use function in_array;
use function is_object;

final class TranslationManager implements Translatable\TranslationManager
{
    private ConfigurationResolver $configurationResolver;
    private ManagerRegistry $managerRegistry;

    public function __construct(ConfigurationResolver $configurationResolver, ManagerRegistry $managerRegistry)
    {
        $this->configurationResolver = $configurationResolver;
        $this->managerRegistry = $managerRegistry;
    }

    public function initializeTranslatableWithNoTranslation(object $entity): void
    {
        $translatableConfiguration = $this->configurationResolver->resolveTranslatable($entity);
        $translationsClass = $translatableConfiguration->getTranslationConfiguration()->getEntityClass();

        /** @var ClassMetadata<object> $translationsClassMetadata */
        $translationsClassMetadata = $this->getManagerForClass($translationsClass)
            ->getClassMetadata($translationsClass)
        ;

        $propertiesConfiguratios = $translatableConfiguration->getPropertyConfigurations();
        array_walk(
            $propertiesConfiguratios,
            function (PropertyConfiguration $configuration) use ($translationsClassMetadata, $entity): void {
                $property = $configuration->getPropertyName();
                if (false === $translationsClassMetadata->isCollectionValuedAssociation($property)) {
                    return;
                }

                $configuration->setValueForEntity($entity, new ArrayCollection());
            }
        );
    }

    public function saveTranslation(object $translation): void
    {
        $this->getManagerForClass(get_class($translation))->persist($translation);
    }

    public function removeTranslation(object $translation): void
    {
        $this->getManagerForClass(get_class($translation))->remove($translation);
    }

    public function clearTranslationsForEntity(object $entity): void
    {
        $translationConfiguration = $this->configurationResolver
            ->resolveTranslatable($entity)
            ->getTranslationConfiguration()
        ;

        $translationClass = $translationConfiguration->getEntityClass();
        $manager = $this->getManagerForClass($translationClass);
        $translations = $manager->createQueryBuilder()
            ->select('t')
            ->from($translationClass, 't')
            ->where("t.{$translationConfiguration->getRelationField()} = :translatable")
            ->setParameter('translatable', $entity)
            ->getQuery()
            ->getResult()
        ;

        array_walk(
            $translations,
            static function (object $translation, int $key, EntityManagerInterface $manager): void {
                $manager->remove($translation);
            },
            $manager
        );
    }

    public function isTranslationEmpty(object $translation): bool
    {
        $translationConfiguration = $this->configurationResolver->resolveTranslation($translation);
        $manager = $this->getManagerForClass(get_class($translation));

        $allFieldsEmpty = $this->areAllTranslationFieldsEmpty(
            $translationConfiguration->getPropertyConfigurations(),
            $translation,
            EmptyEmbeddableVerifier::getEmbeddableClasses($manager, $translation)
        );

        return true === $allFieldsEmpty
            && true === EmptyEmbeddableVerifier::areAllEmpty($manager, $translation)
        ;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function sanitizeTranslationValue($value)
    {
        if (true === $value instanceof PersistentCollection) {
            $sanitizedValue = new ArrayCollection($value->toArray());
        } else {
            $sanitizedValue = $value;
        }

        return $value;
    }

    public function sanitizeTranslatableValue(
        object $translation,
        string $property,
        $translatableValue
    ) {
        $translationValue = $this->configurationResolver
            ->resolveTranslation($translation)
            ->getValueForProperty($translation, $property)
        ;

        if (true === $translationValue instanceof Collection) {
            Assertion::isInstanceOf($translatableValue, Collection::class);

            $translationClass = get_class($translation);
            CollectionSynchronizer::synchronize(
                $translation,
                $this->getManagerForClass($translationClass)->getClassMetadata($translationClass),
                $property,
                $translatableValue,
                $translationValue
            );

            $sanitizedValue = $translationValue;
        } else {
            $sanitizedValue = $translatableValue;
        }

        return $sanitizedValue;
    }

    /**
     * @param array<PropertyConfiguration> $propertyConfigurations
     * @param object $translation
     * @param array<class-string> $embeddableClasses
     * @return bool
     */
    private function areAllTranslationFieldsEmpty(
        array $propertyConfigurations,
        object $translation,
        array $embeddableClasses
    ): bool {
        return array_reduce(
            $propertyConfigurations,
            function (
                bool $accumulator,
                PropertyConfiguration $configuration
            ) use (
                $embeddableClasses,
                $translation
            ): bool {
                if (false === $accumulator) {
                    return $accumulator;
                }

                $value = $configuration->getValueForEntity($translation);
                if (false === $this->isValueEmpty($value, $embeddableClasses)) {
                    $accumulator = false;
                }

                return $accumulator;
            },
            true
        );
    }

    /**
     * @param mixed $value
     * @param array<class-string> $embeddableClasses
     * @return bool
     */
    private function isValueEmpty($value, array $embeddableClasses): bool
    {
        if (true === $value instanceof Collection) {
            $isEmpty = 0 === count($value);
        } elseif (true === is_object($value)) {
            // If an object is not an embeddable, then it is probably a one-to-one
            // relation, so it cannot be marked as empty. Embeddables are verified
            // separately, so here they are marked as empty to not give a false
            // negative.
            $isEmpty = in_array(get_class($value), $embeddableClasses, true);
        } else {
            $isEmpty = empty($value);
        }

        return $isEmpty;
    }

    /**
     * @param class-string $class
     * @return EntityManagerInterface
     */
    private function getManagerForClass(string $class): EntityManagerInterface
    {
        /** @var EntityManagerInterface $manager */
        $manager = $this->managerRegistry->getManagerForClass($class);
        return $manager;
    }
}
