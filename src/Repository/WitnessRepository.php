<?php

namespace App\Repository;

use App\Entity\Witness;
use App\Repository\Traits\ItemsById;
use App\Util\Date;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

    private $sortableFields = ['id', 'fullName', 'active'];
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Witness::class);
    }

    //    /**
    //     * @return Witness[] Returns an array of Witness objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('w')
    //            ->andWhere('w.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('w.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    public function findByRoleId(int $roleId, \DateTime $date): array
    {
        $result = $this->createQueryBuilder('w') //->addSelect('max(twd.Date) as lastDate')
            ->innerJoin('w.Roles', 'role')
            ->leftJoin(
                'App\Entity\TaskWitnessDate',
                'twd',
                Join::WITH,
                'twd.Witness = w AND twd.Role = role AND twd.Date < :date '
            )
            ->setParameter('date', $date)
            ->andWhere('role.id = :roleId')
            ->andWhere('w.active = 1')
            ->setParameter('roleId', $roleId)
            ->groupBy('w')
            ->orderBy('max(twd.Date)', 'ASC')
            ->getQuery()
            ->getResult();
        return $result;
    }

    public function findByRoleName($roleName): array
    {
        return $this->createQueryBuilder('w')
            ->innerJoin('w.Roles', 'roles')
            ->andWhere('roles.name = :roleName')
            ->andWhere('w.active = 1')
            ->setParameter('roleName', $roleName)
            ->orderBy('w.fullName', 'ASC')
            ->getQuery()

            ->getResult()
        ;
    }

    public function getWitnessesByRole(string $role, Date $date)
    {
        '
select
    `witnesses`.`id` as `witness_id`,
    `witnesses`.`full_name`,
    `roles`.`name` as `role_name`,
    `roles`.`id` as `role_id`,
    max(task_witness_date.date) as last_date
from
    `witnesses`
    inner join `role_witness` on `witnesses`.`id` = `role_witness`.`witness_id`
    inner join `roles` on `role_witness`.`role_id` = `roles`.`id`
    left join `task_witness_date` on `witnesses`.`id` = `task_witness_date`.`witness_id`
    and `roles`.`id` = `task_witness_date`.`role_id`
    and `task_witness_date`.`date` < :date
where
    `roles`.`name` = :role
    and `witnesses`.`active` = 1
group by
    `witnesses`.`full_name`,
    `roles`.`id`,
    `roles`.`name`,
    `witnesses`.`id`
order by `last_date` asc
        ';
    }
}
