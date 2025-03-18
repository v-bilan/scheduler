<?php

namespace App\Repository;

use App\Entity\TaskWitnessDate;
use App\Repository\Traits\ItemsById;
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
