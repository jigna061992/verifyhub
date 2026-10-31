<?php

return [
    // Which provider is used. Add a real one (Cashfree, Signzy...) in "drivers" and set it in .env
    'driver' => env('VERIFICATION_PROVIDER', 'mock'),

    'drivers' => [
        'mock' => \App\Services\Providers\MockProvider::class,
    ],
];
