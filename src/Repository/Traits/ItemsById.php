<?php

namespace App\Repository\Traits;

trait ItemsById
{
    public function getAllWithIdKey(): array
    {
        return $this->createQueryBuilder('e')
            ->indexBy('e', 'e.id')
            ->getQuery()
            ->getResult();
    }
}
