<?php

namespace App\Repository;

use Doctrine\ORM\QueryBuilder;

interface Pageable
{
    public function getFieldForSorting($field): ?string;
    public function getFindByQueryBuilder(array $orderBy = [], ?string $filter = ''): QueryBuilder;
    public function getFilterField(): string;
}
