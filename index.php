<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = new Slim\App(include __DIR__ . '/config/settings.php');

(new \App\Configurator())->loadDependencyDefaults($app->getContainer())
    ->initMiddleware($app)
    ->initRoutes($app);

$app->run();