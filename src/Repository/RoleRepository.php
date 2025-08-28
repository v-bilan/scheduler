<?php

namespace App\Repository;

use App\Entity\Role;
use App\Repository\Traits\ItemsById;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Role>
 */
class RoleRepository extends ServiceEntityRepository implements Pageable
{
    use \App\Repository\Traits\Pageable;
    use ItemsById;
    private $sortableFields = ['id' => 'e.id', 'name' => 'e.name', 'priority' => 'e.priority'];
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }
    public function getRolesIdBySchool($school = false)
    {
        $items = $school ? $this->findBy(['school' => $school]) : $this->createQueryBuilder('r')
            ->andWhere('r.school  = false OR r.school is null')
            ->getQuery()
            ->getResult();
        $result = [];
        foreach ($items as $item) {
            $result[$item->getId()] = $item;
        }
        return $result;
    }

    //    /**
    //     * @return Role[] Returns an array of Role objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Role
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
