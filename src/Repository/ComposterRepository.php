<?php

namespace App\Repository;

use App\Entity\Composter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ComposterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Composter::class);
    }

    public function findAllForFrontMap()
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.lat IS NOT NULL')
            ->andWhere('c.lng IS NOT NULL')
            ->andWhere('c.status IN (:map_status)')
            ->setParameter('map_status', ['Active', 'InProject'])
            ->getQuery()
            ->getResult();
    }

    public function findAllForCartoQuartierFrontMap()
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.lat IS NOT NULL')
            ->andWhere('c.lng IS NOT NULL')
            ->andWhere('c.categorie IN (:quartier_categorie_id)')
            ->andWhere('c.status = :map_status')
            ->setParameter('quartier_categorie_id', [1, 3])
            ->setParameter('map_status', 'Active')
            ->getQuery()
            ->getResult();
    }
}
