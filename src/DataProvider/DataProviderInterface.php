<?php

declare(strict_types=1);

namespace Platim\PresenterBundle\DataProvider;

use Platim\PresenterBundle\DataProvider\QueryBuilder\QueryBuilderInterface;

interface DataProviderInterface
{
    public function getQueryBuilder(): QueryBuilderInterface;
}
