<?php

namespace App\Resource;


use App\DAO\DeviceDAO;
use App\Entity\Device as DeviceEntity;
use App\Exception\StatusException;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Validator as v;

class Device extends AbstractResource
{
    /**
     * @var DeviceDAO
     */
    private $service;

    /**
     * @return DeviceDAO
     */
    protected function getService() {
        return $this->service;
    }

    /**
     * @param DeviceDAO $service
     */
    public function setService($service) {
        $this->service = $service;
    }


    /**
     * Get user service
     */
    public function init() {
        $this->setService(new DeviceDAO($this->getEntityManager()));
    }


    public function post($id = null)
    {
        $logger = $this->getServiceLocator()->get('logger');
        $logger->debug(__METHOD__);

        $user = $this->authenticateUser();

        $data = $this->getRequest()->getParsedBody();
        if (!is_array($this->getRequest()->getParsedBody())) {
            $data = [];
        }

        try {
            $this->addValidator('token', v::notEmpty()->setName('token'));
            $this->addValidator(
                'type',
                v::in([DeviceEntity::TYPE_APNS, DeviceEntity::TYPE_GCM])
                    ->setName('type')
            );
            $this->validateArray($data);

            $device = $user->getDevice();
            if (is_null($device)) {
                $device = new DeviceEntity();
            }
            $device->populate($data)
                ->setUser($user)
                ->setUpdatedValue();

            $entity = $this->getService()->save($device);

        } catch (ValidationException $e) {
            throw new StatusException($e->getMainMessage(), self::STATUS_BAD_REQUEST);
        } catch (\InvalidArgumentException $e) {
            throw new StatusException($e->getMessage(), self::STATUS_BAD_REQUEST);
        }
    }

    public function delete($id, $subId = null)
    {
        $logger = $this->getServiceLocator()->get('logger');
        $logger->debug(__METHOD__);

        $user = $this->authenticateUser();

        try {
            $device = $user->getDevice();
            if (!is_null($device)) {
                $this->getService()->remove($device);
                $user->setDevice(null);
            }
        } catch (ValidationException $e) {
            throw new StatusException($e->getMainMessage(), self::STATUS_BAD_REQUEST);
        } catch (\InvalidArgumentException $e) {
            throw new StatusException($e->getMessage(), self::STATUS_BAD_REQUEST);
        }
    }
}