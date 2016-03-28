<?php

namespace App\DAO;


use App\Entity\Contact;
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
    public function createGroup($data, User $user = null) {
        $entity = new GroupEntity();
        $entity->populate($data);
        if (!is_null($user)){
            $entity->setOwner($user);
        }

        $this->save($entity);

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

    /**
     * Search groups related to user
     * @inheritdoc
     */
    public function findGroupsByUser(User $user, $search = null, $hydrate = false, $skipCache = false) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('g, ug, c, u')
            ->from($this->getRepositoryName(), 'g')
            ->leftJoin('g.members', 'ug')
            ->leftJoin('ug.contact', 'c')
            ->leftJoin('c.user', 'u')
            ->where(
                $qb->expr()->orX(
                    $qb->expr()->eq('g.owner', ':user'),
                    $qb->expr()->eq('c.user', ':user')
                )
            )->setParameter('user', $user);
        if ($search) {
            $qb->andWhere(
                $qb->expr()->like('LOWER(g.name)', ':s')
            )->setParameter('s', '%' . strtolower($search) . '%');
        }

        return $qb->getQuery()->useResultCache(!$skipCache, null)->getResult($hydrate ? Query::HYDRATE_ARRAY : null);
    }

    public function getGroupMembers($group, $hydrate = false, $skipCache = false) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('g, o, ug, c')
            ->from($this->getRepositoryName(), 'g')
            ->innerJoin('g.owner', 'o')
            ->leftJoin('g.members', 'ug', 'WITH', 'ug.memberStatus=:status')
            ->leftJoin('ug.contact', 'c')
            ->where($qb->expr()->eq('g.id', ':group'))
            ->setParameter('status', UserGroup::MEMBER_STATUS_MEMBER)
            ->setParameter('group', $group);
        return $qb->getQuery()->useResultCache(!$skipCache, null)->getOneOrNullResult($hydrate ? Query::HYDRATE_ARRAY : null);
    }

    public function isContactInGroup(Contact $contact, Group $group) {
        if ($contact->getUser() == $group->getOwner()) {
            return true;
        }

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('COUNT(ug)')
            ->from($this->getUserGroupRepositoryName(), 'ug')
            ->where(
                $qb->expr()->eq('ug.group', ':group')
            )
            ->setParameter('group', $group);

        if ($contact->getUser()) {
            $qb->innerJoin('ug.contact', 'c')
            ->andWhere(
                $qb->expr()->eq('c.user', ':user')
            )->setParameter('user', $contact->getUser());
        } else {
            $qb->andWhere(
                $qb->expr()->eq('ug.contact', ':contact')
            )
            ->setParameter('contact', $contact);
        }

        return $qb->getQuery()->getSingleScalarResult() != 0;
    }

    public function removeContact(Contact $contact, Group $group) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('ug')
            ->from($this->getUserGroupRepositoryName(), 'ug')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('ug.contact', ':contact'),
                    $qb->expr()->eq('ug.group', ':group')
                )
            )->setParameter('contact', $contact)
            ->setParameter('group', $group);
        /**
         * @var UserGroup $userGroup
         */
        $userGroup = $qb->getQuery()->getOneOrNullResult();
        if ($userGroup) {
            $userGroup->getGroup()->removeUserGroup($userGroup);
            $this->remove($userGroup);
        }
    }

    /**
     * @param $id
     * @param bool $hydrate
     * @param bool $skipCache
     * @return UserGroup|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findUserGroupById($id, $hydrate = false, $skipCache = false) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e')
            ->from($this->getUserGroupRepositoryName(), 'e')
            ->where($qb->expr()->eq('e.id', ':id'))->setParameter('id', $id);

        return $qb->getQuery()->useResultCache(!$skipCache, null)->getOneOrNullResult($hydrate ? Query::HYDRATE_ARRAY : null);
    }
    
    public function getUserGroupsByUserAndGroup(User $user, Group $group, $hydrate = false, $skipCache = false) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('ug')
            ->from($this->getUserGroupRepositoryName(), 'ug')
            ->innerJoin('ug.contact', 'c')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('c.user', ':user'),
                    $qb->expr()->eq('ug.group', ':group')
                )
            )->setParameter('user', $user)
            ->setParameter('group', $group);
        return $qb->getQuery()->useResultCache(!$skipCache, null)->getResult($hydrate ? Query::HYDRATE_ARRAY : null);
    }
}