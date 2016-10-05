<?php

namespace App;


use App\DoctrineExtensions\DBAL\Types\UTCDateTimeType;
use App\Entity\Device;
use App\Event\Listener\ReferralListener;
use App\Event\ReferralStatusChangedEvent;
use App\Exception\CommonException;
use App\Exception\StatusException;
use App\Helper\EncryptionHelper;
use App\Helper\ResponseDataFormatter;
use App\Logger\Logger;
use App\Manager\AuthenticationManager;
use App\Manager\LocalImageManager;
use App\Manager\MailManager;
use App\Manager\SMSManager;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\UidProcessor;
use PHPMailer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Handlers\Strategies\RequestResponseArgs;
use Slim\Http\Response;
use App\Resource\AbstractResource as RestResource;
use Sly\NotificationPusher\Adapter\Apns;
use Sly\NotificationPusher\Adapter\Gcm;
use Sly\NotificationPusher\PushManager;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Configurator
{

    public function loadDependencyDefaults(ContainerInterface $container)
    {
        // event manager
        $container['eventManager'] = function(ContainerInterface $c) {
            return new EventDispatcher();
        };

        // notification
        $container['notificationManager'] = function(ContainerInterface $c) {
            return new PushManager(PushManager::ENVIRONMENT_PROD);
        };
        $container['notificationAdapter'] = function(ContainerInterface $c) {
            $settings = $c->get('settings')['pusher'];

            return function($type) use ($settings) {
                switch ($type) {
                    case Device::TYPE_APNS:
                        return new Apns($settings['apns']);
                    case Device::TYPE_GCM:
                        return new Gcm($settings['gcm']);
                }
                throw new CommonException('Push Notification Adapter not Supported');
            };
        };

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

            Type::overrideType('datetime', UTCDateTimeType::class);
            Type::overrideType('datetimetz', UTCDateTimeType::class);

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

        // mailer
        $container['mailer'] = function ($c) {

            $mail = new PHPMailer;
            $settings = $c->get('settings')['email'];
            if (array_key_exists('smtp', $settings)) {
                $mail->isSMTP();
                //Enable SMTP debugging
                // 0 = off (for production use)
                // 1 = client messages
                // 2 = client and server messages
                $mail->SMTPDebug = 0;
                if ($c->get('settings')['applicationMode'] != 'production') {
                    $mail->SMTPDebug = 2;
                    //Ask for HTML-friendly debug output
                    $mail->Debugoutput = 'html';
                }
                //Set the hostname of the mail server
                $mail->Host = $settings['smtp']['host'];
                // use
//                 $mail->Host = gethostbyname($settings['smtp']['host']);
                // if your network does not support SMTP over IPv6
                //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
                $mail->Port = $settings['smtp']['port'];
                //Set the encryption system to use - ssl (deprecated) or tls
                $mail->SMTPSecure = $settings['smtp']['sequre'];
                //Whether to use SMTP authentication
                $mail->SMTPAuth = $settings['smtp']['auth'];
                //Username to use for SMTP authentication - use full email address for gmail
                $mail->Username = $settings['smtp']['username'];
                //Password to use for SMTP authentication
                $mail->Password = $settings['smtp']['password'];
            }
            //Set who the message is to be sent from
            $mail->setFrom($settings['from'][0], $settings['from'][1]);
            //Set an alternative reply-to address
            $mail->addReplyTo($settings['replyTo'][0], $settings['replyTo'][1]);

            return $mail;
        };

        // email manager
        $container['mailManager'] = function($c) {
            return new MailManager($c);
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
        $c = $app->getContainer();
        $app->add(function ($request, $response, $next) {
            $request->registerMediaTypeParser(
                "application/json",
                function ($input) {
                    $result = json_decode($input, true);
                    if (json_last_error() != JSON_ERROR_NONE) {
                        throw new StatusException('Wrong body format', 500);
                    }
                    return $result;
                }
            );

            return $next($request, $response);
        });

        $app->add(function ($request, $response, $next) use ($c) {
            /**
             * @var EventDispatcher $eventManager
             */
            $eventManager = $c->get('eventManager');
            $eventManager->addListener(ReferralStatusChangedEvent::NAME, function($event) use ($c) {
                (new ReferralListener($c))->onStatusChanged($event);
            });

            return $next($request, $response);
        });

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
                $resource = RestResource::load($resource, $request, $response, $this);

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
                $resource = RestResource::load($resource, $request, $response, $this);

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
                $resource = RestResource::load($resource, $request, $response, $this);

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
                $resource = RestResource::load($resource, $request, $response, $this);
                $resource->delete($id, $subId);
                return $response->withJson($formatter->getSuccess());
            } catch (StatusException $e) {
                return $response->withJson($formatter->getFailure($e->getMessage()), $e->getCode());
            }
        });

    }
}