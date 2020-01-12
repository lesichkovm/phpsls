<?php

return [
    "URL_BASE"=>"http://localhost:35555",
    // Example: Database with Eloquent, delete if not needed
    'USE_ELOQUENT' => true,
    'DB' => [
        'default_connection' => 'sqlite',
        'connections' => [
            'testing' => [
                'driver' => 'sqlite',
                'database' => 'database.sqlite',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ],
    ],
];