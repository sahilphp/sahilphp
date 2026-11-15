<?php

declare(strict_types=1);

/**
 * Ujumbe wa programu kwa Kiswahili — lugha chaguo-msingi ya programu hii.
 *
 * Faili hizi zinapakuliwa kama `lang/{locale}/{kikundi}.php`; wito
 * `__('messages.welcome')` husoma ufunguo `welcome` kutoka faili hii.
 * Funguo za mfumo (`validation.*`, `auth.*`, `pagination.*`, `errors.*`)
 * zinatoka catalog ya mfumo — unaweza kuzibadilisha kwa kuweka faili zinazo
 * lingana hapa (mfano `lang/sw/validation.php`).
 */
return [
    'welcome' => 'Karibu kwenye programu yako ya SahilPHP.',
    'tagline' => 'Tengeneza haraka. Andika kwa uhakika.',
    'saved' => 'Taarifa zimehifadhiwa kikamilifu.',
    'welcome_user' => 'Karibu, :name!',
    'users' => [
        'one' => 'Mtumiaji :count',
        'other' => 'Watumiaji :count',
    ],
];
