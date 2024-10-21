<?php

namespace Partitech\SonataExtra\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Partitech\SonataExtra\Entity\EditorRevision;

/**
 * @extends ServiceEntityRepository<EditorRevision>
 *
 * @method EditorRevision|null find($id, $lockMode = null, $lockVersion = null)
 * @method EditorRevision|null findOneBy(array $criteria, array $orderBy = null)
 * @method EditorRevision[]    findAll()
 * @method EditorRevision[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EditorRevisionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EditorRevision::class);
    }

    public function save(EditorRevision $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(EditorRevision $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
