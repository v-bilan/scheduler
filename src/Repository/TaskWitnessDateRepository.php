<?php

namespace App\Repository;

use App\Entity\TaskWitnessDate;
use App\Entity\Witness;
use App\Repository\Traits\ItemsById;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TaskWitnessDate>
 */
class TaskWitnessDateRepository extends ServiceEntityRepository
{
    use ItemsById;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaskWitnessDate::class);
    }
    public function getWithWitness($date)
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.Witness', 'Witness')
            ->addSelect('Witness')
            ->andWhere('t.date = :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult();
    }

    public function findByRange($dateFrom, $dateTo): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.Witness', 'Witness')
            ->addSelect('Witness')
            ->andWhere('t.date >= :dateFrom')
            ->andWhere('t.date <= :dateTo')
            ->setParameter('dateFrom', $dateFrom)
            ->setParameter('dateTo', $dateTo)
            ->orderBy('t.date', 'ASC')
            ->getQuery()
            ->getResult();
    }
    //    /**
    //     * @return TaskWitnessDate[] Returns an array of TaskWitnessDate objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?TaskWitnessDate
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
