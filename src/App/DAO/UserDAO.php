<?php

namespace App\DAO;

use App\Entity\User as UserEntity;
use Doctrine\ORM\Query;

class UserDAO extends AbstractDAO {

    protected function getRepositoryName() {
        return 'App\Entity\User';
    }

    /**
     * @param $data
     * @return UserEntity
     */
    public function createUser($data) {
        $user = new UserEntity();
        $user->populate($data);

        $this->save($user);

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
        $user = $this->findById($id);

        if ($user === null) {
            return null;
        }

        $user->populate($data);
        $user->setUpdated(new \DateTime());

        $this->save($user);

        return $user;
    }

    public function deleteUser($id) {
        /**
         * @var \App\Entity\User $user
         */
        $user = $this->findById($id);

        if ($user === null) {
            return false;
        }

        $this->remove($user);

        return true;
    }

    public function isEmailExist($email) {
        $user = $this->findByEmail($email);
        return $user !== null;
    }

    /**
     * @param $token
     * @return null|UserEntity
     */
    public function findByToken($token, $hydrate = false, $skipCache = false) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e')
            ->from($this->getRepositoryName(), 'e')
            ->where($qb->expr()->eq('e.token', ':token'))->setParameter('token', $token);

        return $qb->getQuery()->useResultCache(!$skipCache, null)->getOneOrNullResult($hydrate ? Query::HYDRATE_ARRAY : null);
    }

    /**
     * @param $email
     * @return null|UserEntity
     */
    public function findByEmail($email, $hydrate = false, $skipCache = false) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e')
            ->from($this->getRepositoryName(), 'e')
            ->where($qb->expr()->eq('e.email', ':email'))->setParameter('email', $email);

        return $qb->getQuery()->useResultCache(!$skipCache, null)->getOneOrNullResult($hydrate ? Query::HYDRATE_ARRAY : null);
    }

}