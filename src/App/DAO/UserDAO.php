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

        /**
         * @var \App\Entity\User $user
         */
        $data = array();
        foreach ($users as $user)
        {
            $data[] = array(
                'id' => $user->getId(),
                'created' => $user->getCreated(),
                'updated' => $user->getUpdated(),
                'email' => $user->getEmail(),
            );
        }

        return $data;
    }

    /**
     * @param $data
     * @return UserEntity
     */
    public function createUser($data)
    {
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
        if ($this->isEmailExist($data['email']))
            throw new \InvalidArgumentException('email "' . $data['email'] . '"" exists already');

        $user = new UserEntity();
        $user->populate($data);

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        return $user;
    }

    /**
     * @param $id
     * @param $email
     * @param $password
     * @return array|null
     */
    public function updateUser($id, $email, $password)
    {
        /**
         * @var \App\Entity\User $user
         */
        $repository = $this->getEntityManager()->getRepository('App\Entity\User');
        $user = $repository->find($id);

        if ($user === null) {
            return null;
        }

        $user->setEmail($email);
        $user->setPassword($password);
        $user->setUpdated(new \DateTime());

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        return array(
            'id' => $user->getId(),
            'created' => $user->getCreated(),
            'updated' => $user->getUpdated(),
            'email' => $user->getEmail()
        );
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

    public function findByToken() {
        throw new \AssertionError("NOT IMPLEMENTED");
    }
}