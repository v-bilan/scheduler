<?php

namespace App\Services;

use App\Repository\Pageable;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;

class PagerFantaManager
{
    private int $pageCount = 10;

    public function getPageCount(): int
    {
        return $this->pageCount;
    }

    public function setPageCount(int $val)
    {
        $this->pageCount = $val;
    }

    public function getQueryBuilder(
        Request $request,
        Pageable $pageableRepository,
        $defaultOrderBy = null
    ): QueryBuilder {
        $orderBy = $request->get('orderBy', $defaultOrderBy);
        $filter = $request->get('filter');
        if ($orderBy = $pageableRepository->getFieldForSorting($orderBy)) {
            $direction = strtolower($request->get('orderDir')) === 'desc' ? 'DESC' : 'ASC';
            $queryBuilder = $pageableRepository->getFindByQueryBuilder(
                orderBy: [$orderBy => $direction],
                filter: $filter
            );
        } else {
            $queryBuilder = $pageableRepository->getFindByQueryBuilder(filter: $filter);
        }
        return $queryBuilder;
    }

    public function createPagerFanta(QueryBuilder $queryBuilder, $page)
    {
        $adapter = new QueryAdapter($queryBuilder);

        $pagerfanta = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $page,
            $this->pageCount
        );
        return $pagerfanta;
    }

    public function getPagerFanta(
        Request $request,
        Pageable $pageableRepository,
        $defaultOrderBy = null
    ): Pagerfanta {

        $queryBuilder = $this->getQueryBuilder($request, $pageableRepository, $defaultOrderBy);

        return $this->createPagerFanta($queryBuilder, $request->query->get('page', 1));
    }
}
