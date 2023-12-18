<?php

namespace Partitech\SonataExtra\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Partitech\SonataExtra\Entity\AdminActivityLog;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(name: 'doctrine.repository_service')]
class AdminActivityLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdminActivityLog::class);
    }

    public function findByTokenExceptId($token, $id)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.token = :token')
            ->andWhere('a.id != :id')
            ->setParameter('token', $token)
            ->setParameter('id', $id)
            ->orderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
