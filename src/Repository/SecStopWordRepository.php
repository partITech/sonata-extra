<?php
namespace Partitech\SonataExtra\Repository;

use Partitech\SonataExtra\Entity\SecStopWord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SecStopWord|null find($id, $lockMode = null, $lockVersion = null)
 * @method SecStopWord|null findOneBy(array $criteria, array $orderBy = null)
 * @method SecStopWord[]    findAll()
 * @method SecStopWord[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SecStopWordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SecStopWord::class);
    }

    // Custom methods can be added here
}
