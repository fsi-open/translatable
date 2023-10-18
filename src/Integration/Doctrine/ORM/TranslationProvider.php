<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Component\Translatable\Integration\Doctrine\ORM;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use FSi\Component\Translatable;
use FSi\Component\Translatable\ConfigurationResolver;
use FSi\Component\Translatable\TranslationConfiguration;

use function get_class;

final class TranslationProvider implements Translatable\TranslationProvider
{
    private ManagerRegistry $managerRegistry;
    private ConfigurationResolver $configurationResolver;

    public function __construct(ManagerRegistry $managerRegistry, ConfigurationResolver $configurationResolver)
    {
        $this->managerRegistry = $managerRegistry;
        $this->configurationResolver = $configurationResolver;
    }

    public function createForEntityAndLocale(object $entity, string $locale): object
    {
        $translationConfiguration = $this->getTranslationConfiguration($entity);
        $translation = $translationConfiguration->creatNewEntityInstance();

        $translationConfiguration->setLocaleForEntity($translation, $locale);
        $translationConfiguration->setRelationValueForEntity($translation, $entity);

        return $translation;
    }

    public function findForEntityAndLocale(object $entity, string $locale): ?object
    {
        if (false === $this->entityHasAnIdentifier($entity)) {
            return null;
        }

        $translationConfiguration = $this->getTranslationConfiguration($entity);
        $translationsClass = $translationConfiguration->getEntityClass();

        return $this->getManagerForClass($translationsClass)
            ->createQueryBuilder()
            ->select('t')
            ->from($translationsClass, 't')
            ->where("t.{$translationConfiguration->getLocaleField()} = :locale")
            ->andWhere("t.{$translationConfiguration->getRelationField()} = :translatable")
            ->setParameter('locale', $locale)
            ->setParameter('translatable', $entity)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findAllForEntity(object $entity): array
    {
        $translationConfiguration = $this->getTranslationConfiguration($entity);
        $translationsClass = $translationConfiguration->getEntityClass();

        return $this->getManagerForClass($translationsClass)
            ->createQueryBuilder()
            ->select('t')
            ->from($translationsClass, 't')
            ->where("t.{$translationConfiguration->getRelationField()} = :translatable")
            ->setParameter('translatable', $entity)
            ->getQuery()
            ->getResult()
        ;
    }

    private function entityHasAnIdentifier(object $entity): bool
    {
        $entityClass = get_class($entity);
        $classMetadata = $this->getManagerForClass($entityClass)->getClassMetadata($entityClass);

        return 0 !== count($classMetadata->getIdentifierValues($entity));
    }

    private function getTranslationConfiguration(object $entity): TranslationConfiguration
    {
        return $this->configurationResolver->resolveTranslatable($entity)->getTranslationConfiguration();
    }

    /**
     * @param class-string $class
     */
    private function getManagerForClass(string $class): EntityManagerInterface
    {
        /** @var EntityManagerInterface $manager */
        $manager = $this->managerRegistry->getManagerForClass($class);
        return $manager;
    }
}
