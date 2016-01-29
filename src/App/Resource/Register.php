<?php

namespace App\Resource;


use App\DAO\UserDAO;
use App\Exception\StatusException;
use App\Resource;

class Register extends AbstractResource {
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

    public function post() {
        $data = $this->getRequest()->getParsedBody();

        try {
            if (empty($data['email']))
                throw new \InvalidArgumentException('"email" missed');
            if (empty($data['password']))
                throw new \InvalidArgumentException('"password" missed');
            if (empty($data['firstName']))
                throw new \InvalidArgumentException('"firstName" missed');
            if (empty($data['phone']))
                throw new \InvalidArgumentException('"phone" missed');
            if (empty($data['lastName']))
                throw new \InvalidArgumentException('"lastName" missed');
            if ($this->getUserService()->isEmailExist($data['email']))
                throw new \InvalidArgumentException('email "' . $data['email'] . '"" exists already');

            $data['password'] = $this->getServiceLocator()->get('encryptionHelper')->getHash($data['password']);

            $user = $this->getUserService()->createUser($data);
            return array(
                'id' => $user->getId(),
                'created' => $user->getCreated(),
                'updated' => $user->getUpdated(),
                'email' => $user->getEmail()
            );
        } catch (\InvalidArgumentException $e) {
            throw new StatusException($e->getMessage(), self::STATUS_BAD_REQUEST);
        }
    }
}