<?php

return [
    'URL_BASE' => 'NONE',
    'TESTING_FRAMEWORK' => '{{ TESTING_FRAMEWORK }}', // Options: TESTIFY, PHPUNIT, NONE
    // Example: Database with Eloquent, delete if not needed
    'USE_ELOQUENT' => true,
    'DB' => [
        'default_connection' => 'testing',
        'connections' => [
            'testing' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ],
    ],
];
