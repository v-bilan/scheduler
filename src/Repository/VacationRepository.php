<?php

namespace App\Repository;

use ApiPlatform\Metadata\GraphQl\Query;
use App\Entity\Vacation;
use App\Repository\Traits\ItemsById;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vacation>
 */
class VacationRepository extends ServiceEntityRepository implements Pageable
{
    use \App\Repository\Traits\Pageable;
    use ItemsById;

    private $sortableFields = ['id' => 'e.id', 'startDate' => 'e.startDate', 'endDate' => 'e.endDate', 'fullName' => 'Witness.fullName'];
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vacation::class);
    }

    public function attachWitnessesToBuilder(QueryBuilder $queryBuilder, $witness  = null)
    {
        $queryBuilder->innerJoin('e.witness', 'Witness')
            ->addSelect('Witness as w');
        if ($witness) {
            $queryBuilder->where('e.witness = :witness')
                ->setParameter('witness', $witness);
        }
    }

    //    /**
    //     * @return Vacation[] Returns an array of Vacation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('v.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Vacation
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
