<?php

declare(strict_types=1);

namespace Platim\PresenterBundle\Request\Pagination;

interface PaginationRequestInterface
{
    public function getPage(): int;

    public function getPageSize(): int;

    public function getPageStart(): int;
}
