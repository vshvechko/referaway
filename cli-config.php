<?php

use Slim\Container;

require_once __DIR__ . '/vendor/autoload.php';

$di = new Container(include __DIR__ . '/config/settings.php');
$di = (new \App\Configurator())->loadDependencyDefaults($di);

$em = $di->get('entityManager');

$helpers = new Symfony\Component\Console\Helper\HelperSet(array(
    'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection()),
    'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em)
));