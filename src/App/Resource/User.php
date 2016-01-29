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
            $users = $this->getUserService()->getUsers();
            /**
             * @var \App\Entity\User $user
             */
            $data = [];
            foreach ($users as $user) {
                $data[] = [
                    'id' => $user->getId(),
                    'created' => $user->getCreated(),
                    'updated' => $user->getUpdated(),
                    'email' => $user->getEmail(),
                ];
            }

        } else {
            $user = $this->getUserService()->getUser($id);
            if ($user === null) {
                throw new StatusException('User not found', self::STATUS_NOT_FOUND);
            }
            $data = [
                'id' => $user->getId(),
                'created' => $user->getCreated(),
                'updated' => $user->getUpdated(),
                'email' => $user->getEmail()
            ];
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
            return [
                'id' => $user->getId(),
                'created' => $user->getCreated(),
                'updated' => $user->getUpdated(),
                'email' => $user->getEmail()
            ];
        } catch (\InvalidArgumentException $e) {
            throw new StatusException($e->getMessage(), self::STATUS_BAD_REQUEST);
        }
    }

    /**
     * Update user
     */
    public function put($id)
    {
        $data = $this->getRequest()->getParsedBody();

        $user = $this->getUserService()->updateUser($id, $data);

        if ($user === null) {
            throw new StatusException('Not found', self::STATUS_NOT_FOUND);
        }

        return [
            'id' => $user->getId(),
            'created' => $user->getCreated(),
            'updated' => $user->getUpdated(),
            'email' => $user->getEmail()
        ];

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

}