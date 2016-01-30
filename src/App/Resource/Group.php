<?php

namespace App\Resource;


use App\DAO\GroupDAO;
use App\Exception\StatusException;
use App\Resource\ViewModel\Helper\Group as GroupHelper;

class Group extends AbstractResource {
    use GroupHelper;
    /**
     * @var GroupDAO
     */
    private $service;

    /**
     * @return GroupDAO
     */
    protected function getService() {
        return $this->service;
    }

    /**
     * @param GroupDAO $service
     */
    public function setService($service) {
        $this->service = $service;
    }


    /**
     * Get user service
     */
    public function init() {
        $this->setService(new GroupDAO($this->getEntityManager()));
    }

    public function get($id) {
        if ($id === null) {
            $entities = $this->getService()->findAll();
            /**
             * @var \App\Entity\Group $user
             */
            $data = [];
            foreach ($entities as $entity) {
                $data[] = $this->exportGroupArray($entity);
            }
            $data = ['groups' => $data];
        } else {
            $entity = $this->getService()->findById($id);
            if ($entity === null) {
                throw new StatusException('Group not found', self::STATUS_NOT_FOUND);
            }
            $data = ['group' => $this->exportGroupArray($entity)];
        }

        return $data;
    }

    public function post() {
        $user = $this->authenticateUser();
        $data = $this->getRequest()->getParsedBody();

        try {
            $entity = $this->getService()->createGroup($data, $user);

            return ['group' => $this->exportGroupArray($entity)];
        } catch (\InvalidArgumentException $e) {
            throw new StatusException($e->getMessage(), self::STATUS_BAD_REQUEST);
        }
    }

}