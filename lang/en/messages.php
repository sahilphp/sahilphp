<?php

declare(strict_types=1);

/**
 * Application messages in English.
 *
 * These files load as `lang/{locale}/{group}.php`; `__('messages.welcome')`
 * reads the `welcome` key from this file. Framework keys (`validation.*`,
 * `auth.*`, `pagination.*`, `errors.*`) come from the framework catalog —
 * override them key-by-key with a matching file here (e.g.
 * `lang/en/validation.php`).
 */
return [
    'welcome' => 'Welcome to your SahilPHP application.',
    'tagline' => 'Build fast. Write with confidence.',
    'saved' => 'The information has been saved successfully.',
    'welcome_user' => 'Welcome, :name!',
    'users' => [
        'one' => ':count user',
        'other' => ':count users',
    ],
];
