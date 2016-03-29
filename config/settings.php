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
        ],
        'CDN' => [
            'uploadDir' => __DIR__ . '/../public/uploads',
            'uploadUrl' => sprintf(
                "%s://%s%s",
                isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
                $_SERVER['SERVER_NAME'],
                ($_SERVER['SERVER_PORT'] != 80 && $_SERVER['SERVER_PORT'] != 443) ? ':' . $_SERVER['SERVER_PORT'] : null
            ) . '/uploads'
        ],
    ],
];
