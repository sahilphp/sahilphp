<?php

declare(strict_types=1);

/**
 * This application's error-page copy — the bespoke wording of the starter
 * kit's designed error pages. Headings reuse the framework's `errors.*`
 * keys directly; these keys cover the app-specific extras.
 */
return [
    'trail_end' => 'end of the trail',
    'nav_home' => 'Home',
    'nav_uikit' => 'UI kit',
    'nav_health' => 'Health',

    'denied_step_role' => 'If this page is yours, sign in with an account that has the required role.',
    'denied_step_admin' => 'Otherwise ask the site administrator to grant your account access.',
    'denied_step_link' => 'Followed a link to get here? It may be out of date or belong to another team.',

    'expired_step_reload' => 'Reload the page and submit the form again — the refreshed token is valid.',
    'expired_step_copy' => 'If the form was long, keep a copy of what you typed before reloading.',
    'expired_step_signin' => 'Signed out in another tab? Signing in again resets the session.',

    'ratelimit_budget' => 'Request budget',
    'ratelimit_exhausted' => 'Exhausted',
    'ratelimit_step_wait' => '<strong>Wait a few seconds</strong>, then reload this page once — not repeatedly.',
    'ratelimit_step_share' => 'If you share this connection, another device may be using the same budget.',
    'ratelimit_step_retry' => 'Calling the API? Honour the <code>Retry-After</code> response header before the next attempt.',

    'step_wait' => '<strong>Wait a moment.</strong> Most of these errors clear on their own within a minute.',
    'step_reload' => '<strong>Reload the page.</strong> A fresh request often succeeds where the last one failed.',
    'step_contact' => '<strong>If it keeps happening,</strong> contact the site administrator and mention the status code <strong>500</strong> — the details have already been logged.',
    'reload_page' => 'Reload this page',
];
