<?php

declare(strict_types=1);

namespace Platim\PresenterBundle\DependencyInjection;

use Platim\PresenterBundle\PresenterHandler\PresenterHandlerRegistry;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class PresenterHandlerCompilerPass extends AbstractCompilerPass
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(PresenterHandlerRegistry::class)) {
            return;
        }

        $classHandlersArray = [];
        $interfaceHandlersArray = [];
        foreach ($container->findTaggedServiceIds('presenter.handler', true) as $serviceId => $tags) {
            $className = $this->getServiceClass($container, $serviceId);
            if (null === $className) {
                throw new \RuntimeException(sprintf('Invalid service "%s": class is not found.', $serviceId));
            }
            $reflection = $this->getReflectionClass($container, $serviceId, $className);

            foreach ($tags as $tag) {
                $methodName = $tag['method'] ?? '__invoke';
                $handles = $tag['handles'] ?? null;
                $group = $tag['group'] ?? 'default';
                $priority = $tag['priority'] ?? 0;

                if (null === $handles) {
                    try {
                        $method = $reflection->getMethod($methodName);
                    } catch (\ReflectionException) {
                        throw new \RuntimeException(sprintf('Invalid converter handler: class "%s" must have an "%s()" method.', $reflection->getName(), $methodName));
                    }
                    if (0 === $method->getNumberOfRequiredParameters()) {
                        throw new \RuntimeException(sprintf(
                            'Invalid converter handler: method "%s::__invoke()" requires at least one argument, first one being the object it handles.',
                            $reflection->getName()
                        ));
                    }
                    $parameters = $method->getParameters();
                    $type = $parameters[0]->getType();
                    if (!$type) {
                        throw new \RuntimeException(sprintf(
                            'Invalid converter handler: argument "$%s" of method "%s::%s()" must have a type-hint corresponding to the object class it handles.',
                            $parameters[0]->getName(),
                            $reflection->getName(),
                            $methodName
                        ));
                    }

                    if ($type->isBuiltin()) {
                        throw new \RuntimeException(sprintf(
                            'Invalid converter handler: type-hint of argument "$%s" in method "%s::%s()" must be a class , "%s" given.',
                            $parameters[0]->getName(),
                            $reflection->getName(),
                            $methodName,
                            $type
                        ));
                    }
                    $handles = (string) $type;
                }

                if (class_exists($handles)) {
                    $classHandlersArray[$handles . ':' . $group][] = [$handles, $group, new Reference($reflection->getName()), $methodName, $priority];
                } elseif (interface_exists($handles)) {
                    $interfaceHandlersArray[$handles . ':' . $group][] = [$handles, $group, new Reference($reflection->getName()), $methodName, $priority];
                }
            }
        }
        $classHandlers = [];
        foreach ($classHandlersArray as $array) {
            usort($array, static fn ($item1, $item2) => $item2['priority'] <=> $item1['priority']);
            $classHandlers[] = array_shift($array);
        }
        $interfaceHandlers = [];
        foreach ($interfaceHandlersArray as $array) {
            usort($array, static fn ($item1, $item2) => $item2['priority'] <=> $item1['priority']);
            $interfaceHandlers[] = array_shift($array);
        }
        $commandDefinition = $container->getDefinition(PresenterHandlerRegistry::class);
        $commandDefinition->setArgument('$classHandlers', $classHandlers);
        $commandDefinition->setArgument('$interfaceHandlers', $interfaceHandlers);
    }
}
