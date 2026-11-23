<?php

declare(strict_types=1);

namespace LegitHealth\MedicalDevicePlusBundle\Tests;

use LegitHealth\MedicalDevicePlus\Crypto\Es256Key;
use LegitHealth\MedicalDevicePlus\PlusClient;
use PHPUnit\Framework\TestCase;

final class BundleTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER['LEGIT_HEALTH_PLUS_BASE_URL'] = 'https://plus-staging.legit.health';
        $_SERVER['LEGIT_HEALTH_PLUS_ISSUER'] = 'https://id-staging.legit.health/realms/legit-health';
        $_SERVER['LEGIT_HEALTH_PLUS_CLIENT_ID'] = 'app-backend';
    }

    protected function tearDown(): void
    {
        unset($_SERVER['LEGIT_HEALTH_PLUS_BASE_URL'], $_SERVER['LEGIT_HEALTH_PLUS_ISSUER'], $_SERVER['LEGIT_HEALTH_PLUS_CLIENT_ID'], $_SERVER['LEGIT_HEALTH_PLUS_PRIVATE_KEY']);
    }

    public function testRegistersTheClientFromEnvironmentVariables(): void
    {
        $_SERVER['LEGIT_HEALTH_PLUS_PRIVATE_KEY'] = base64_encode(Es256Key::generate()->toPem());

        $container = $this->bootContainer();

        self::assertInstanceOf(PlusClient::class, $container->get(PlusClient::class));
        self::assertSame($container->get(PlusClient::class), $container->get('legit_health_medical_device_plus.client'));
    }

    public function testAcceptsAPlainPemKey(): void
    {
        $_SERVER['LEGIT_HEALTH_PLUS_PRIVATE_KEY'] = Es256Key::generate()->toPem();

        self::assertInstanceOf(PlusClient::class, $this->bootContainer()->get(PlusClient::class));
    }

    public function testFailsWhenTheKeyIsInvalid(): void
    {
        $_SERVER['LEGIT_HEALTH_PLUS_PRIVATE_KEY'] = 'not a key';

        $this->expectException(\InvalidArgumentException::class);
        $this->bootContainer()->get(PlusClient::class);
    }

    private function bootContainer(): \Symfony\Component\DependencyInjection\ContainerInterface
    {
        $kernel = new TestKernel('test', false);
        $kernel->boot();

        return $kernel->getContainer();
    }
}
