<?php

namespace App\DAO;

use App\Entity\User as UserEntity;

class UserDAO extends AbstractDAO
{
    /**
     * @param $id
     * @return UserEntity
     */
    public function getUser($id)
    {
        /**
         * @var \App\Entity\User $user
         */
        $repository = $this->getEntityManager()->getRepository('App\Entity\User');
        $user = $repository->find($id);

        if ($user === null) {
            return null;
        }

        return $user;
    }

    /**
     * @return array|null
     */
    public function getUsers()
    {
        $repository = $this->getEntityManager()->getRepository('App\Entity\User');
        $users = $repository->findAll();

        if (empty($users)) {
            return null;
        }

        return $users;
    }

    /**
     * @param $data
     * @return UserEntity
     */
    public function createUser($data)
    {
        $user = new UserEntity();
        $user->populate($data);

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        return $user;
    }

    /**
     * @param $id
     * @param array $data
     * @return array|null
     */
    public function updateUser($id, array $data) {
        /**
         * @var \App\Entity\User $user
         */
        $repository = $this->getEntityManager()->getRepository('App\Entity\User');
        $user = $repository->find($id);

        if ($user === null) {
            return null;
        }

        $user->populate($data);
        $user->setUpdated(new \DateTime());

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        return $user;
    }

    public function deleteUser($id)
    {
        /**
         * @var \App\Entity\User $user
         */
        $repository = $this->getEntityManager()->getRepository('App\Entity\User');
        $user = $repository->find($id);

        if ($user === null) {
            return false;
        }

        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();

        return true;
    }

    public function isEmailExist($email) {
        $user = $this->getEntityManager()->getRepository('App\Entity\User')->findOneBy(array('email' => $email));
        return $user !== null;
    }

    public function findByToken($token) {
        return $this->getEntityManager()->getRepository('App\Entity\User')->findOneBy(array('token' => $token));
    }

    /**
     * @param $email
     * @return null|UserEntity
     */
    public function findByEmail($email) {
        return $this->getEntityManager()->getRepository('App\Entity\User')->findOneBy(array('email' => $email));
    }

}