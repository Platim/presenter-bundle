<?php

declare(strict_types=1);

namespace Platim\PresenterBundle\ArgumentResolver;

use Platim\PresenterBundle\Attribute\DataProvider;
use Platim\PresenterBundle\Attribute\Presenter as PresenterAttribute;
use Platim\PresenterBundle\Presenter\Presenter;
use Platim\PresenterBundle\Presenter\PresenterInterface;
use Platim\PresenterBundle\PresenterContext\DataProviderContextFactory;
use Platim\PresenterBundle\PresenterContext\ObjectContext;
use Platim\PresenterBundle\PresenterContext\ObjectContextFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class PresenterResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly DataProviderContextFactory $dataProviderContextFactory,
        private readonly ObjectContextFactory $objectContextFactory,
    ) {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $type = $argument->getType();

        if (!($type && interface_exists($type))) {
            return [];
        }

        $reflection = new \ReflectionClass($type);

        if (
            !(
                $reflection->isInterface()
                && $reflection->implementsInterface(PresenterInterface::class)
            )
        ) {
            return [];
        }

        $objectContext = $this->objectContextFactory->createFromInputBug($request->query);
        $dataProviderContext = $this->dataProviderContextFactory->createFromInputBug($request->query);

        /** @var PresenterAttribute $attribute */
        foreach ($argument->getAttributes(PresenterAttribute::class) as $attribute) {
            $objectContext->group = $attribute->group ?? ObjectContext::DEFAULT_GROUP;
        }

        /** @var DataProvider $attribute */
        foreach ($argument->getAttributes(DataProvider::class) as $attribute) {
            $dataProviderContext->setPaginationEnabled($attribute->paginated);
            $dataProviderContext->setFilterEnabled($attribute->filtered);
            $dataProviderContext->setSortEnabled($attribute->sorted);
        }

        yield new Presenter($objectContext, $dataProviderContext);
    }
}
