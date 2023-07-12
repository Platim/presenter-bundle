<?php

declare(strict_types=1);

namespace Platim\PresenterBundle\Request\Expand;

interface ExpandRequestInterface
{
    public function getExpand(): array;
}
