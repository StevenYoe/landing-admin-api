<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Schedule Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define all of your scheduled tasks. In Laravel 12, the 
    | schedule configuration is defined here instead of the kernel class.
    |
    */

    'timezone' => 'Asia/Jakarta',

    'tasks' => [
        // Run the command to inactive expired vacancies daily at midnight
        [
            'command' => 'vacancies:inactive-expired',
            'schedule' => 'dailyAt',
            'arguments' => ['00:00'],
        ],
    ],
];
