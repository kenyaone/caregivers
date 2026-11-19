<?php

// Web push (browser/app notifications). Keys are generated once on the server and kept in .env.
return [
    'public_key'  => env('VAPID_PUBLIC_KEY'),
    'private_key' => env('VAPID_PRIVATE_KEY'),
    'subject'     => env('VAPID_SUBJECT', env('APP_URL', 'https://nursesoncall254.afyayako.co.ke')),
];
