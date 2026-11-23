<?php

declare(strict_types=1);

namespace LegitHealth\MedicalDevicePlusBundle\Tests;

use LegitHealth\MedicalDevicePlusBundle\LegitHealthMedicalDevicePlusBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;

final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new LegitHealthMedicalDevicePlusBundle();
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/plus-bundle-tests/cache/' . spl_object_id($this);
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/plus-bundle-tests/log';
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', ['test' => true, 'http_client' => true, 'secret' => 'test']);
        $container->extension('legit_health_medical_device_plus', [
            'base_url' => '%env(LEGIT_HEALTH_PLUS_BASE_URL)%',
            'issuer' => '%env(LEGIT_HEALTH_PLUS_ISSUER)%',
            'client_id' => '%env(LEGIT_HEALTH_PLUS_CLIENT_ID)%',
            'private_key' => '%env(LEGIT_HEALTH_PLUS_PRIVATE_KEY)%',
        ]);
    }
}
