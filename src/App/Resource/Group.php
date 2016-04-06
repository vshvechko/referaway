<?php

namespace App\Resource;


use App\DAO\ContactDAO;
use App\DAO\GroupDAO;
use App\Entity\Group as GroupEntity;
use App\Entity\Contact as ContactEntity;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Exception\StatusException;
use App\Resource\ViewModel\Helper\Group as GroupHelper;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Validator as v;

class Group extends AbstractResource {
    use GroupHelper;

    const REQUEST_ACTION = 'action';
    const REQUEST_DATA = 'data';
    const REQUEST_ALL = 'all';
    const REQUEST_SEARCH = 's';
    const REQUEST_CONTACTS = 'contacts';
    const REQUEST_USERGROUP_ID = 'inviteId';

    const ACTION_UPDATE = 'update';
    const ACTION_INVITE = 'invite';
    const ACTION_ACCEPT_INVITE = 'accept_invite';
    const ACTION_REMOVE_CONTACT = 'remove_contact';
    const ACTION_REJECT_INVITE = 'reject_invite';

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

    public function get($id, $subId = null) {
        $user = $this->authenticateUser();
        if ($id === null) {
            $params = $this->getRequest()->getQueryParams();
            $search = !empty($params[self::REQUEST_SEARCH]) ? $params[self::REQUEST_SEARCH] : null;

            $entities = $this->getService()->findGroupsByUser($user, $search);
            /**
             * @var GroupEntity $entity
             */
            $data = [];
            foreach ($entities as $entity) {
                $data[] = $this->exportGroupShortArray($entity, $this->getServiceLocator()->get('imageService'), $user);
            }
            $data = ['groups' => $data];
        } else {
            $entity = $this->getService()->getGroupMembers($id);
            if ($entity === null) {
                throw new StatusException('Group not found', self::STATUS_NOT_FOUND);
            }
            $data = ['group' => $this->exportGroupArray($entity, $this->getServiceLocator()->get('imageService'), $user)];
        }

        return $data;
    }

    public function post($id = null) {
        $user = $this->authenticateUser();
        $data = $this->getRequest()->getParsedBody();

        try {
            $this->addValidator('name', v::notEmpty()->length(1, 255)->setName('Group Name'));
            $this->addValidator('visibility', v::in([
                GroupEntity::VISIBILITY_PUBLIC, GroupEntity::VISIBILITY_CLOSED, GroupEntity::VISIBILITY_PRIVATE
            ])->setName('visibility'));
            $this->validateArray($data);

            $entity = $this->getService()->createGroup($data, $user);

            if (array_key_exists(self::REQUEST_CONTACTS, $data)) {
                $contactDao = new ContactDAO($this->getEntityManager());
                foreach ($data[self::REQUEST_CONTACTS] as $contactId) {
                    /**
                     * @var ContactEntity $contact
                     */
                    $contact = $contactDao->findById($contactId);
                    if ($contact && $contact->getOwner() == $user) {
                        $this->addContactToGroup($entity, $contact);
                    }
                }
            }

            return ['group' => $this->exportGroupArray($entity, $this->getServiceLocator()->get('imageService'), $user)];
        } catch (ValidationException $e) {
            throw new StatusException($e->getMainMessage(), self::STATUS_BAD_REQUEST);
        } catch (\InvalidArgumentException $e) {
            throw new StatusException($e->getMessage(), self::STATUS_BAD_REQUEST);
        }
    }

    public function put($id, $subId = null) {
        $user = $this->authenticateUser();

        $group = $this->getService()->findById($id);
        if (is_null($group))
            throw new StatusException('Group not found', self::STATUS_NOT_FOUND);

        $data = $this->getRequest()->getParsedBody();

        try {
            $this->addValidator(self::REQUEST_ACTION, v::notEmpty());
            $this->addValidator(self::REQUEST_DATA, v::arrayType());
            $this->validateArray($data);
            $this->clearValidators();
        } catch (ValidationException $e) {
            throw new StatusException($e->getMainMessage(), self::STATUS_BAD_REQUEST);
        }

        $action = $data[self::REQUEST_ACTION];

        switch ($action) {
            case self::ACTION_UPDATE:
                return $this->doActionUpdate($user, $group, $data);
            case self::ACTION_INVITE:
                return $this->doActionInvite($user, $group, $data);
            case self::ACTION_REMOVE_CONTACT:
                return $this->doActionRemoveContact($user, $group, $data);
            case self::ACTION_ACCEPT_INVITE:
                return $this->doActionAcceptInvite($user, $group, $data);
            case self::ACTION_REJECT_INVITE:
                return $this->doActionRejectInvite($user, $group, $data);
            default:
                throw new StatusException('Action not supported', self::STATUS_BAD_REQUEST);
        }
    }

    public function delete($id, $subId = null) {
        $user = $this->authenticateUser();
        /**
         * @var GroupEntity $group
         */
        $group = $this->getService()->findById($id);
        if (is_null($group)) {
            throw new StatusException('Group not found', self::STATUS_NOT_FOUND);
        }
        if (!$group->canAdmin($user)) {
            throw new StatusException('Permission violated', self::STATUS_UNAUTHORIZED);
        }
        $oldImage = $group->getImage();
        $this->getService()->remove($group);

        if ($oldImage) {
            $this->getServiceLocator()->get('imageService')->delete($oldImage);
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
            $entityData = $data[self::REQUEST_DATA];

            $this->addValidator('name', v::notEmpty()->length(1, 255)->setName('Group Name'));
            $this->addValidator('visibility', v::optional(v::notEmpty()->in([
                GroupEntity::VISIBILITY_PUBLIC, GroupEntity::VISIBILITY_CLOSED, GroupEntity::VISIBILITY_PRIVATE
            ])->setName('visibility')));
            $this->validateArray($entityData);

            $service = $this->getService();

            if (!$group->canAdmin($user))
                throw new StatusException('Permission violated', self::STATUS_FORBIDDEN);

            $group->populate($entityData);

            $service->save($group);

            return ['group' => $this->exportGroupArray($group, $this->getServiceLocator()->get('imageService'), $user)];
        } catch (ValidationException $e) {
            throw new StatusException($e->getMainMessage(), self::STATUS_BAD_REQUEST);
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
    private function doActionInvite($user, $group, $data) {
        if (!$group->canAdmin($user)) {
            throw new StatusException('Permission violated', self::STATUS_UNAUTHORIZED);
        }
        try {
            $inData = $data[self::REQUEST_DATA];
            if (array_key_exists(self::REQUEST_CONTACTS, $inData)) {
                $contactDao = new ContactDAO($this->getEntityManager());
                foreach ($inData[self::REQUEST_CONTACTS] as $contactId) {
                    /**
                     * @var ContactEntity $contact
                     */
                    $contact = $contactDao->findById($contactId);
                    if ($contact && $contact->getOwner() == $user) {
                        $this->addContactToGroup($group, $contact);
                    }
                }
            }

        } catch (ValidationException $e) {
            throw new StatusException($e->getMainMessage(), self::STATUS_BAD_REQUEST);
        } catch (\InvalidArgumentException $e) {
            throw new StatusException($e->getMessage(), self::STATUS_BAD_REQUEST);
        }

        return null;
    }

    /**
     * @param User $user
     * @param GroupEntity $group
     * @param $data
     * @return array
     * @throws StatusException
     */
    private function doActionAcceptInvite($user, $group, $data) {
        try {
            $inData = $data[self::REQUEST_DATA];
            $this->addValidator(self::REQUEST_USERGROUP_ID, v::notEmpty());
            $this->validateArray($inData);

            $userGroupId = $inData[self::REQUEST_USERGROUP_ID];
            $userGroup = $this->getService()->findUserGroupById($userGroupId);
            if (is_null($userGroup)) {
                throw new StatusException('Not found', self::STATUS_NOT_FOUND);
            }
            if ($userGroup->getContact()->getUser() != $user || $userGroup->getGroup() != $group) {
                throw new StatusException('Permission violated', self::STATUS_UNAUTHORIZED);
            }
            if ($userGroup->getMemberStatus() != UserGroup::MEMBER_STATUS_MEMBER) {
                $userGroup->setMemberStatus(UserGroup::MEMBER_STATUS_MEMBER);
                $this->getService()->save($userGroup, false);
                $assignedUserGroups = $this->getService()->getUserGroupsByUserAndGroup($user, $group);
                /**
                 * @var UserGroup $value
                 */
                foreach ($assignedUserGroups as $value) {
                    if ($value->getId() != $userGroup->getId()) {
                        $value->getGroup()->removeUserGroup($value);
                        $this->getService()->remove($value, false);
                    }
                }
                $this->getService()->flush();
            }

        } catch (ValidationException $e) {
            throw new StatusException($e->getMainMessage(), self::STATUS_BAD_REQUEST);
        } catch (\InvalidArgumentException $e) {
            throw new StatusException($e->getMessage(), self::STATUS_BAD_REQUEST);
        }

        return null;
    }

    /**
     * @param User $user
     * @param GroupEntity $group
     * @param $data
     * @return array
     * @throws StatusException
     */
    private function doActionRejectInvite($user, $group, $data) {
        try {
            $inData = $data[self::REQUEST_DATA];
            $this->addValidator(self::REQUEST_USERGROUP_ID, v::notEmpty());
            $this->validateArray($inData);

            $userGroupId = $inData[self::REQUEST_USERGROUP_ID];
            $userGroup = $this->getService()->findUserGroupById($userGroupId);
            if (is_null($userGroup)) {
                throw new StatusException('Not found', self::STATUS_NOT_FOUND);
            }
            if ($userGroup->getContact()->getUser() != $user || $userGroup->getGroup() != $group) {
                throw new StatusException('Permission violated', self::STATUS_UNAUTHORIZED);
            }
            $this->getService()->remove($userGroup);

        } catch (ValidationException $e) {
            throw new StatusException($e->getMainMessage(), self::STATUS_BAD_REQUEST);
        } catch (\InvalidArgumentException $e) {
            throw new StatusException($e->getMessage(), self::STATUS_BAD_REQUEST);
        }

        return null;
    }

    /**
     * @param User $user
     * @param GroupEntity $group
     * @param $data
     * @return array
     * @throws StatusException
     */
    private function doActionRemoveContact($user, $group, $data) {
        if (!$group->canAdmin($user)) {
            throw new StatusException('Permission violated', self::STATUS_UNAUTHORIZED);
        }

        try {
            $inData = $data[self::REQUEST_DATA];
            if (array_key_exists(self::REQUEST_CONTACTS, $inData)) {
                $contactDao = new ContactDAO($this->getEntityManager());
                foreach ($inData[self::REQUEST_CONTACTS] as $contactId) {
                    /**
                     * @var ContactEntity $contact
                     */
                    $contact = $contactDao->findById($contactId);
                    $this->getService()->removeContact($contact, $group);
                }
            }

        } catch (\InvalidArgumentException $e) {
            throw new StatusException($e->getMessage(), self::STATUS_BAD_REQUEST);
        }

        return ['group' => $this->exportGroupArray($group, $this->getServiceLocator()->get('imageService'))];
    }

    private function addContactToGroup(GroupEntity $group, ContactEntity $contact, $checkExists = true) {
        if ($checkExists && $this->getService()->isContactInGroup($contact, $group)) {
            return;
        }

        $userGroup = new UserGroup();
        $userGroup->setContact($contact)
            ->setGroup($group);
        $this->getService()->flush();

        return null;
    }
}