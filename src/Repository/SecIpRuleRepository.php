<?php
namespace Partitech\SonataExtra\Repository;

use Partitech\SonataExtra\Entity\SecIpRule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SecIpRule|null find($id, $lockMode = null, $lockVersion = null)
 * @method SecIpRule|null findOneBy(array $criteria, array $orderBy = null)
 * @method SecIpRule[]    findAll()
 * @method SecIpRule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SecIpRuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SecIpRule::class);
    }

    // Custom methods can be added here
}
