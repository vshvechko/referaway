<?php

namespace App\Resource;

use App\Exception\StatusException;
use App\Resource;
use App\DAO\UserDAO;

class User extends AbstractResource {
    use Resource\ViewModel\Helper\User;
    /**
     * @var UserDAO
     */
    private $userService;

    /**
     * Get user service
     */
    public function init() {
        $this->setService(new UserDAO($this->getEntityManager()));
    }

    /**
     * @param null $id
     * @return \Slim\Http\Response|void
     * @throws StatusException
     */
    public function get($id = null) {
        if ($id === null) {
            $users = $this->getService()->findAll();
            /**
             * @var \App\Entity\User $user
             */
            $data = [];
            foreach ($users as $user) {
                $data[] = $this->exportUserArray($user);
            }

        } else {
            $user = $this->getService()->findById($id);
            if ($user === null) {
                throw new StatusException('User not found', self::STATUS_NOT_FOUND);
            }
            $data = $this->exportUserArray($user);
        }

        return $data;
    }

    /**
     * Create user
     */
    public function post() {
        $obj = $this->getRequest()->getParsedBody();

        try {
            $user = $this->getService()->createUser($obj);
            return $this->exportUserArray($user);
        } catch (\InvalidArgumentException $e) {
            throw new StatusException($e->getMessage(), self::STATUS_BAD_REQUEST);
        }
    }

    /**
     * Update user
     */
    public function put($id) {
        $data = $this->getRequest()->getParsedBody();

        $user = $this->getService()->updateUser($id, $data);

        if ($user === null) {
            throw new StatusException('Not found', self::STATUS_NOT_FOUND);
        }

        return $this->exportUserArray($user);

    }

    /**
     * @param $id
     * @return bool|void
     * @throws StatusException
     */
    public function delete($id) {
        $status = $this->getService()->deleteUser($id);

        if ($status === false) {
            throw new StatusException('Not found', self::STATUS_NOT_FOUND);
        }

        return true;
    }

    /**
     * @return UserDAO
     */
    public function getService() {
        return $this->userService;
    }

    /**
     * @param UserDAO $userService
     */
    public function setService($userService) {
        $this->userService = $userService;
    }

}