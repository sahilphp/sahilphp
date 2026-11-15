<?php

declare(strict_types=1);

/**
 * Service providers for this installation, in registration order.
 *
 * Kept separate from `bootstrap/app.php` so testing utilities can register
 * exactly the same providers on an isolated application instance without
 * booting the HTTP entry point.
 *
 * @return list<class-string<Sahil\Foundation\ServiceProvider>>
 */
return [
    \App\Providers\ObservabilityProvider::class,
    \App\Providers\ProxyProvider::class,
    \App\Providers\DatabaseProvider::class,
    \App\Providers\SessionProvider::class,
    \App\Providers\EncryptionProvider::class,
    \App\Providers\StorageProvider::class,
    \App\Providers\AuthProvider::class,
    \App\Providers\AuthorizationProvider::class,
    \App\Providers\RateLimitProvider::class,
    \App\Providers\QueueProvider::class,
    \App\Providers\ScheduleProvider::class,
    \App\Providers\MailProvider::class,
    \App\Providers\NotificationsProvider::class,
    \App\Providers\EventsProvider::class,
    \App\Providers\HttpClientProvider::class,
    \App\Providers\WebhookProvider::class,
    \App\Providers\AssetProvider::class,
    // Native `html` rendering — always required. `ui:setup` injects the UI
    // kit's provider (Firefiber, React, Vue, or Svelte) directly after this line.
    \App\Providers\HtmlProvider::class,
    \Sahil\Firefiber\FirefiberServiceProvider::class,    // This install's UI kit. `ui:setup` strips this line and
    // injects the selected kit's provider in generated projects.
    \App\Providers\ViewProvider::class,
    \App\Providers\WebRoutesProvider::class,
    \App\Providers\CacheProvider::class,
    \App\Providers\OpenApiProvider::class,
];
