<?php

namespace Partitech\SonataExtra\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Partitech\SonataExtra\Entity\Editor;
use Partitech\SonataExtra\Entity\EditorRevision;

class EditorRevisionListener
{
    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof Editor) {
                $revision = new EditorRevision();
                $revision->setContent($entity->getContent());
                $revision->setTitle($entity->getTitle());
                $revision->setAuthor($entity->getAuthor());
                $revision->setRevisionDate(new \DateTime());
                $revision->setContent($entity->getContent());
                $revision->setStatus($entity->getStatus());
                $revision->setFeaturedImage($entity->getFeaturedImage());

                $revision->setPublishedAt($entity->getPublishedAt());

                $revision->setEditor($entity);

                $em->persist($revision);
                $uow->computeChangeSet(
                    $em->getClassMetadata(EditorRevision::class),
                    $revision
                );
            }
        }

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof Editor) {
                $revision = new EditorRevision();
                $revision->setContent($entity->getContent());
                $revision->setTitle($entity->getTitle());
                $revision->setAuthor($entity->getAuthor());
                $revision->setRevisionDate(new \DateTime());
                $revision->setContent($entity->getContent());
                $revision->setStatus($entity->getStatus());
                $revision->setPublishedAt($entity->getPublishedAt());
                $revision->setEditor($entity);
                $revision->setFeaturedImage($entity->getFeaturedImage());

                $em->persist($revision);
                $uow->computeChangeSet(
                    $em->getClassMetadata(EditorRevision::class),
                    $revision
                );
            }
        }
    }
}
