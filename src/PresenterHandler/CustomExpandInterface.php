<?php

declare(strict_types=1);

namespace Platim\PresenterBundle\PresenterHandler;

interface CustomExpandInterface
{
    /**
     * Association Names.
     *
     * @example ['customer' => fn ($entity) => $entity->getCustomer(), 'company']
     */
    public function getExpandFields(): array;
}
