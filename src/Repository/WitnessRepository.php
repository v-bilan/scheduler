<?php

namespace App\Repository;

use App\Entity\Witness;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\OrderBy;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Witness>
 */
class WitnessRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Witness::class);
    }

    public function getFindByQueryBuilder($orderBy= ['fullName' => 'asc']) : QueryBuilder
    {
        $result = $this->createQueryBuilder('w');
        if ($orderBy) {
            foreach ($orderBy as $field => $order) {
                $result->addOrderBy("w.$field", $order);
            }
        }
        return $result;
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
        $result = $this->createQueryBuilder('w')//->addSelect('max(twd.Date) as lastDate')
            ->innerJoin('w.Roles', 'role')
            ->leftJoin(
                'App\Entity\TaskWitnessDate',
                'twd' ,
                Join::WITH,
                'twd.Witness = w AND twd.Role = role AND twd.Date < :date ')
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
}
