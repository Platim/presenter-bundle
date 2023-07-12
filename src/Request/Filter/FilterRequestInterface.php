<?php

declare(strict_types=1);

namespace Platim\PresenterBundle\Request\Filter;

interface FilterRequestInterface
{
    public function getFilters(): array;
}
