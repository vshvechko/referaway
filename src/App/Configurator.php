<?php

namespace App;


use App\Helper\EncryptionHelper;
use App\Helper\ResponseDataFormatter;
use App\Manager\AuthenticationManager;
use App\Manager\SMSManager;
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
            $applicationMode = $c->get('settings')['applicationMode'];

            $cache = new ArrayCache; // TODO

            $config = new Configuration;
            $config->setMetadataCacheImpl($cache);
            $driverImpl = $config->newDefaultAnnotationDriver(array(__DIR__ . '/Entity'));
            $config->setMetadataDriverImpl($driverImpl);
            $config->setQueryCacheImpl($cache);
            $config->setResultCacheImpl($cache);
            $config->setProxyDir(__DIR__ . '/Entity/Proxy');
            $config->setProxyNamespace('App\Entity\Proxy');

            if ($applicationMode == 'development') {
                $config->setAutoGenerateProxyClasses(true);
            } else {
                $config->setAutoGenerateProxyClasses(false);
            }

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

        $container['authService'] = function ($c) {
            return new AuthenticationManager($c);
        };

        $container['smsService'] = function ($c) {
            return new SMSManager($c);
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