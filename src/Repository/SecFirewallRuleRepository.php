<?php
namespace Partitech\SonataExtra\Repository;

use Partitech\SonataExtra\Entity\SecFirewallRule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SecFirewallRule|null find($id, $lockMode = null, $lockVersion = null)
 * @method SecFirewallRule|null findOneBy(array $criteria, array $orderBy = null)
 * @method SecFirewallRule[]    findAll()
 * @method SecFirewallRule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SecFirewallRuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SecFirewallRule::class);
    }

    // Custom methods can be added here
}
