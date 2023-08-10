<?php

declare(strict_types=1);

namespace Platim\PresenterBundle\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class AsPresenterHandler
{
    public function __construct(
        public ?string $handles = null,
        public ?string $method = null,
        public ?string $group = null,
        public ?int $priority = null,
    ) {
    }
}
