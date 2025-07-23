<?php

namespace NetBull\TranslationBundle\ORM\Subscribers\Translation;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use NetBull\DoctrineBehaviors\Contract\Entity\SluggableInterface;
use NetBull\DoctrineBehaviors\Contract\Entity\TranslationInterface;

#[AsDoctrineListener(event: Events::onFlush)]
class TranslationListener
{
    /**
     * @param OnFlushEventArgs $eventArgs
     * @return void
     */
    public function onFlush(OnFlushEventArgs $eventArgs): void
    {
        $om = $eventArgs->getObjectManager();
        $uow = $om->getUnitOfWork();

        $insertions = $this->getEntities($om, $uow->getScheduledEntityInsertions());
        $updates = $this->getEntities($om, $uow->getScheduledEntityUpdates());

        foreach (array_merge($insertions, $updates) as $entity) {
            $entity->generateSlug();
            $om->persist($entity);
            $classMetadata = $om->getClassMetadata(get_class($entity));
            $uow->computeChangeSet($classMetadata, $entity);
        }
    }

    /**
     * @param EntityManagerInterface $em
     * @param array $entities
     * @return array
     */
    private function getEntities(EntityManagerInterface $em, array $entities): array
    {
        $output = [];
        foreach ($entities as $entity) {
            $classMetadata = $em->getClassMetadata(get_class($entity));

            if (!is_a($classMetadata->reflClass->getName(), TranslationInterface::class, true)) {
                continue;
            }

            $translatable = $entity->getTranslatable();
            $classMetadata = $em->getClassMetadata(get_class($translatable));

            if (is_a($classMetadata->reflClass->getName(), SluggableInterface::class, true)) {
                $output[] = $translatable;
            }
        }

        return $output;
    }
}
