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
     * @param bool $flush
     * @return UserEntity
     */
    public function createUser($data, $flush = true) {
        $user = new UserEntity();
        $user->populate($data);

        $this->save($user, $flush);

        return $user;
    }

    /**
     * @param $id
     * @param array $data
     * @return \App\Entity\User|null
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

    public function isEmailExist($email, $exceptId = null) {
        $user = $this->findByEmail($email);
        if (!empty($user)) {
            return $exceptId ? ($user->getId() != $exceptId) : true;
        }
        return false;
    }

    public function isPhoneExist($phone, $exceptId = null) {
        $user = $this->findByPhone($phone);
        if (!empty($user)) {
            return $exceptId ? ($user->getId() != $exceptId) : true;
        }
        return false;
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
     * @param bool $hydrate
     * @param bool $skipCache
     * @return UserEntity|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByEmail($email, $hydrate = false, $skipCache = false) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e')
            ->from($this->getRepositoryName(), 'e')
            ->where($qb->expr()->eq('LOWER(e.email)', ':email'))->setParameter('email', strtolower($email));

        return $qb->getQuery()->useResultCache(!$skipCache, null)->getOneOrNullResult($hydrate ? Query::HYDRATE_ARRAY : null);
    }

    public function findByPhone($phone, $hydrate = false, $skipCache = false) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e')
            ->from($this->getRepositoryName(), 'e')
            ->where($qb->expr()->eq('e.phone', ':phone'))->setParameter('phone', $phone);

        return $qb->getQuery()->useResultCache(!$skipCache, null)->getOneOrNullResult($hydrate ? Query::HYDRATE_ARRAY : null);
    }

}