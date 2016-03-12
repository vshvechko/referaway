<?php

namespace App\DAO;


use App\Entity\Group as GroupEntity;
use App\Entity\Group;
use App\Entity\User;
use App\Entity\UserGroup;
use Doctrine\ORM\Query;

class GroupDAO extends AbstractDAO {

    protected function getRepositoryName() {
        return 'App\Entity\Group';
    }

    protected function getUserGroupRepositoryName() {
        return 'App\Entity\UserGroup';
    }

    /**
     * @param $data
     * @param User $user
     * @return GroupEntity
     */
    public function createGroup($data,User $user = null) {
        $entity = new GroupEntity();
        $entity->populate($data);
        if (!is_null($user)){
            $entity->setOwner($user);
        }

        $this->save($entity);

        new UserGroup($user, $entity, UserGroup::ROLE_ADMIN);
        $this->getEntityManager()->flush();

        return $entity;
    }

    /**
     * @param $id
     * @param array $data
     * @return GroupEntity|null
     */
    public function updateGroup($id, array $data) {
        /**
         * @var GroupEntity $entity
         */
        $entity = $this->findById($id);

        if ($entity === null)
            throw new \InvalidArgumentException('Group not found');

        $entity->populate($data);

        $this->save($entity);

        return $entity;
    }

    public function exitFromGroup(User $user, Group $group) {
        $userGroup = $this->findUserGroup($user, $group);
        if (!is_null($userGroup)) {
            $this->remove($userGroup);
        }
    }

    public function enterToGroup(User $user, Group $group) {
        $userGroup = $this->findUserGroup($user, $group);
        if (is_null($userGroup)) {
            $userGroup = new UserGroup($user, $group);
            $this->save($userGroup);
        }
    }

    public function findUserGroup(User $user, Group $group, $hydrate = false, $skipCache = false) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e')
            ->from($this->getUserGroupRepositoryName(), 'e')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('e.user', ':user'),
                    $qb->expr()->eq('e.group', ':group')
                )
            )->setParameter('user', $user)
        ->setParameter('group', $group);

        return $qb->getQuery()->useResultCache(!$skipCache, null)->getOneOrNullResult($hydrate ? Query::HYDRATE_ARRAY : null);

    }
}