# Symfony bundle for Legit.Health Plus

Official Symfony bundle for [`medical-device-plus-sdk-php`](https://github.com/Legit-Health/medical-device-plus-sdk-php), the SDK to call **diagnosis support** on Legit.Health Plus 🩺🤖

It registers `LegitHealth\MedicalDevicePlus\PlusClient` as a service, configured from environment variables. The client gets its own DPoP-bound access token from the Legit.Health authorization server and keeps it in a shared cache pool, so every PHP-FPM worker reuses it.

For how the authorization works, the diagnosis support contract and the environment URLs, see the [SDK README](https://github.com/Legit-Health/medical-device-plus-sdk-php#how-plus-authorization-works).

## Installation

```bash
composer require legit-health/medical-device-plus-sdk-bundle
```

Register the bundle in `config/bundles.php`:

```php
LegitHealth\MedicalDevicePlusBundle\LegitHealthMedicalDevicePlusBundle::class => ['all' => true],
```

## Configuration

`config/packages/legit_health_medical_device_plus.yaml`:

```yaml
legit_health_medical_device_plus:
    base_url: '%env(LEGIT_HEALTH_PLUS_BASE_URL)%'
    issuer: '%env(LEGIT_HEALTH_PLUS_ISSUER)%'
    client_id: '%env(LEGIT_HEALTH_PLUS_CLIENT_ID)%'
    private_key: '%env(LEGIT_HEALTH_PLUS_PRIVATE_KEY)%'
    # Optional:
    # scope: 'organization:*'   # default
    # cache_pool: 'cache.app'   # shared PSR-6 pool for the token and its DPoP key
    # http_client: 'http_client'
```

| Variable | Staging | Production |
|---|---|---|
| `LEGIT_HEALTH_PLUS_BASE_URL` | `https://plus-staging.legit.health` | `https://plus.legit.health` |
| `LEGIT_HEALTH_PLUS_ISSUER` | `https://id-staging.legit.health/realms/legit-health` | `https://id.legit.health/realms/legit-health` |
| `LEGIT_HEALTH_PLUS_CLIENT_ID` | `app-backend` | `app-backend` |
| `LEGIT_HEALTH_PLUS_PRIVATE_KEY` | from the secret manager | from the secret manager |

`LEGIT_HEALTH_PLUS_PRIVATE_KEY` is the P-256 private key registered for the client, either as PEM or as the PEM encoded in base64 on one line (handy for environment variables). Never commit it.

`cache_pool` must be shared across workers (Redis, APCu or a filesystem pool on one host). The pool stores the token together with its DPoP private key for at most five minutes.

## Usage

```php
use LegitHealth\MedicalDevicePlus\Arguments\{DiagnosisSupportArguments, EncodedImage};
use LegitHealth\MedicalDevicePlus\Exception\RequestException;
use LegitHealth\MedicalDevicePlus\PlusClient;

final class SendDiagnosis
{
    public function __construct(private PlusClient $plus) {}

    public function __invoke(string $image): void
    {
        $response = $this->plus->diagnosisSupport(
            new DiagnosisSupportArguments([EncodedImage::fromBinary($image)])
        );

        $response->reportId;                                  // store it with the organisation
        $response->riskMetrics()->referralProbability;        // 0-100
        $response->studyAggregate->topHypothesis()?->identifier;
    }
}
```

The service is also available as `legit_health_medical_device_plus.client`.

## Development

```bash
docker run --rm -v "$PWD":/app -w /app composer:2 composer install --ignore-platform-req=php
docker run --rm -v "$PWD":/app -w /app php:8.4-cli vendor/bin/phpunit
```

## Releases

Releases are git tags following semantic versioning (`1.0.0`), published on Packagist as `legit-health/medical-device-plus-sdk-bundle`.
