<?php

namespace App\Resource;

use App\Exception\StatusException;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Response;

abstract class AbstractResource
{
    const STATUS_OK = 200;
    const STATUS_CREATED = 201;
    const STATUS_ACCEPTED = 202;
    const STATUS_NO_CONTENT = 204;

    const STATUS_MULTIPLE_CHOICES = 300;
    const STATUS_MOVED_PERMANENTLY = 301;
    const STATUS_FOUND = 302;
    const STATUS_NOT_MODIFIED = 304;
    const STATUS_USE_PROXY = 305;
    const STATUS_TEMPORARY_REDIRECT = 307;

    const STATUS_BAD_REQUEST = 400;
    const STATUS_UNAUTHORIZED = 401;
    const STATUS_FORBIDDEN = 403;
    const STATUS_NOT_FOUND = 404;
    const STATUS_METHOD_NOT_ALLOWED = 405;
    const STATUS_NOT_ACCEPTED = 406;

    const STATUS_INTERNAL_SERVER_ERROR = 500;
    const STATUS_NOT_IMPLEMENTED = 501;

    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var ContainerInterface
     */
    protected $serviceLocator;
    
    public function __construct(ServerRequestInterface $request, Response $response, ContainerInterface $di)
    {
        $this->setRequest($request);
        $this->setResponse($response);
        $this->setServiceLocator($di);

        $this->init();
    }

    /**
     * Default init, use for overwrite only
     */
    public function init()
    {}

    /**
     * Default get method
     */
    public function get()
    {
        throw new StatusException('Not allowed', self::STATUS_METHOD_NOT_ALLOWED);
    }

    /**
     * Default post method
     */
    public function post()
    {
        throw new StatusException('Not allowed', self::STATUS_METHOD_NOT_ALLOWED);
    }

    /**
     * Default put method
     */
    public function put()
    {
        throw new StatusException('Not allowed', self::STATUS_METHOD_NOT_ALLOWED);
    }

    /**
     * Default delete method
     */
    public function delete()
    {
        throw new StatusException('Not allowed', self::STATUS_METHOD_NOT_ALLOWED);
    }

    /**
     * General options method
     */
    public function options()
    {
        throw new StatusException('Not allowed', self::STATUS_METHOD_NOT_ALLOWED);
    }

    /**
     * @param int $status
     * @param array $data
     * @return Response
     */
    public function response($status = 200, array $data = array())
    {
        $response = $this->getResponse()->withHeader('Content-Type', 'application/json');
        return $response->withJson($data, $status);
    }

    /**
     * @param $resource
     * @param ServerRequestInterface $request
     * @param Response $response
     * @param ContainerInterface $di
     * @return AbstractResource
     * @throws StatusException
     */
    public static function load($resource, ServerRequestInterface $request, Response $response, ContainerInterface $di)
    {
        $class = 'App\\Resource\\' . ucfirst($resource);
        if (!class_exists($class)) {
            throw new StatusException('Resource not exists', self::STATUS_NOT_FOUND);
        }

        return new $class($request, $response, $di);
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->serviceLocator->get('entityManager');
    }

    /**
     * @return ServerRequestInterface
     * @throws \AssertionError
     */
    public function getRequest()
    {
        if (!$this->request) {
            throw new \AssertionError();
        }
        return $this->request;
    }

    /**
     * @param ServerRequestInterface $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @return Response
     * @throws \AssertionError
     */
    public function getResponse()
    {
        if (!$this->response) {
            throw new \AssertionError();
        }
        return $this->response;
    }

    /**
     * @param Response $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * @return ContainerInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * @param ContainerInterface $serviceLocator
     */
    public function setServiceLocator($serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }
    
    
}