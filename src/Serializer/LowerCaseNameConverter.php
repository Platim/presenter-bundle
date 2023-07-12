<?php

declare(strict_types=1);

namespace Platim\PresenterBundle\Serializer;

use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class LowerCaseNameConverter implements NameConverterInterface
{
    public function normalize(string $propertyName): string
    {
        return strtolower($propertyName);
    }

    public function denormalize(string $propertyName): string
    {
        return $propertyName;
    }
}
