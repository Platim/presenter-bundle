<?php

declare(strict_types=1);

namespace Platim\PresenterBundle\Serializer;

use Platim\Presenter\Contracts\DataProvider\DataProviderInterface;
use Platim\Presenter\Contracts\DataProvider\QueryBuilder\QueryBuilderInterface;
use Platim\PresenterBundle\NameConverter\NameConverterRegistry;
use Platim\PresenterBundle\Presenter\Presenter;
use Platim\PresenterBundle\PresenterContext\DataProviderContext;
use Platim\PresenterBundle\PresenterContext\DataProviderContextFactory;
use Platim\PresenterBundle\PresenterContext\ObjectContextFactory;
use Platim\PresenterBundle\PresenterHandler\PresenterHandlerRegistry;
use Platim\PresenterBundle\Request\Filter\CustomFilterInterface;
use Platim\PresenterBundle\Request\Filter\FilterBuilder;
use Platim\PresenterBundle\Request\Pagination\PaginationBuilder;
use Platim\PresenterBundle\Request\Pagination\PaginationResponseFactoryInterface;
use Platim\PresenterBundle\Request\Sort\CustomSortInterface;
use Platim\PresenterBundle\Request\Sort\SortBuilder;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

class DataProviderNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    private NormalizerInterface $normalizer;

    public function __construct(
        private readonly PresenterHandlerRegistry $presenterHandlerRegistry,
        private readonly NameConverterRegistry $nameConverterRegistry,
        private readonly DataProviderContextFactory $dataProviderContextFactory,
        private readonly ObjectContextFactory $objectContextFactory,
    ) {
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        if ($data instanceof DataProviderInterface) {
            return true;
        }
        if ($data instanceof Presenter) {
            return $data->getObject() instanceof DataProviderInterface;
        }

        return false;
    }

    public function normalize($object, string $format = null, array $context = []): mixed
    {
        $dataProvider = $object instanceof Presenter ? $object->getObject() : $object;
        if (!$dataProvider instanceof DataProviderInterface) {
            throw new \InvalidArgumentException();
        }

        $dataProviderContext = $object instanceof Presenter ? $object->dataProviderContext
            : $this->dataProviderContextFactory->createFromArrayContext($context);

        $queryBuilder = $this->prepareQueryBuilder($dataProvider, $dataProviderContext);

        $objectContext = $object instanceof Presenter ? $object->objectContext : $this->objectContextFactory->createFromArrayContext($context);

        if (null === $objectContext->nameConverter) {
            $objectContext->nameConverter = $this->nameConverterRegistry->getNameConverter($objectContext->group);
        }

        [$presenterHandler, $method] = $this->presenterHandlerRegistry
            ->getPresenterHandlerForClass($dataProvider::class, $objectContext->group);

        if ($dataProviderContext->isPaginationEnabled()) {
            if ($presenterHandler instanceof PaginationResponseFactoryInterface) {
                $responseFactory = $presenterHandler;
            } elseif ($dataProvider instanceof PaginationResponseFactoryInterface) {
                $responseFactory = $dataProvider;
            } else {
                $responseFactory = null;
            }

            $response = (new PaginationBuilder())
                ->paginate(
                    $dataProviderContext->paginationRequest,
                    $queryBuilder,
                    fn ($entity) => $this->normalizer->normalize($entity, $format, $objectContext->toArray()),
                    $responseFactory
                );
        } else {
            $response = array_map(
                fn ($entity) => $this->normalizer->normalize($entity, $format, $objectContext->toArray()),
                $queryBuilder->fetchAll()
            );
        }
        if (\is_callable([$presenterHandler, $method])) {
            $response = $presenterHandler->$method($dataProvider, $response, $context, $queryBuilder);
        }

        return $this->normalizer->normalize($response, $format, $context);
    }

    public function setSerializer(SerializerInterface $serializer): void
    {
        $this->normalizer = $serializer;
    }

    private function prepareQueryBuilder(
        DataProviderInterface $dataProvider,
        DataProviderContext $dataProviderContext,
    ): QueryBuilderInterface {
        $queryBuilder = clone $dataProvider->getQueryBuilder();
        if (null !== $dataProviderContext->sortRequest && $dataProviderContext->isSortEnabled()) {
            (new SortBuilder())->sort(
                $dataProviderContext->sortRequest,
                $queryBuilder,
                $dataProvider instanceof CustomSortInterface ? $dataProvider : null
            );
        }
        if (null !== $dataProviderContext->filterRequest && $dataProviderContext->isFilterEnabled()) {
            (new FilterBuilder())->filter(
                $dataProviderContext->filterRequest,
                $queryBuilder,
                $dataProvider instanceof CustomFilterInterface ? $dataProvider : null
            );
        }

        return $queryBuilder;
    }
}
