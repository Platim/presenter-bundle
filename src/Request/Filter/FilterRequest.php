<?php

declare(strict_types=1);

namespace Platim\PresenterBundle\Request\Filter;

class FilterRequest implements FilterRequestInterface
{
    public function __construct(
        private readonly array $filters
    ) {
    }

    public function getFilters(): array
    {
        return $this->filters;
    }
}
