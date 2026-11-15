<?php

declare(strict_types=1);

namespace App\Providers;

use Sahil\Auth\Bouncer;
use Sahil\Contracts\Auth\AuthenticatableContract;
use Sahil\Foundation\ServiceProvider;

/**
 * The application's authorization abilities — one obvious place.
 *
 * Bouncer answers a single question: "is this user allowed to perform
 * this action on this resource?" Define every ability here during boot;
 * check them anywhere:
 *
 *     Bouncer::allows('manage-users');        // in any code
 *     $user->can('update-post', $post);        // on a user model
 *     $this->authorize('update-post', $post);  // in a controller
 *     ->middleware('ability:update-post,{post}'); // on a route
 *     @can('manage-users') ... @endcan         // in a template
 *
 * Ability callbacks receive the authenticated user (or null) followed
 * by the resource arguments given at check time, and must return
 * exactly `true` to allow.
 */
final class AuthorizationProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Any signed-in user may open the dashboard; the generated auth
        // scaffold's Authenticate middleware still decides what a guest
        // sees (a redirect to /login rather than a 401).
        Bouncer::define(
            'access-dashboard',
            static fn (AuthenticatableContract $user): bool => true,
        );

        // An admin-style check — `App\Models\User` only exists once
        // `php sahil unda:auth` has generated it, so the instanceof
        // guard keeps this ability inert (deny) until then.
        Bouncer::define('manage-users', static function (AuthenticatableContract $user): bool {
            return $user instanceof \App\Models\User && $user->isAdmin();
        });

        // Resource abilities take the resource after the user:
        //
        //     Bouncer::define('update-post', static fn (AuthenticatableContract $user, Post $post): bool =>
        //         $user->getAuthIdentifier() === $post->user_id);
    }
}
