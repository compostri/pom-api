<?php

namespace App\Repository;

use App\Entity\Permanence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Permanence|null find($id, $lockMode = null, $lockVersion = null)
 * @method Permanence|null findOneBy(array $criteria, array $orderBy = null)
 * @method Permanence[]    findAll()
 * @method Permanence[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PermanenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Permanence::class);
    }

    /**
     * @throws
     * @return Permanence[] Returns an array of Permanence objects
     */
    public function findAllToNotify()
    {
        $today = new \DateTime();
        $dateMax = new \DateTime();
        $dateMax->add(new \DateInterval('P3D'));

        return $this->createQueryBuilder('p')
            ->andWhere('p.date > :date')
            ->andWhere('p.date < :dateMax')
            ->andWhere('p.hasUsersBeenNotify = :has_users_been_notify')
            ->setParameter('date', $today)
            ->setParameter('dateMax', $dateMax)
            ->setParameter('has_users_been_notify', false)
            ->orderBy('p.date', 'DESC')
            ->setMaxResults(100)
            ->getQuery()
            ->getResult()
        ;
    }
}
