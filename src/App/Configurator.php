<?php

namespace App;


use App\Exception\StatusException;
use App\Helper\EncryptionHelper;
use App\Helper\ResponseDataFormatter;
use App\Logger\Logger;
use App\Manager\AuthenticationManager;
use App\Manager\LocalImageManager;
use App\Manager\SMSManager;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\UidProcessor;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Handlers\Strategies\RequestResponseArgs;
use Slim\Http\Response;
use App\Resource\AbstractResource as Resource;

class Configurator
{

    public function loadDependencyDefaults(ContainerInterface $container)
    {
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
                $config->setSQLLogger($c->get('logger'));
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
        
        $container['imageService'] = function ($c) {
            $settings = $c->get('settings')['CDN'];
            $imageManager = new LocalImageManager($c);
            $imageManager->setUploadDir($settings['uploadDir'])
                ->setUploadUrl($settings['uploadUrl']);
            return $imageManager;
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

    public function initMiddleware(App $app)
    {
        return $this;
    }

    public function initRoutes(App $app)
    {
        // Get
        $app->get('/{resource}[/{id}[/{sub}[/{subId}]]]', function (ServerRequestInterface $request, Response $response,
                                                                    $resource, $id = null, $sub = null, $subId = null) {
            /**
             * @var ResponseDataFormatter $formatter
             */
            $formatter = $this->get('dataFormatter');
            try {
                $resource = Resource::load($resource, $request, $response, $this);

                return $response->withJson($formatter->getSuccess($resource->get($id, $subId)));
            } catch (StatusException $e) {
                return $response->withJson($formatter->getFailure($e->getMessage()), $e->getCode());
            }
        });

        // Post
        $app->post('/{resource}[/{id}/{sub}]', function (ServerRequestInterface $request, Response $response,
                                                              $resource, $id = null, $sub = null) {
            /**
             * @var ResponseDataFormatter $formatter
             */
            $formatter = $this->get('dataFormatter');
            try {
                $resource = Resource::load($resource, $request, $response, $this);

                return $response->withJson($formatter->getSuccess($resource->post($id)));
            } catch (StatusException $e) {
                return $response->withJson($formatter->getFailure($e->getMessage()), $e->getCode());
            }
        });

        // Put
        $app->put('/{resource}/{id}[/{sub}[/{subId}]]', function (ServerRequestInterface $request, Response $response,
                                                                  $resource, $id = null, $sub = null, $subId = null) {
            /**
             * @var ResponseDataFormatter $formatter
             */
            $formatter = $this->get('dataFormatter');
            try {
                $resource = Resource::load($resource, $request, $response, $this);

                return $response->withJson($formatter->getSuccess($resource->put($id, $subId)));
            } catch (StatusException $e) {
                return $response->withJson($formatter->getFailure($e->getMessage()), $e->getCode());
            }
        });

        // Delete
        $app->delete('/{resource}/{id}[/{sub}[/{subId}]]', function (ServerRequestInterface $request, Response $response,
                                                                     $resource, $id = null, $sub = null, $subId = null) {
            /**
             * @var ResponseDataFormatter $formatter
             */
            $formatter = $this->get('dataFormatter');
            try {
                $resource = Resource::load($resource, $request, $response, $this);
                $resource->delete($id, $subId);
                return $response->withJson($formatter->getSuccess());
            } catch (StatusException $e) {
                return $response->withJson($formatter->getFailure($e->getMessage()), $e->getCode());
            }
        });

    }
}