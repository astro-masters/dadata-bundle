<?php

declare(strict_types=1);

namespace Velhron\DadataBundle\Tests;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Velhron\DadataBundle\Service\DadataSuggest;
use Velhron\DadataBundle\VelhronDadataBundle;

class TestingKernel extends Kernel
{
    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/velhron-dadata-bundle-tests/cache';
    }

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new VelhronDadataBundle(),
        ];
    }

    protected function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->setAlias('test.dadata_suggest', DadataSuggest::class)->setPublic(true);
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/DependencyInjection/config.yaml');
    }
}
