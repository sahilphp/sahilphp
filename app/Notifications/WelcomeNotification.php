<?php

declare(strict_types=1);

namespace App\Notifications;

use Sahil\Contracts\Notifications\NotifiableContract;
use Sahil\Mail\Message;
use Sahil\Notifications\MailMessage;
use Sahil\Notifications\Notification;

/**
 * Sent when an account is created — the starter's worked example.
 *
 * `via()` picks channels; each `to*()` composes one channel's payload.
 * Add `'mail'` to `via()` and the `toMail()` below is delivered through
 * the configured mail transport.
 */
final class WelcomeNotification extends Notification
{
    public function via(NotifiableContract $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(NotifiableContract $notifiable): array
    {
        return [
            'title' => 'Welcome',
            'message' => 'Your account is ready.',
        ];
    }

    public function toMail(NotifiableContract $notifiable): Message
    {
        return MailMessage::make()
            ->subject('Welcome')
            ->greeting('Welcome!')
            ->line('Your account is ready.')
            ->action('Open the dashboard', '/dashboard')
            ->toMessage();
    }
}
