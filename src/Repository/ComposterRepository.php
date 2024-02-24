<?php

namespace App\Repository;

use App\DBAL\Types\CapabilityEnumType;
use App\DBAL\Types\StatusEnumType;
use App\Entity\Composter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

class ComposterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Composter::class);
    }

    public function findAllWithUsers()
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.userComposters', 'uc')
            ->where('uc.capability IN (:user_capability)')
            ->andWhere('c.status IN (:map_status)')
            ->setParameters((new ArrayCollection([
                new Parameter('user_capability', [CapabilityEnumType::USER, CapabilityEnumType::OPENER]),
                new Parameter('map_status', [StatusEnumType::ACTIVE]),
            ])))
            ->getQuery()
            ->getResult();
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
