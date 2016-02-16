<?php
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'applicationMode' => 'development',

        // Monolog settings
        'logger' => [
            'name' => 'referaway',
            'path' => __DIR__ . '/../logs/app.log',
            'debug' => 1
        ],

        // database settings
        'database' => [
            'driver' => 'pdo_mysql',
            'host' => 'localhost',
            'port' => 3306,
            'user' => 'root',
            'password' => '',
            'dbname' => 'referaway'
        ]
    ],
];