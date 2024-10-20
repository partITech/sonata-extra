<?php
    
    namespace Partitech\SonataExtra\Repository;
    
    use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
    use App\Entity\SonataClassificationCategory;
    use Doctrine\Persistence\ManagerRegistry;
    
    class ClassificationCategory extends ServiceEntityRepository
    {
        public function __construct(ManagerRegistry $registry)
        {
            parent::__construct($registry, SonataClassificationCategory::class);
        }
        
        
        public function findMediaBySiteId($siteId)
        {
            return $this->createQueryBuilder('classification_category')
                        ->innerJoin('classification_category.', 'page')
                        ->where('page.site = :siteId')
                        ->setParameter('siteId', $siteId)
                        ->getQuery()
                        ->getResult();
        }
        
    }