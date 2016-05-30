<?php

namespace App\Resource;


use App\DAO\UserDAO;
use App\Entity\User as UserEntity;
use App\Exception\StatusException;
use App\Resource\ViewModel\Helper\User as UserHelper;

class Me extends AbstractResource {

    const REQUEST_ACTION = 'action';
    const ACTION_ACTIVATE = 'activate';
    const ACTION_SEND_CODE = 'sendActivationCode';
    const REQUEST_CODE = 'code';


    /**
     * @var UserDAO
     */
    private $userService;

    /**
     * Get user service
     */
    public function init() {
        $this->setUserService(new UserDAO($this->getEntityManager()));
    }

    /**
     * @return UserDAO
     */
    public function getUserService() {
        return $this->userService;
    }

    /**
     * @param UserDAO $userService
     */
    public function setUserService($userService) {
        $this->userService = $userService;
    }

    public function get($id, $subId = null) {
        $user = $this->authenticateUser();

        return ['user' => (new UserHelper())->exportUserArray($user, $this->getServiceLocator()->get('imageService'))];
    }

    public function post($id = null) {
        $user = $this->authenticateUser(false);
        if (is_null($user))
            throw new StatusException('User not found', self::STATUS_NOT_FOUND);

        $data = $this->getRequest()->getParsedBody();

        $action = $data['action'];

        switch ($action) {
            case self::ACTION_ACTIVATE:
                return $this->doActionActivate($user, $data);
            case self::ACTION_SEND_CODE:
                return $this->doActionSendCode($user, $data);
            default:
                throw new StatusException('Action not supported', self::STATUS_BAD_REQUEST);
        }
    }

    private function doActionActivate(UserEntity $user, $data) {
        try {
            if ($user->getIsActive() != UserEntity::STATUS_ACTIVE) {
                $service = $this->getUserService();

                $code = empty($data[self::REQUEST_CODE]) ? null : $data[self::REQUEST_CODE];
                if (is_null($code) || $code != $user->getActivationCode())
                    throw new StatusException('Invalid activation code', self::STATUS_NOT_FOUND);

                $user->setIsActive(UserEntity::STATUS_ACTIVE);
                $service->save($user);
            }
        } catch (\InvalidArgumentException $e) {
            throw new StatusException($e->getMessage(), self::STATUS_BAD_REQUEST);
        }
        return null;
    }

    private function doActionSendCode(UserEntity $user, $data) {
        try {
            if ($user->getIsActive() != UserEntity::STATUS_ACTIVE) {
                $service = $this->getUserService();

                $encoder = $this->getServiceLocator()->get('encryptionHelper');
                $code = $encoder->generateShortCode();

                $smsManager = $this->getServiceLocator()->get('smsService');

                $user->setActivationCode($code);
                $service->save($user);

                $smsManager->sendActivationCode($user->getPhone(), $code);

                //TODO SEND BY SMS
                return ['code' => $code];
            } else {
                throw new StatusException('User is active', self::STATUS_OK);
            }
        } catch (\InvalidArgumentException $e) {
            throw new StatusException($e->getMessage(), self::STATUS_BAD_REQUEST);
        }
        return null;
    }

}