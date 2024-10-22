<?php

namespace Partitech\SonataExtra\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Partitech\SonataExtra\Entity\Article;
use Partitech\SonataExtra\Enum\ArticleStatus;
use Sonata\PageBundle\Model\SiteInterface;

class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function save(Article $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Article $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findPublishedByCategory($categoryEntity): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.status = :status')
            ->andWhere(':category MEMBER OF a.category')
            ->setParameter('status', ArticleStatus::PUBLISHED->value)
            ->setParameter('category', $categoryEntity)
            ->orderBy('a.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function QueryPublishedByCategory($categoryEntity, SiteInterface $site): Query
    {
        return $this->createQueryBuilder('a')
            ->where('a.status = :status')
            ->andWhere('a.site = :site')
            ->andWhere(':category MEMBER OF a.category')
            ->setParameter('status', ArticleStatus::PUBLISHED->value)
            ->setParameter('category', $categoryEntity)
            ->setParameter('site', $site->getId())
            ->orderBy('a.publishedAt', 'DESC')
            ->getQuery()
        ;
    }
    
    public function QueryPublishedByKeyWord($searchTerm, SiteInterface $site): mixed
    {

        $query = $this->createQueryBuilder('a')
                    ->where('a.status = :status')
                    ->andWhere('a.site = :site')
                    ->andWhere('MATCH(a.content) AGAINST (:content) > 0')
                    ->setParameter('site', $site->getId())
                    ->setParameter('status', ArticleStatus::PUBLISHED->value)
                    ->setParameter('content', '%' . $searchTerm . '%')
                    ->getQuery()
            ;
        return $query->getResult();
    }
    
    
    public function findPublishedByTag($categoryEntity): mixed
    {
        return $this->createQueryBuilder('a')
            ->where('a.status = :status')
            ->andWhere(':tag MEMBER OF a.tag')
            ->setParameter('status', ArticleStatus::PUBLISHED->value)
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
            ->setParameter('status', ArticleStatus::PUBLISHED->value)
            ->setParameter('tag', $tagEntity)
            ->orderBy('a.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findBySite($siteId): mixed
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.site = :val')
            ->setParameter('val', $siteId)
            ->getQuery()
            ->getResult();
    }
    
    public function findPublishedBySite(SiteInterface $site): mixed
    {
        return $this->createQueryBuilder('a')
                    ->andWhere('a.site = :site')
                    ->andWhere('a.status = :status')
                    ->setParameter('site', $site->getId())
                    ->setParameter('status', ArticleStatus::PUBLISHED->value)
                    ->getQuery()
                    ->getResult();
    }
}
