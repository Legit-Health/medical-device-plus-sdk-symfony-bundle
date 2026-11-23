<?php

declare(strict_types=1);

namespace LegitHealth\MedicalDevicePlusBundle;

use LegitHealth\MedicalDevicePlus\Auth\ClientCredentials;
use LegitHealth\MedicalDevicePlus\Auth\Psr6TokenCache;
use LegitHealth\MedicalDevicePlus\Crypto\Es256Key;
use LegitHealth\MedicalDevicePlus\PlusClient;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class PlusClientFactory
{
    public static function create(
        string $baseUrl,
        string $issuer,
        string $clientId,
        string $privateKey,
        string $scope,
        CacheItemPoolInterface $cachePool,
        HttpClientInterface $httpClient,
    ): PlusClient {
        return PlusClient::create(
            $baseUrl,
            new ClientCredentials($issuer, $clientId, Es256Key::fromPem($privateKey), $scope),
            new Psr6TokenCache($cachePool),
            $httpClient,
        );
    }
}
