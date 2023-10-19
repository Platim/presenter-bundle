<?php

declare(strict_types=1);

namespace Platim\PresenterBundle\Metadata;

use Doctrine\Persistence\ManagerRegistry;
use Platim\Presenter\Contracts\Metadata\MetadataInterface;
use Platim\Presenter\Contracts\Metadata\MetadataRegistryInterface;
use Platim\Presenter\Doctrine\MetadataRegistry as DoctrineMetadataRegistry;

class MetadataRegistry implements MetadataRegistryInterface
{
    private DoctrineMetadataRegistry $doctrineMetadata;

    public function __construct(
        ManagerRegistry $registry,
    ) {
        $this->doctrineMetadata = new DoctrineMetadataRegistry($registry);
    }

    public function getMetadataForClass(string $class): ?MetadataInterface
    {
        return $this->doctrineMetadata->getMetadataForClass($class);
    }

    public function getObjectClass(object $object): string
    {
        return $this->doctrineMetadata->getObjectClass($object);
    }
}
