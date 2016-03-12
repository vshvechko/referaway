<?php

namespace App\Resource;


use App\DAO\GroupDAO;
use App\Entity\Group as GroupEntity;
use App\Entity\User;
use App\Exception\StatusException;
use App\Resource\ViewModel\Helper\Group as GroupHelper;

class Group extends AbstractResource {
    use GroupHelper;

    const REQUEST_ACTION = 'action';
    const REQUEST_DATA = 'data';

    const ACTION_UPDATE = 'update';
    const ACTION_ENTER = 'enter';
    const ACTION_EXIT = 'exit';
    const ACTION_INVITE = 'invite';
    const ACTION_REJECT = 'reject';

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
             * @var GroupEntity $user
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

    public function put($id) {
        $user = $this->authenticateUser();

        $group = $this->getService()->findById($id);
        if (is_null($group))
            throw new StatusException('Group not found', self::STATUS_NOT_FOUND);

        $data = $this->getRequest()->getParsedBody();

        $action = $data['action'];

        switch ($action) {
            case self::ACTION_UPDATE:
                return $this->doActionUpdate($user, $group, $data);
            case self::ACTION_ENTER:
                return $this->doActionEnter($user, $group, $data);
            case self::ACTION_EXIT:
                return $this->doActionExit($user, $group, $data);
            default:
                throw new StatusException('Action not supported', self::STATUS_BAD_REQUEST);
        }
    }

    /**
     * @param $user
     * @param GroupEntity $group
     * @param $data
     * @return array
     * @throws StatusException
     */
    private function doActionUpdate($user, $group, $data) {
        try {
            $service = $this->getService();

            $entityData = $data[self::REQUEST_DATA];

            if (!$group->canAdmin($user))
                throw new StatusException('Permission violated', self::STATUS_FORBIDDEN);

            $group->populate($entityData);

            $service->save($group);

            return ['group' => $this->exportGroupArray($group)];
        } catch (\InvalidArgumentException $e) {
            throw new StatusException($e->getMessage(), self::STATUS_BAD_REQUEST);
        }
    }

    /**
     * @param User $user
     * @param GroupEntity $group
     * @param $data
     * @return array
     * @throws StatusException
     */
    private function doActionEnter($user, $group, $data) {
        try {
            $this->getService()->enterToGroup($user, $group);

            return ['group' => $this->exportGroupArray($group)];
        } catch (\InvalidArgumentException $e) {
            throw new StatusException($e->getMessage(), self::STATUS_BAD_REQUEST);
        }
    }

    /**
     * @param User $user
     * @param GroupEntity $group
     * @param $data
     * @return array
     * @throws StatusException
     */
    private function doActionExit($user, $group, $data) {
        try {
            $this->getService()->exitFromGroup($user, $group);

            return ['group' => $this->exportGroupArray($group)];
        } catch (\InvalidArgumentException $e) {
            throw new StatusException($e->getMessage(), self::STATUS_BAD_REQUEST);
        }
    }

}