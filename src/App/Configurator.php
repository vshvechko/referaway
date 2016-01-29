<?php

namespace App;


use App\Helper\EncryptionHelper;
use App\Helper\ResponseDataFormatter;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Slim\Handlers\Strategies\RequestResponseArgs;

class Configurator {

    public function loadDependencyDefaults(ContainerInterface $container) {
        // logger
        $container['logger'] = function (ContainerInterface $c) {
            $settings = $c->get('settings')['logger'];
            $debug = array_key_exists('debug', $settings) && $settings['debug'];
            $logger = new Logger($settings['name']);
            $logger->pushProcessor(new UidProcessor());
            $logger->pushHandler(new StreamHandler($settings['path'], $debug ? Logger::DEBUG : Logger::ERROR));
            return $logger;
        };

        // entity manager
        $container['entityManager'] = function (ContainerInterface $c) {
            $settings = $c->get('settings')['database'];

            $config = new Configuration();
            $config->setMetadataCacheImpl(new ArrayCache());
            $driverImpl = $config->newDefaultAnnotationDriver(array(__DIR__ . '/Entity'));
            $config->setMetadataDriverImpl($driverImpl);

            $config->setProxyDir(__DIR__ . '/Entity/Proxy');
            $config->setProxyNamespace('Proxy');

            return EntityManager::create($settings, $config);
        };

        $container['foundHandler'] = function () {
            return new RequestResponseArgs();
        };

        $container['dataFormatter'] = function () {
            return new ResponseDataFormatter();
        };

        $container['encryptionHelper'] = function () {
            return new EncryptionHelper();
        };

        // error handler
        if (!$container->get('settings')['displayErrorDetails']) {
            $container['errorHandler'] = function ($c) {
                return function ($request, ResponseInterface $response, \Exception $e) use ($c) {
                    /**
                     * @var Logger $logger
                     */
                    $logger = $c->get('logger');
                    $logger->err(sprintf('%s code %s in file %s:%s', $e->getMessage(),
                        $e->getCode(), $e->getFile(), $e->getLine()));
                    $logger->debug($e->getTraceAsString());

                    /**
                     * @var ResponseDataFormatter $formatter
                     */
                    $formatter = $c->get('dataFormatter');
                    return $response->withJson($formatter->getFailure('Service error'), 500);
                };
            };
        }

        return $this;
    }

    public function initMiddleware(App $app) {
        return $this;
    }
}