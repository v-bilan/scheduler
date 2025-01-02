<?php

namespace App\Controller\Traits;

use App\Repository\Pageable;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;

trait PagerFantaTrait
{
    function getPagerFanta(Request $request, Pageable $pageableRepository, $defaultOrderBy = null): Pagerfanta
    {
        $orderBy = $request->get('orderBy', $defaultOrderBy);
        if ($pageableRepository->isSortableField($orderBy)) {
            $direction = strtolower($request->get('orderDir')) === 'desc' ? 'DESC' : 'ASC';
            $queryBuilder =$pageableRepository->getFindByQueryBuilder(
                orderBy: [$orderBy => $direction]
            );
        } else {
            $queryBuilder =$pageableRepository->getFindByQueryBuilder();
        }
        $adapter = new QueryAdapter($queryBuilder);

        $pagerfanta = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $request->query->get('page', 1),
            10
        );
        return $pagerfanta;
    }

}