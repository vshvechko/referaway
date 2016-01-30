<?php

namespace App\DAO;


use App\Entity\Group as GroupEntity;
use App\Entity\User;

class GroupDAO extends AbstractDAO {

    protected function getRepositoryName() {
        return 'App\Entity\Group';
    }

    /**
     * @param $data
     * @return GroupEntity
     */
    public function createGroup($data,User $user = null) {
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

        if ($entity === null) {
            return null;
        }

        $entity->populate($data);
        $entity->setUpdated(new \DateTime());

        $this->save($entity);

        return $entity;
    }

    public function deleteGroup($id) {
        /**
         * @var GroupEntity $entity
         */
        $entity = $this->findById($id);

        if ($entity === null) {
            return false;
        }

        $this->remove($entity);

        return true;
    }

}