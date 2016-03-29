<?php

namespace App\Resource;


use App\Exception\StatusException;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Validator as v;

class Image extends AbstractResource {

    const REQUEST_TYPE = 'type';

    public function post() {
        try {
            $resource = $this->getResource();

            return $resource->post();
        } catch (ValidationException $e) {
            throw new StatusException($e->getMainMessage(), self::STATUS_BAD_REQUEST);
        } catch (\InvalidArgumentException $e) {
            throw new StatusException($e->getMessage(), self::STATUS_BAD_REQUEST);
        }
    }

    public function put($id) {
        try {
            $resource = $this->getResource();

            return $resource->put($id);
        } catch (ValidationException $e) {
            throw new StatusException($e->getMainMessage(), self::STATUS_BAD_REQUEST);
        } catch (\InvalidArgumentException $e) {
            throw new StatusException($e->getMessage(), self::STATUS_BAD_REQUEST);
        }
    }

    public function delete($id) {
        try {
            $resource = $this->getResource();

            return $resource->delete($id);
        } catch (ValidationException $e) {
            throw new StatusException($e->getMainMessage(), self::STATUS_BAD_REQUEST);
        } catch (\InvalidArgumentException $e) {
            throw new StatusException($e->getMessage(), self::STATUS_BAD_REQUEST);
        }
    }

    protected function getResource() {
        $params = $this->getRequest()->getQueryParams();
        $this->addValidator(self::REQUEST_TYPE, v::notEmpty()->setName('type'));
        $this->validateArray($params);

        $type = $params[self::REQUEST_TYPE];
        $resName = $class = 'Media\\' . ucfirst($type);
        try {
            return self::load($resName, $this->getRequest(), $this->getResponse(), $this->getServiceLocator());
        } catch (StatusException $e) {
            throw new StatusException('Type not supported', self::STATUS_BAD_REQUEST);
        }
    }
 }