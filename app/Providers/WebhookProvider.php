<?php

declare(strict_types=1);

namespace App\Providers;

use Sahil\Container\Container;
use Sahil\Database\Connection;
use Sahil\Encryption\Encrypter;
use Sahil\Foundation\ServiceProvider;
use Sahil\Webhooks\DatabaseWebhookStore;
use Sahil\Webhooks\DnsResolverContract;
use Sahil\Webhooks\SystemDnsResolver;
use Sahil\Webhooks\WebhookManager;
use Sahil\Webhooks\WebhookStore;
use Sahil\Webhooks\WebhookUrlValidator;

/**
 * Outbound webhook delivery: registers an endpoint store (secrets sealed
 * with the APP_KEY encrypter) plus the manager `Webhook::` resolves.
 *
 * Local development: set `WEBHOOKS_ALLOW_PRIVATE=1` (and optionally
 * `WEBHOOKS_ALLOW_HTTP=1`) in `.env` to permit http/loopback endpoints.
 * Production keeps https-only public destinations.
 */
final class WebhookProvider extends ServiceProvider
{
    public function register(): void
    {
        $container = $this->app()->container();

        $container->singleton(WebhookStore::class, static function (Container $c): WebhookStore {
            /** @var Connection $db */
            $db = $c->make(Connection::class);
            /** @var Encrypter|null $encrypter */
            $encrypter = $c->has(Encrypter::class) ? $c->make(Encrypter::class) : null;

            return new DatabaseWebhookStore($db, $encrypter);
        });

        $container->singleton(DnsResolverContract::class, static fn (): DnsResolverContract => new SystemDnsResolver());

        $container->singleton(WebhookUrlValidator::class, static function (Container $c): WebhookUrlValidator {
            return new WebhookUrlValidator(
                allowHttp: (bool) env('WEBHOOKS_ALLOW_HTTP', false),
                allowPrivateHosts: (bool) env('WEBHOOKS_ALLOW_PRIVATE', false),
                resolver: $c->make(DnsResolverContract::class),
            );
        });

        $container->singleton(WebhookManager::class, static function (Container $c): WebhookManager {
            return new WebhookManager(
                $c->make(WebhookStore::class),
                $c->make(WebhookUrlValidator::class),
            );
        });
    }
}
