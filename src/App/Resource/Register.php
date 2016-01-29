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
    public function init()
    {
        $this->setUserService(new UserDAO($this->getEntityManager()));
    }

    /**
     * @return UserDAO
     */
    public function getUserService()
    {
        return $this->userService;
    }

    /**
     * @param UserDAO $userService
     */
    public function setUserService($userService)
    {
        $this->userService = $userService;
    }

    public function post() {
        $obj = $this->getRequest()->getParsedBody();

        try {
            $user = $this->getUserService()->createUser($obj);
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