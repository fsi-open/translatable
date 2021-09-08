<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Component\Translatable\Integration\Doctrine\ORM\Subscriber;

use Assert\Assertion;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Persistence\Proxy;
use FSi\Component\Translatable\ConfigurationResolver;
use FSi\Component\Translatable\Entity\TranslationCleaner;
use FSi\Component\Translatable\Entity\TranslationLoader;
use FSi\Component\Translatable\Entity\TranslationUpdater;
use FSi\Component\Translatable\LocaleProvider;

use function array_walk;
use function get_class;

final class EntitySubscriber implements EventSubscriber
{
    private LocaleProvider $localeProvider;
    private ConfigurationResolver $entityConfigurationResolver;
    private TranslationLoader $translationLoader;
    private TranslationUpdater $translationUpdater;
    private TranslationCleaner $translationCleaner;

    public function __construct(
        LocaleProvider $localeProvider,
        ConfigurationResolver $entityConfigurationResolver,
        TranslationLoader $translationLoader,
        TranslationUpdater $translationUpdater,
        TranslationCleaner $translationCleaner
    ) {
        $this->localeProvider = $localeProvider;
        $this->entityConfigurationResolver = $entityConfigurationResolver;
        $this->translationLoader = $translationLoader;
        $this->translationUpdater = $translationUpdater;
        $this->translationCleaner = $translationCleaner;
    }

    /**
     * @return array<string>
     */
    public function getSubscribedEvents(): array
    {
        return [Events::postLoad, Events::preRemove, Events::preFlush, Events::onFlush];
    }

    public function postLoad(LifecycleEventArgs $event): void
    {
        $entity = $event->getEntity();
        if (false === $this->isTranslatable($entity)) {
            return;
        }

        $this->callIterativelyForObjectAndItsEmbbedables(
            [$this->translationLoader, 'loadFromLocale'],
            [$this->localeProvider->getLocale()],
            $event->getEntityManager(),
            $entity
        );
    }

    public function preRemove(LifecycleEventArgs $event): void
    {
        $object = $event->getEntity();
        if (false === $this->isTranslatable($object)) {
            return;
        }

        $this->translationCleaner->clean($object);
    }

    public function preFlush(PreFlushEventArgs $eventArgs): void
    {
        /** @var EntityManagerInterface $manager */
        $manager = $eventArgs->getEntityManager();
        $uow = $manager->getUnitOfWork();
        if (true === $this->isDeepNestedTransaction($manager)) {
            return;
        }

        $locale = $this->localeProvider->getLocale();
        $scheduledInsertions = $uow->getScheduledEntityInsertions();
        array_walk(
            $scheduledInsertions,
            function (object $entity, $key, string $locale) use ($manager): void {
                if (false === $this->isTranslatable($entity)) {
                    return;
                }

                $this->setEntityLocaleIfIsNull($entity, $locale);

                $this->callIterativelyForObjectAndItsEmbbedables(
                    [$this->translationUpdater, 'update'],
                    [],
                    $manager,
                    $entity
                );
            },
            $locale
        );

        $identityMap = $uow->getIdentityMap();
        array_walk($identityMap, function (array $entities) use ($manager): void {
            array_walk($entities, function (object $entity) use ($manager): void {
                if (false === $this->isTranslatable($entity)) {
                    return;
                }

                $this->callIterativelyForObjectAndItsEmbbedables(
                    [$this->translationUpdater, 'update'],
                    [],
                    $manager,
                    $entity
                );
            });
        });
    }

    public function onFlush(OnFlushEventArgs $event): void
    {
        $manager = $event->getEntityManager();
        if (true === $this->isDeepNestedTransaction($manager)) {
            return;
        }

        $locale = $this->localeProvider->getLocale();
        $uow = $manager->getUnitOfWork();
        $scheduledInsertions = $uow->getScheduledEntityInsertions();
        array_walk(
            $scheduledInsertions,
            function (object $entity) use ($manager, $locale): void {
                $this->initializeEmptyTranslatableForNewTranslation($manager, $entity, $locale);
            }
        );
    }

    /**
     * @param callable $callable
     * @param array<mixed> $callableArguments
     * @param EntityManagerInterface $manager
     * @param object $object
     * @return void
     */
    private function callIterativelyForObjectAndItsEmbbedables(
        callable $callable,
        array $callableArguments,
        EntityManagerInterface $manager,
        object $object
    ): void {
        $callable($object, ...$callableArguments);

        /** @var ClassMetadataInfo<object> $metadata */
        $metadata = $manager->getClassMetadata(get_class($object));
        array_walk(
            $metadata->embeddedClasses,
            function (
                array $configuration,
                string $property,
                callable $callable
            ) use (
                $object,
                $manager,
                $metadata,
                $callableArguments
            ): void {
                if (null !== $configuration['declaredField'] || null !== $configuration['originalField']) {
                    return;
                }

                $embeddable = $metadata->getFieldValue($object, $property);
                if (null === $embeddable) {
                    return;
                }

                $this->callIterativelyForObjectAndItsEmbbedables(
                    $callable,
                    $callableArguments,
                    $manager,
                    $embeddable
                );
            },
            $callable
        );
    }

    private function initializeEmptyTranslatableForNewTranslation(
        EntityManagerInterface $manager,
        object $translation,
        string $locale
    ): void {
        if (false === $this->isTranslation($translation)) {
            return;
        }

        $translationConfiguration = $this->entityConfigurationResolver->resolveTranslation($translation);
        $translationLocale = $translationConfiguration->getLocaleForEntity($translation);
        Assertion::notNull(
            $translationLocale,
            sprintf('No locale for translation of class "%s".', get_class($translation))
        );

        if ($translationLocale !== $locale) {
            return;
        }

        $entity = $translationConfiguration->getRelationValueForEntity($translation);
        Assertion::notNull(
            $entity,
            sprintf('No relation entity for translation of class "%s".', get_class($translation))
        );

        $translatableConfiguration = $this->entityConfigurationResolver->resolveTranslatable($entity);
        if ($translatableConfiguration->getLocale($entity) !== $translationLocale) {
            return;
        }

        $this->callIterativelyForObjectAndItsEmbbedables(
            [$this->translationLoader, 'loadFromTranslation'],
            [$translation],
            $manager,
            $entity
        );
    }

    private function setEntityLocaleIfIsNull(object $entity, string $locale): void
    {
        $configuration = $this->entityConfigurationResolver->resolveTranslatable($entity);
        if (null !== $configuration->getLocale($entity)) {
            return;
        }

        $configuration->setLocale($entity, $locale);
    }

    private function isDeepNestedTransaction(EntityManagerInterface $manager): bool
    {
        return 1 < $manager->getConnection()->getTransactionNestingLevel();
    }

    private function isTranslatable(object $object): bool
    {
        $this->initializeProxy($object);
        return $this->entityConfigurationResolver->isTranslatable($object);
    }

    private function isTranslation(object $object): bool
    {
        $this->initializeProxy($object);
        return $this->entityConfigurationResolver->isTranslation($object);
    }

    private function initializeProxy(object $object): void
    {
        if (true === $object instanceof Proxy && false === $object->__isInitialized()) {
            $object->__load();
        }
    }
}
