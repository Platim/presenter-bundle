<?php

declare(strict_types=1);

namespace Platim\PresenterBundle;

use Platim\PresenterBundle\DependencyInjection\PresenterExtension;
use Platim\PresenterBundle\DependencyInjection\PresenterHandlerCompilerPass;
use Platim\PresenterBundle\DependencyInjection\PresenterNameConverterCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PresenterBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new PresenterHandlerCompilerPass());
        $container->addCompilerPass(new PresenterNameConverterCompilerPass());
    }

    public function getContainerExtension(): ExtensionInterface
    {
        return new PresenterExtension();
    }
}
