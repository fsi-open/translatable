<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Component\Translatable\Integration\Doctrine\ORM\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

use function array_reduce;
use function get_class;
use function mb_strlen;
use function uksort;

final class EmptyEmbeddableVerifier
{
    private static ?PropertyAccessorInterface $propertyAccessor = null;
    /**
     * @var array<class-string, ClassMetadata<object>>
     */
    private static array $cachedMetadata = [];

    public static function areAllEmpty(EntityManagerInterface $manager, object $translation): bool
    {
        $classMetadata = self::getTranslationMetadata($manager, $translation);
        $embeddedClassesData = self::getAndSortEmbeddedDataByNestingLevel($classMetadata);
        $allEmpty = true;
        foreach ($embeddedClassesData as $fieldName => $embeddedData) {
            /** @var class-string $embeddableClass */
            $embeddableClass = $embeddedData['class'];
            $embeddedMeta = $manager->getClassMetadata($embeddableClass);
            $embeddedParentMeta = $classMetadata;
            $embeddedParentObject = $translation;

            if (true === isset($embeddedData['declaredField'])) {
                $embeddedParentObject = self::getPropertyAccessor()->getValue(
                    $translation,
                    $embeddedData['declaredField']
                );

                if (null === $embeddedParentObject) {
                    continue;
                }

                $embeddedParentData = $embeddedClassesData[$embeddedData['declaredField']];
                $embeddedParentMeta = $manager->getClassMetadata($embeddedParentData['class']);
                $fieldName = $embeddedData['originalField'];
            }

            $embeddedObject = $embeddedParentMeta->getFieldValue($embeddedParentObject, $fieldName);
            if (null === $embeddedObject) {
                continue;
            }

            $allEmpty = self::isEmbeddableEmpty($embeddedMeta, $embeddedObject);
            if (false === $allEmpty) {
                break;
            }
        }

        return $allEmpty;
    }

    /**
     * @param EntityManagerInterface $manager
     * @param object $translation
     * @return array<class-string>
     */
    public static function getEmbeddableClasses(EntityManagerInterface $manager, object $translation): array
    {
        $embeddedClassesData = self::getAndSortEmbeddedDataByNestingLevel(
            self::getTranslationMetadata($manager, $translation)
        );

        return array_map(
            static fn($embeddedData): string => $embeddedData['class'],
            $embeddedClassesData
        );
    }

    /**
     * @param ClassMetadata<object> $classMetadata
     * @param object $embeddable
     * @return bool
     */
    private static function isEmbeddableEmpty(ClassMetadata $classMetadata, object $embeddable): bool
    {
        return array_reduce(
            $classMetadata->getFieldNames(),
            static function (bool $accumulator, string $fieldName) use ($classMetadata, $embeddable): bool {
                if (false === $accumulator) {
                    return $accumulator;
                }

                if (null !== $classMetadata->getFieldValue($embeddable, $fieldName)) {
                    $accumulator = false;
                }

                return $accumulator;
            },
            true
        );
    }

    /**
     * @param ClassMetadata<object> $classMetadata
     * @return array<string, array{ class: class-string, declaredField?: string|null, originalField: string }>
     */
    private static function getAndSortEmbeddedDataByNestingLevel(ClassMetadata $classMetadata): array
    {
        /** @var array<string, array{ class: class-string, declaredField?: string|null, originalField: string }> $data */
        $data = $classMetadata->embeddedClasses;
        // Example data ["fieldName" => [], "fieldName.nestedFieldName" => []]
        uksort(
            $data,
            static fn(string $a, string $b): int => mb_strlen($b) - mb_strlen($a)
        );

        return $data;
    }

    private static function getPropertyAccessor(): PropertyAccessorInterface
    {
        if (null === self::$propertyAccessor) {
            self::$propertyAccessor = new PropertyAccessor();
        }

        return self::$propertyAccessor;
    }

    /**
     * @param EntityManagerInterface $manager
     * @param object $translation
     * @return ClassMetadata<object>
     */
    private static function getTranslationMetadata(
        EntityManagerInterface $manager,
        object $translation
    ): ClassMetadata {
        $translationClass = get_class($translation);
        if (false === array_key_exists($translationClass, self::$cachedMetadata)) {
            self::$cachedMetadata[$translationClass] = $manager->getClassMetadata($translationClass);
        }

        return self::$cachedMetadata[$translationClass];
    }
}
