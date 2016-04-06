<?php

namespace App\Resource;

use App\DAO\UserDAO;
use App\Entity\User;
use App\Exception\StatusException;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Validatable;
use Slim\Http\Response;

abstract class AbstractResource {
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

    protected $validators = [];

    public function __construct(ServerRequestInterface $request, Response $response, ContainerInterface $di) {
        $this->setRequest($request);
        $this->setResponse($response);
        $this->setServiceLocator($di);

        $this->init();
    }

    public function init() {
    }

    /**
     * Default get method
     * @param null $id
     * @param null $subId
     * @return mixed
     * @throws StatusException
     */
    public function get($id = null, $subId = null) {
        throw new StatusException('Not allowed', self::STATUS_METHOD_NOT_ALLOWED);
    }

    /**
     * Default post method
     * @param null $id
     * @return mixed
     * @throws StatusException
     */
    public function post($id = null) {
        throw new StatusException('Not allowed', self::STATUS_METHOD_NOT_ALLOWED);
    }

    /**
     * Default put method
     * @param $id
     * @param null $subId
     * @return mixed
     * @throws StatusException
     */
    public function put($id, $subId = null) {
        throw new StatusException('Not allowed', self::STATUS_METHOD_NOT_ALLOWED);
    }

    /**
     * Default delete method
     * @param $id
     * @param null $subId
     * @return mixed
     * @throws StatusException
     */
    public function delete($id, $subId = null) {
        throw new StatusException('Not allowed', self::STATUS_METHOD_NOT_ALLOWED);
    }

    /**
     * @param $resource
     * @param ServerRequestInterface $request
     * @param Response $response
     * @param ContainerInterface $di
     * @return AbstractResource
     * @throws StatusException
     */
    public static function load($resource, ServerRequestInterface $request, Response $response, ContainerInterface $di) {
        $subResource = $request->getAttribute('route')->getArgument('sub');
        $nameSpace = 'App\\Resource\\';
        $resource = ucfirst($resource);
        if ($subResource) {
            $nameSpace .= 'Sub\\';
            $resource = $resource . ucfirst($subResource);
        }
        $class = $nameSpace . $resource;
        if (!class_exists($class)) {
            throw new StatusException('Resource not exists', self::STATUS_NOT_FOUND);
        }

        return new $class($request, $response, $di);
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager() {
        return $this->serviceLocator->get('entityManager');
    }

    /**
     * @return ServerRequestInterface
     * @throws \AssertionError
     */
    public function getRequest() {
        if (!$this->request) {
            throw new \AssertionError();
        }
        return $this->request;
    }

    /**
     * @param ServerRequestInterface $request
     */
    public function setRequest($request) {
        $this->request = $request;
    }

    /**
     * @return Response
     * @throws \AssertionError
     */
    public function getResponse() {
        if (!$this->response) {
            throw new \AssertionError();
        }
        return $this->response;
    }

    /**
     * @param Response $response
     */
    public function setResponse($response) {
        $this->response = $response;
    }

    /**
     * @return ContainerInterface
     */
    public function getServiceLocator() {
        return $this->serviceLocator;
    }

    /**
     * @param ContainerInterface $serviceLocator
     */
    public function setServiceLocator($serviceLocator) {
        $this->serviceLocator = $serviceLocator;
    }

    protected function authenticateUser($activeOnly = true) {
        $logger = $this->getServiceLocator()->get('logger');
        $logger->debug(__CLASS__ . '::authenticateUser');
        try {
            $service = $this->getServiceLocator()->get('authService');
            $token = $service->getAuthToken();

            $dao = new UserDAO($this->getEntityManager());
            $user = $dao->findByToken($token);
            if (is_null($user))
                throw new \Exception('User not found');

//            if ($activeOnly && $user->getIsActive() != User::STATUS_ACTIVE)
//                throw new StatusException('User not activated', self::STATUS_UNAUTHORIZED);

            return $user;
        } catch (StatusException $e) {
            throw $e;
        } catch (\Exception $e) {
            $logger->err(sprintf('%s in %s:%s', $e->getMessage(), $e->getFile(), $e->getLine()));
            $logger->debug($e->getTraceAsString());

            throw new StatusException('Authentication error', self::STATUS_UNAUTHORIZED, $e);
        }
    }

    protected function addValidator($key, $value) {
        $this->validators[$key] = $value;
    }

    protected function validateArray(array $data) {
        /**
         * @var Validatable $validator
         */
        foreach ($this->validators as $key => $validator) {
            $value = array_key_exists($key, $data) ? $data[$key] : null;
            $validator->check($value);
        }
    }

    protected function clearValidators() {
        $this->validators = [];
    }
}