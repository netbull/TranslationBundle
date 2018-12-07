<?php

namespace NetBull\TranslationBundle\ORM\Subscribers\Translation;

use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Class TranslationSubscriber
 * @package NetBull\TranslationBundle\ORM\Subscribers\Translation
 */
class TranslationSubscriber extends \Knp\DoctrineBehaviors\ORM\Translatable\TranslatableSubscriber
{
    private $sluggableTrait = 'Knp\DoctrineBehaviors\Model\Sluggable\Sluggable';

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        parent::prePersist($eventArgs);

        $entity = $eventArgs->getEntity()->getTranslatableEntityClass();
        $em = $eventArgs->getEntityManager();
        $classMetadata = $em->getClassMetadata(get_class($entity));

        if ($this->isSluggable($classMetadata)) {
            $entity->generateSlug();
        }
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function preUpdate(LifecycleEventArgs $eventArgs)
    {
        parent::prePersist($eventArgs);

        $entity = $eventArgs->getEntity()->getTranslatableEntityClass();
        $em = $eventArgs->getEntityManager();
        $classMetadata = $em->getClassMetadata(get_class($entity));

        if ($this->isSluggable($classMetadata)) {
            $entity->generateSlug();
        }
    }

    /**
     * @inheritdoc
     */
    public function getSubscribedEvents()
    {
        $events = parent::getSubscribedEvents();
        $events[] = Events::preUpdate;

        return $events;
    }



    /**
     * Checks if entity is sluggable
     *
     * @param ClassMetadata $classMetadata The metadata
     *
     * @return boolean
     */
    private function isSluggable(ClassMetadata $classMetadata)
    {
        return $this->getClassAnalyzer()->hasTrait(
            $classMetadata->reflClass,
            $this->sluggableTrait,
            $this->isRecursive
        );
    }
}
