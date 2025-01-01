<?php

namespace App\Repository;

use App\Entity\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Role>
 */
class RoleRepository extends ServiceEntityRepository implements Pageable
{
    private $sortableFields = ['id', 'name','priority'];
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }
    public function isSortableField($field): bool
    {
        return in_array($field, $this->sortableFields);
    }
    public function getFindByQueryBuilder($orderBy= ['name' => 'ASC']) : QueryBuilder
    {
        $result = $this->createQueryBuilder('r');
        if ($orderBy) {
            foreach ($orderBy as $field => $order) {
                $result->addOrderBy("r.$field", $order);
            }
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
