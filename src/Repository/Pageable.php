<?php

namespace App\Repository;

use Doctrine\ORM\QueryBuilder;
interface Pageable
{
    public function isSortableField($field): bool;
    public function getFindByQueryBuilder($orderBy = []) : QueryBuilder;
}