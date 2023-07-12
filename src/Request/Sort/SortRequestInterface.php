<?php

declare(strict_types=1);

namespace Platim\PresenterBundle\Request\Sort;

interface SortRequestInterface
{
    public function getSortOrders(): array;
}
