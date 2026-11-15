<?php

declare(strict_types=1);

namespace App\Events;

use Sahil\Contracts\Notifications\NotifiableContract;

/**
 * Fired after a new account is created and the user is logged in.
 *
 * Events are plain objects carrying what listeners need — no framework
 * base class, nothing mutable.
 */
final class UserRegistered
{
    public function __construct(
        public readonly NotifiableContract $user,
    ) {
    }
}
