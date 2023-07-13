<?php

declare(strict_types=1);

namespace Platim\PresenterBundle\Attribute;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class Request
{
    public function __construct(
        public ?string $formClass = null
    ) {
    }
}
