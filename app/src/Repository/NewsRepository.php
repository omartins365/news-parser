<?php

namespace App\Repository;

use App\Entity\News;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class NewsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, News::class);
    }

    public function findAllOrderedByDate()
    {
        return $this->createQueryBuilder('n')
            ->orderBy('n.date', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findLatest($count)
    {
        return $this->createQueryBuilder('n')
            ->orderBy('n.date', 'DESC')
            ->setMaxResults($count)
            ->getQuery()
            ->getResult()
        ;
    }

    public function deleteNewsItem(News $newsItem)
    {
        $this->getEntityManager()->remove($newsItem);
        $this->getEntityManager()->flush();
    }
}
