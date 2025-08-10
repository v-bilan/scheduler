<?php

namespace App\Repository;

use App\Entity\Witness;
use App\Repository\Traits\ItemsById;
use App\Util\Date;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Witness>
 */
class WitnessRepository extends ServiceEntityRepository implements Pageable
{
    use \App\Repository\Traits\Pageable;
    use ItemsById;

    private $sortableFields = ['id' => 'e.id', 'fullName' => 'e.fullName', 'active' => 'e.active'];
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Witness::class);
    }

    public function getWitnessesByRole(string $role, Date $date)
    {
        return $this->createQueryBuilder('w')
            ->select('w.id AS witness_id')
            ->addSelect('w.fullName')
            ->addSelect('MAX(twd.date) AS last_date')
            ->addSelect('r.name AS role_name')
            ->addSelect('r.id as role_id')
            ->leftJoin('w.vacations', 'v', 'WITH', ':date BETWEEN v.startDate AND v.endDate ')
            ->where('v.id IS NULL')
            ->innerJoin('w.Roles', 'r')
            ->leftJoin(
                'App\Entity\TaskWitnessDate',
                'twd',
                Join::WITH,
                'twd.Witness = w AND twd.Role = r AND twd.date < :date'
            )
            ->setParameter('date', $date)
            ->andWhere('r.name = :role')
            ->andWhere('w.active = 1')
            ->setParameter('role', $role)
            ->groupBy('w')
            ->orderBy('last_date', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
