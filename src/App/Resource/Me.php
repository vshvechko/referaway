<?php

namespace App\Resource;


use App\DAO\UserDAO;
use App\Exception\StatusException;
use App\Resource\ViewModel\Helper\User;

class Me extends AbstractResource {
    use User;

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

    public function get($id) {
        $user = $this->authenticateUser();

        return ['user' => $this->exportUserArray($user)];
    }

}