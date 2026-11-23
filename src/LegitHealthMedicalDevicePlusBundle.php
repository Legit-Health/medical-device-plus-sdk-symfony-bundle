<?php

declare(strict_types=1);

namespace LegitHealth\MedicalDevicePlusBundle;

use LegitHealth\MedicalDevicePlus\Auth\ClientCredentials;
use LegitHealth\MedicalDevicePlus\PlusClient;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class LegitHealthMedicalDevicePlusBundle extends AbstractBundle
{
    protected string $extensionAlias = 'legit_health_medical_device_plus';

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->scalarNode('base_url')->isRequired()->cannotBeEmpty()
                    ->info('Plus API, e.g. https://plus-staging.legit.health')->end()
                ->scalarNode('issuer')->isRequired()->cannotBeEmpty()
                    ->info('Authorization server issuer, e.g. https://id-staging.legit.health/realms/legit-health')->end()
                ->scalarNode('client_id')->isRequired()->cannotBeEmpty()
                    ->info('Machine client registered in the authorization server, e.g. app-backend')->end()
                ->scalarNode('private_key')->isRequired()->cannotBeEmpty()
                    ->info('P-256 private key of the client, as PEM or as the PEM encoded in base64')->end()
                ->scalarNode('scope')->defaultValue(ClientCredentials::DEFAULT_SCOPE)->end()
                ->scalarNode('cache_pool')->defaultValue('cache.app')
                    ->info('Shared PSR-6 pool that keeps the token and its DPoP key across PHP-FPM workers')->end()
                ->scalarNode('http_client')->defaultValue('http_client')->end()
            ->end();
    }

    /**
     * @param array{base_url: string, issuer: string, client_id: string, private_key: string, scope: string, cache_pool: string, http_client: string} $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->services()
            ->set(PlusClient::class, PlusClient::class)
            ->factory([PlusClientFactory::class, 'create'])
            ->args([
                $config['base_url'],
                $config['issuer'],
                $config['client_id'],
                $config['private_key'],
                $config['scope'],
                new Reference($config['cache_pool']),
                new Reference($config['http_client']),
            ])
            ->public()
            ->alias('legit_health_medical_device_plus.client', PlusClient::class)
            ->public();
    }
}
