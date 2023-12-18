<?php

namespace Partitech\SonataExtra\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Partitech\SonataExtra\Entity\SliderSlides;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * @extends ServiceEntityRepository<SliderSlides>
 *
 * @method SliderSlide|null find($id, $lockMode = null, $lockVersion = null)
 * @method SliderSlide|null findOneBy(array $criteria, array $orderBy = null)
 * @method SliderSlide[]    findAll()
 * @method SliderSlide[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
#[AutoconfigureTag(name: 'doctrine.repository_service')]
class SliderSlidesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SliderSlides::class);
    }

    public function save(SliderSlides $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SliderSlides $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
    //     * @return OptionsRefKeyWords[] Returns an array of OptionsRefKeyWords objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('o.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?OptionsRefKeyWords
    //    {
    //        return $this->createQueryBuilder('o')
    //            ->andWhere('o.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
