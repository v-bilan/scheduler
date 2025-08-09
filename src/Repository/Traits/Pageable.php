<?php

namespace App\Repository\Traits;

use Doctrine\ORM\QueryBuilder;

trait Pageable
{
    public function getFieldForSorting($field): ?string
    {
        return $this->sortableFields[$field] ?? null;
    }

    public function getFindByQueryBuilder($orderBy = []): QueryBuilder
    {
        $result = $this->createQueryBuilder('e');

        if ($orderBy) {
            foreach ($orderBy as $field => $order) {
                $result->addOrderBy($field, $order);
            }
        }
        return $result;
    }
}
