<?php

declare(strict_types=1);

namespace Platim\PresenterBundle\Serializer;

use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class DummyNameConverter implements NameConverterInterface
{
    public function normalize(string $propertyName): string
    {
        return $propertyName;
    }

    public function denormalize(string $propertyName): string
    {
        return $propertyName;
    }
}
