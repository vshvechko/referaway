<?php

namespace App\Resource;

use App\Exception\StatusException;
use App\Resource;
use App\DAO\UserDAO;

class User extends AbstractResource
{
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
     * @param null $id
     * @return \Slim\Http\Response|void
     * @throws StatusException
     */
    public function get($id = null)
    {
        if ($id === null) {
            $data = $this->getUserService()->getUsers();
        } else {
            $user = $this->getUserService()->getUser($id);
            if ($user === null) {
                throw new StatusException('User not found', self::STATUS_NOT_FOUND);
            }
            $data = array(
                'id' => $user->getId(),
                'created' => $user->getCreated(),
                'updated' => $user->getUpdated(),
                'email' => $user->getEmail()
            );
        }

        return $data;
    }

    /**
     * Create user
     */
    public function post()
    {
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

    /**
     * Update user
     */
    public function put($id)
    {
        $email = $this->getSlim()->request()->params('email');
        $password = $this->getSlim()->request()->params('password');

        if (empty($email) && empty($password) || $email === null && $password === null) {
            self::response(self::STATUS_BAD_REQUEST);
            return;
        }

        $user = $this->getUserService()->updateUser($id, $email, $password);

        if ($user === null) {
            self::response(self::STATUS_NOT_IMPLEMENTED);
            return;
        }

        self::response(self::STATUS_NO_CONTENT);
    }

    /**
     * @param $id
     * @return bool|void
     * @throws StatusException
     */
    public function delete($id)
    {
        $status = $this->getUserService()->deleteUser($id);

        if ($status === false) {
            throw new StatusException('Not found', self::STATUS_NOT_FOUND);
        }

        return true;
    }

    /**
     * Show options in header
     */
    public function options()
    {
        self::response(self::STATUS_OK, array(), array('GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'));
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

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
}