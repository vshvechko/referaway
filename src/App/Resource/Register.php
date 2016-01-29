<?php

namespace App\Resource;


use App\Exception\StatusException;
use App\Resource;
use App\Service\User as UserService;

class Register extends AbstractResource {
    /**
     * @var \App\Service\User
     */
    private $userService;

    /**
     * Get user service
     */
    public function init()
    {
        $this->setUserService(new UserService($this->getEntityManager()));
    }

    /**
     * @return \App\Service\User
     */
    public function getUserService()
    {
        return $this->userService;
    }

    /**
     * @param \App\Service\User $userService
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