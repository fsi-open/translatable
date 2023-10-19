<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Component\Translatable\Integration\Doctrine\ORM\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\ClassMetadata;
use FSi\Component\Translatable\PropertyConfiguration;

final class CollectionSynchronizer
{
    /**
     * @param ClassMetadata<object> $metadata
     * @param Collection<string|int, object> $translatableCollection
     * @param Collection<string|int, object> $translationCollection
     */
    public static function synchronize(
        object $translation,
        ClassMetadata $metadata,
        string $collectionField,
        Collection $translatableCollection,
        Collection $translationCollection
    ): void {
        $relationType = $metadata->getAssociationMapping($collectionField)['type'];
        $targetRelationField = $metadata->getAssociationMappedByTargetField($collectionField);
        // Remove elements from collection which are not in the new set
        foreach ($translationCollection as $translationElement) {
            if (true === $translatableCollection->contains($translationElement)) {
                continue;
            }

            self::removeFromRelation(
                $relationType,
                $translation,
                $translationElement,
                $targetRelationField
            );

            $translationCollection->removeElement($translationElement);
        }

        // Add new elements to current collection
        foreach ($translatableCollection as $translatableElement) {
            if (true === $translationCollection->contains($translatableElement)) {
                continue;
            }

            self::addToRelation(
                $relationType,
                $translation,
                $translatableElement,
                $targetRelationField
            );

            $translationCollection->add($translatableElement);
        }
    }

    private static function addToRelation(
        int $relationType,
        object $translation,
        object $collectionElement,
        ?string $targetField
    ): void {
        if (null === $targetField) {
            // one-sided relation, no property to set relation on
            return;
        }

        $propertyConfiguration = self::getPropertyConfiguration($collectionElement, $targetField);
        if (true === self::isManyToMany($relationType)) {
            /** @var Collection<int|string, object> $inversedCollection */
            $inversedCollection = $propertyConfiguration->getValueForEntity($collectionElement);
            $inversedCollection->add($translation);
        } else {
            $propertyConfiguration->setValueForEntity($collectionElement, $translation);
        }
    }

    private static function removeFromRelation(
        int $relationType,
        object $translation,
        object $collectionElement,
        ?string $targetField
    ): void {
        if (null === $targetField) {
            // one-sided relation, no property to set relation on
            return;
        }

        $propertyConfiguration = self::getPropertyConfiguration($collectionElement, $targetField);
        if (true === self::isManyToMany($relationType)) {
            /** @var Collection<int|string, object> $inversedCollection */
            $inversedCollection = $propertyConfiguration->getValueForEntity($collectionElement);
            $inversedCollection->removeElement($translation);
        } else {
            $propertyConfiguration->setValueForEntity($collectionElement, null);
        }
    }

    private static function isManyToMany(int $relationType): bool
    {
        return ClassMetadata::MANY_TO_MANY === $relationType;
    }

    private static function getPropertyConfiguration(object $entity, string $property): PropertyConfiguration
    {
        return new PropertyConfiguration(get_class($entity), $property);
    }
}
