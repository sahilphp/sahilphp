<?php

declare(strict_types=1);

namespace App\Providers;

use Sahil\Database\Connection;
use Sahil\Foundation\ServiceProvider;
use Sahil\Notifications\Channels\DatabaseChannel;
use Sahil\Notifications\DatabaseNotificationStore;
use Sahil\Notifications\NotificationStore;
use Sahil\Notifications\Notifier;

/**
 * Notification services.
 *
 *   - `NotificationStore` → database-channel persistence on the default
 *     connection (see `create_notifications_table`).
 *   - The bound `Notifier` — already carrying the mail channel from
 *     `MailProvider` — is extended here with the `database` channel, so
 *     `via() => ['mail', 'database']` works out of the box and additional
 *     channels can be registered with `$notifier->extend($channel)`.
 */
final class NotificationsProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app()->container()->singleton(NotificationStore::class, static fn ($c): NotificationStore
            => new DatabaseNotificationStore($c->make(Connection::class)));
    }

    public function boot(): void
    {
        /** @var Notifier $notifier */
        $notifier = $this->app()->container()->make(Notifier::class);
        $notifier->extend(new DatabaseChannel($this->app()->container()->make(NotificationStore::class)));
    }
}
