<?php

namespace Partitech\SonataExtra\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Partitech\SonataExtra\Entity\Editor;
use Partitech\SonataExtra\Enum\EditorStatus;

class EditorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Editor::class);
    }

    public function save(Editor $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Editor $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findPublishedByCategory($categoryEntity): mixed
    {
        return $this->createQueryBuilder('a')
            ->where('a.status = :status')
            ->andWhere(':category MEMBER OF a.category')
            ->setParameter('status', EditorStatus::PUBLISHED->value)
            ->setParameter('category', $categoryEntity)
            ->orderBy('a.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function QueryPublishedByCategory($categoryEntity): Query
    {
        return $this->createQueryBuilder('a')
            ->where('a.status = :status')
            ->andWhere(':category MEMBER OF a.category')
            ->setParameter('status', EditorStatus::PUBLISHED->value)
            ->setParameter('category', $categoryEntity)
            ->orderBy('a.publishedAt', 'DESC')
            ->getQuery()
        ;
    }

    public function findPublishedByTag($categoryEntity): mixed
    {
        return $this->createQueryBuilder('a')
            ->where('a.status = :status')
            ->andWhere(':tag MEMBER OF a.tag')
            ->setParameter('status', EditorStatus::PUBLISHED->value)
            ->setParameter('category', $categoryEntity)
            ->orderBy('a.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function QueryPublishedByTag($tagEntity): mixed
    {
        return $this->createQueryBuilder('a')
            ->where('a.status = :status')
            ->andWhere(':tag MEMBER OF a.tags')
            ->setParameter('status', EditorStatus::PUBLISHED->value)
            ->setParameter('tag', $tagEntity)
            ->orderBy('a.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
