<?php

namespace App\Repository\Traits;

use Doctrine\ORM\QueryBuilder;

trait Pageable
{
    public function isSortableField($field): bool
    {
        return in_array($field, $this->sortableFields);
    }
    public function getFindByQueryBuilder($orderBy = []): QueryBuilder
    {
        $result = $this->createQueryBuilder('e');

        if ($orderBy) {
            foreach ($orderBy as $field => $order) {
                $result->addOrderBy('e.' . $field, $order);
            }
        }
        return $result;
    }
}
