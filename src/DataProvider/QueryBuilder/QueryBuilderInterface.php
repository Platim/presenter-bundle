<?php

declare(strict_types=1);

namespace Platim\PresenterBundle\DataProvider\QueryBuilder;

interface QueryBuilderInterface extends QueryBuilderFilterInterface, QueryBuilderPaginationInterface, QueryBuilderSortInterface
{
}
