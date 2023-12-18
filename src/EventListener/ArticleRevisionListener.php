<?php

namespace Partitech\SonataExtra\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Partitech\SonataExtra\Entity\Article;
use Partitech\SonataExtra\Entity\ArticleRevision;

class ArticleRevisionListener
{
    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof Article) {
                $revision = new ArticleRevision();
                $revision->setContent($entity->getContent());
                $revision->setTitle($entity->getTitle());
                $revision->setAuthor($entity->getAuthor());
                $revision->setRevisionDate(new \DateTime());
                $revision->setContent($entity->getContent());
                $revision->setStatus($entity->getStatus());
                $revision->setSlug($entity->getSlug());
                $revision->setFeaturedImage($entity->getFeaturedImage());

                $revision->setSeoTitle($entity->getSeoTitle());
                $revision->setSeoDescription($entity->getSeoDescription());
                $revision->setSeoKeywords($entity->getSeoKeywords());

                $revision->setSeoOgTitle($entity->getSeoOgTitle());
                $revision->setSeoOgDescription($entity->getSeoOgDescription());
                $revision->setSeoOgImage($entity->getSeoOgImage());

                foreach ($entity->getCategory() as $category) {
                    $revision->getCategory()->add($category);
                }
                foreach ($entity->getTags() as $tag) {
                    $revision->getTags()->add($tag);
                }

                $revision->setPublishedAt($entity->getPublishedAt());

                $revision->setArticle($entity);

                $em->persist($revision);
                $uow->computeChangeSet(
                    $em->getClassMetadata(ArticleRevision::class),
                    $revision
                );
            }
        }

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof Article) {
                $revision = new ArticleRevision();
                $revision->setContent($entity->getContent());
                $revision->setTitle($entity->getTitle());
                $revision->setAuthor($entity->getAuthor());
                $revision->setRevisionDate(new \DateTime());
                $revision->setContent($entity->getContent());
                $revision->setStatus($entity->getStatus());
                $revision->setPublishedAt($entity->getPublishedAt());
                $revision->setSlug($entity->getSlug());
                $revision->setArticle($entity);
                $revision->setFeaturedImage($entity->getFeaturedImage());

                foreach ($entity->getCategory() as $category) {
                    $revision->getCategory()->add($category);
                }
                foreach ($entity->getTags() as $tag) {
                    $revision->getTags()->add($tag);
                }

                $em->persist($revision);
                $uow->computeChangeSet(
                    $em->getClassMetadata(ArticleRevision::class),
                    $revision
                );
            }
        }
    }
}
