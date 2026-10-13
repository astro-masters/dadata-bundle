<?php

declare(strict_types=1);

namespace Velhron\DadataBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Yaml\Yaml;
use Velhron\DadataBundle\DependencyInjection\VelhronDadataExtension;
use Velhron\DadataBundle\Service\DadataSuggest;
use Velhron\DadataBundle\Tests\TestingKernel;
use Velhron\DadataBundle\VelhronDadataBundle;

class VelhronDadataExtensionTest extends TestCase
{
    public function testSymfonyContainer(): void
    {
        $kernel = new TestingKernel('test', false);
        $kernel->boot();

        $this->assertInstanceOf(DadataSuggest::class, $kernel->getContainer()->get('test.dadata_suggest'));

        $kernel->shutdown();
    }

    public function testSymfonyContainerWithoutConfiguration(): void
    {
        $kernel = new class('noconfig', false) extends Kernel {
            public function getCacheDir(): string
            {
                return sys_get_temp_dir().'/velhron-dadata-bundle-tests/no-config-cache';
            }

            public function registerBundles(): iterable
            {
                return [new FrameworkBundle(), new VelhronDadataBundle()];
            }

            public function registerContainerConfiguration(LoaderInterface $loader): void
            {
                $loader->load(__DIR__.'/no-config.yaml');
            }
        };

        $kernel->boot();

        $this->assertFalse($kernel->getContainer()->has(DadataSuggest::class));

        $kernel->shutdown();
    }

    public function testRegister(): void
    {
        $config = Yaml::parse(file_get_contents(__DIR__.'/config.yaml'));

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.debug', true);

        $extension = new VelhronDadataExtension();
        $extension->load([$config['velhron_dadata']], $containerBuilder);

        $this->assertSame('token', $containerBuilder->getParameter('velhron_dadata.token'));
        $this->assertSame('secret', $containerBuilder->getParameter('velhron_dadata.secret'));
        $this->assertSame('https://example.com/general', $containerBuilder->getParameter('velhron_dadata.base_general_url'));
        $this->assertSame('https://example.com/cleaner', $containerBuilder->getParameter('velhron_dadata.base_cleaner_url'));
        $this->assertSame('https://example.com/suggetions', $containerBuilder->getParameter('velhron_dadata.base_suggestions_url'));
    }

    public function testRegisterWithDefaults(): void
    {
        $containerBuilder = new ContainerBuilder();
        $config = [
            'token' => 'token12345',
            'secret' => 'secret12345',
        ];

        $extension = new VelhronDadataExtension();
        $extension->load([$config], $containerBuilder);

        $this->assertSame('token12345', $containerBuilder->getParameter('velhron_dadata.token'));
        $this->assertSame('secret12345', $containerBuilder->getParameter('velhron_dadata.secret'));
        $this->assertSame('https://dadata.ru/api/v2', $containerBuilder->getParameter('velhron_dadata.base_general_url'));
        $this->assertSame('https://cleaner.dadata.ru/api/v1/clean', $containerBuilder->getParameter('velhron_dadata.base_cleaner_url'));
        $this->assertSame('https://suggestions.dadata.ru/suggestions/api/4_1/rs', $containerBuilder->getParameter('velhron_dadata.base_suggestions_url'));
    }

    public function testRegisterWithoutConfiguration(): void
    {
        $containerBuilder = new ContainerBuilder();

        $extension = new VelhronDadataExtension();
        $extension->load([[]], $containerBuilder);

        $this->assertFalse($containerBuilder->hasParameter('velhron_dadata.token'));
        $this->assertFalse($containerBuilder->hasDefinition(DadataSuggest::class));
    }

    public function testRegisterWithoutToken(): void
    {
        $containerBuilder = new ContainerBuilder();

        $this->expectExceptionMessage('token');

        $extension = new VelhronDadataExtension();
        $extension->load([['secret' => 'secret']], $containerBuilder);
    }

    public function testRegisterWithoutSecret(): void
    {
        $containerBuilder = new ContainerBuilder();

        $this->expectExceptionMessage('secret');

        $extension = new VelhronDadataExtension();
        $extension->load([['token' => 'token']], $containerBuilder);
    }
}
