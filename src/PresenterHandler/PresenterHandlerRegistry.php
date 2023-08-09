<?php

declare(strict_types=1);

namespace Platim\PresenterBundle\PresenterHandler;

use Doctrine\Persistence\Proxy;
use Platim\PresenterBundle\PresenterContext\ObjectContext;

class PresenterHandlerRegistry
{
    private array $classHandlers = [];
    private array $interfaceHandlers = [];

    public function __construct(
        array $classHandlers = [],
        array $interfaceHandlers = [],
    ) {
        foreach ($classHandlers as [$class, $group, $handler, $method, $priority]) {
            $this->classHandlers[$class][$group] = [$handler, $method, $priority];
        }
        foreach ($interfaceHandlers as [$interface, $group, $handler, $method, $priority]) {
            $this->interfaceHandlers[$interface][$group] = [$handler, $method, $priority];
        }
    }

    public function hasPresenterHandlerForClass(string $class): bool
    {
        $class = $this->getHandledClass($class);

        if (isset($this->classHandlers[$class])) {
            return true;
        }

        $interfaces = class_implements($class);
        if (\is_array($interfaces) && \count($interfaces)) {
            return \count(array_intersect(array_keys($this->interfaceHandlers), $interfaces)) > 0;
        }

        return false;
    }

    public function getPresenterHandlerForClass(string $class, string $group): array
    {
        $class = $this->getHandledClass($class);

        if (isset($this->classHandlers[$class][$group])) {
            return $this->classHandlers[$class][$group];
        }
        if (isset($this->classHandlers[$class][ObjectContext::DEFAULT_GROUP])) {
            return $this->classHandlers[$class][ObjectContext::DEFAULT_GROUP];
        }

        if (\count($this->interfaceHandlers)) {
            $interfaces = class_implements($class);
            if (\is_array($interfaces) && \count($interfaces)) {
                $interfaceHandlers = array_intersect_key($this->interfaceHandlers, array_flip($interfaces));
                if (\count($interfaceHandlers)) {
                    usort($interfaceHandlers, static fn ($item1, $item2) => $item2['priority'] <=> $item1['priority']);

                    return array_shift($interfaceHandlers);
                }
            }
        }

        return [null, null];
    }

    public function getCustomExpandFieldsForClass(string $class, string $group): ?CustomExpandInterface
    {
        [$presenterHandler] = $this->getPresenterHandlerForClass($class, $group);

        return $presenterHandler instanceof CustomExpandInterface ? $presenterHandler : null;
    }

    private function getHandledClass(string $class): string
    {
        if (is_subclass_of($class, Proxy::class)) {
            $class = get_parent_class($class);
        }

        return $class;
    }
}
