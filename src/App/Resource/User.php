<?php

namespace App\Resource;

use App\Entity\User as UserEntity;
use App\Exception\StatusException;
use App\Resource;
use App\DAO\UserDAO;
use App\Resource\ViewModel\Helper\User as UserHelper;

class User extends AbstractResource {
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
    public function _get($id = null) {
        if ($id === null) {
            $users = $this->getService()->findAll();
            /**
             * @var UserEntity $user
             */
            $data = [];
            foreach ($users as $user) {
                $data[] = (new UserHelper())->exportUserArray($user, $this->getServiceLocator()->get('imageService'));
            }

        } else {
            $user = $this->getService()->findById($id);
            if ($user === null) {
                throw new StatusException('User not found', self::STATUS_NOT_FOUND);
            }
            $data = (new UserHelper())->exportUserArray($user, $this->getServiceLocator()->get('imageService'));
        }

        return $data;
    }

    /**
     * Create user
     */
    public function _post() {
        $obj = $this->getRequest()->getParsedBody();

        try {
            $user = $this->getService()->createUser($obj);
            return (new UserHelper())->exportUserArray($user, $this->getServiceLocator()->get('imageService'));
        } catch (\InvalidArgumentException $e) {
            throw new StatusException($e->getMessage(), self::STATUS_BAD_REQUEST);
        }
    }

    /**
     * Update user
     */
    public function _put($id) {
        $data = $this->getRequest()->getParsedBody();

        $user = $this->getService()->updateUser($id, $data);

        if ($user === null) {
            throw new StatusException('Not found', self::STATUS_NOT_FOUND);
        }

        return (new UserHelper())->exportUserArray($user, $this->getServiceLocator()->get('imageService'));

    }

    /**
     * @param $id
     * @return bool|void
     * @throws StatusException
     */
    public function _delete($id) {
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