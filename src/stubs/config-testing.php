<?php

return [
    'URL_BASE' => "none",
    "USE_ELOQUENT" => true,
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