<?php

namespace App\Repository\Traits;

use Doctrine\ORM\QueryBuilder;

trait Pageable
{
    public function getFieldForSorting($field): ?string
    {
        return $this->sortableFields[$field] ?? null;
    }

    public function getFindByQueryBuilder(array $orderBy = [], ?string $filter = ''): QueryBuilder
    {
        /** @var \Doctrine\ORM\QueryBuilder $result */
        $result = $this->createQueryBuilder('e');

        if ($orderBy) {
            foreach ($orderBy as $field => $order) {
                $result->addOrderBy($field, $order);
            }
        }

        if ($filter) {
            $result->andWhere('e.' . $this->getFilterField() . ' like :param')
                ->setParameter('param',  '%' . $filter . '%');
        }

        return $result;
    }

    public function getFilterField(): string
    {
        return 'name';
    }
}
