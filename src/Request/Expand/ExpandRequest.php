<?php

declare(strict_types=1);

namespace Platim\PresenterBundle\Request\Expand;

class ExpandRequest implements ExpandRequestInterface
{
    public function __construct(
        private readonly array $expand
    ) {
    }

    public function getExpand(): array
    {
        return $this->expand;
    }
}
