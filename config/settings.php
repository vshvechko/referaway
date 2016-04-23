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

        // email settings
        'email' => [
            // use smtp server
            'smtp' => [
                'host' => 'smtp.gmail.com',
                'port' => '587',
                'sequre' => 'tls',
                'auth' => true,
                'username' => 'gmail account',
                'password' => 'gmail pass',
            ],
            'from' => [
                'noreply@cogniteq.com',
                'Info'
            ],
            'replyTo' => [
                'noreply@cogniteq.com',
                'Info'
            ],

            // messages settings
            'messages' => [
                // message id
                'password' => [
                    // email subject
                    'subject' => 'Referaway password',
                    // email template
                    'template' => __DIR__ . '/../src/App/View/Email/password.phtml'
                ]
            ]
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
