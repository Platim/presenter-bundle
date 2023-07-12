<?php

declare(strict_types=1);

namespace Platim\PresenterBundle\Presenter;

interface PresenterInterface
{
    public function show(object $object, mixed $context = null): self;
}
