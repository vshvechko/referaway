<?php

namespace App\DAO;

use App\Entity\Contact as ContactEntity;
use App\Entity\ContactCustomField;
use App\Entity\User as UserEntity;
use Doctrine\ORM\Query;

class ContactDAO extends AbstractDAO {

    protected function getRepositoryName() {
        return 'App\Entity\Contact';
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

    public function getUserContact(UserEntity $user, $contactId, $hydrate = false, $skipCache = false) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e, owner, fields')
            ->from($this->getRepositoryName(), 'e')
            ->innerJoin('e.owner', 'owner')
            ->leftJoin('e.customFields', 'fields')
            ->where($qb->expr()->eq('owner.id', ':owner'))
            ->andWhere($qb->expr()->eq('e.id', ':id'))
            ->setParameter('owner', $user)
            ->setParameter('id', $contactId);

        return $qb->getQuery()->useResultCache(!$skipCache, null)->getOneOrNullResult($hydrate ? Query::HYDRATE_ARRAY : null);
    }

    public function getUserContacts(UserEntity $user, $type = null, $search = null, $hydrate = false, $skipCache = false) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e, owner, fields')
            ->from($this->getRepositoryName(), 'e')
            ->innerJoin('e.owner', 'owner')
            ->leftJoin('e.customFields', 'fields')
            ->where($qb->expr()->eq('owner.id', ':owner'))
            ->setParameter('owner', $user);
        if ($type) {
            $qb->andWhere($qb->expr()->eq('e.type', ':type'))
                ->setParameter('type', (int)$type);
        }
        if ($search) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('LOWER(e.firstName)', ':s'),
                    $qb->expr()->like('LOWER(e.lastName)', ':s'),
                    $qb->expr()->like('LOWER(e.email)', ':s')
                )
            )->setParameter('s', '%' . strtolower($search) . '%');
        }

        return $qb->getQuery()->useResultCache(!$skipCache, null)->getResult($hydrate ? Query::HYDRATE_ARRAY : null);
    }

    public function createContact($data, UserEntity $owner) {
        $entity = new ContactEntity();
        $entity->populate($data);
        // set contact owner
        $entity->setOwner($owner);

        // set related user
        $userDAO = new UserDAO($this->getEntityManager());
        $user = $userDAO->findByEmail($entity->getEmail());
        if ($user) {
            $entity->setUser($user);
        }

        $this->save($entity);

        // set custom fields
        $customFields = [];
        if (!empty($data['customFields'])) {
            foreach ($data['customFields'] as $fieldData) {
                $field = new ContactCustomField();
                $field->populate($fieldData);
                $field->setContact($entity);
                $customFields[] = $field;
            }
            $entity->setCustomFields($customFields);
        }

//        $this->save($entity);

        $this->getEntityManager()->flush();

        return $entity;

    }

}