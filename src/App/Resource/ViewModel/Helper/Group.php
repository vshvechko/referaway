<?php

namespace App\Resource\ViewModel\Helper;

use App\Entity\Contact;
use App\Entity\ContactCustomField;
use App\Entity\Group as GroupEntity;
use App\Entity\User;
use App\Entity\UserGroup;

trait Group {
    public function exportGroupArray(GroupEntity $entity, User $user = null) {
        $owner = [
            'id' => $entity->getOwner()->getId(),
            'email' => $entity->getOwner()->getEmail(),
            'firstName' => $entity->getOwner()->getFirstName(),
            'lastName' => $entity->getOwner()->getLastName(),
            'business' => $entity->getOwner()->getBusiness(),
            'phone' => $entity->getOwner()->getPhone(),
        ];
        $members = [];
        /**
         * @var Contact $member
         * @var UserGroup $userGroup
         */
        foreach ($entity->getUserGroups() as $userGroup) {
            $member = $userGroup->getContact();
            $customFields = [];
            /**
             * @var ContactCustomField $field
             */
            foreach ($member->getCustomFields() as $field) {
                $customFields[] = [
                    'type' => $field->getType(),
                    'value' => $field->getValue()
                ];
            }
            $members[] = [
                'id' => $member->getId(),
                'firstName' => $member->getFirstName(),
                'lastName' => $member->getLastName(),
                'type' => $member->getType(),
                'business' => $member->getBusiness(),
                'customFields' => $customFields,
                'isAdmin' => $userGroup->isAdmin(),
                'memberStatus' => $userGroup->getMemberStatus()
            ];
        }

        $result = [
            'id' => $entity->getId(),
            'name' => $entity->getName(),
            'visibility' => $entity->getVisibility(),
            'owner' => $owner,
            'contacts' => $members,
        ];
        if ($user) {
            $result['isAdmin'] = $entity->canAdmin($user);
        }

        return $result;
    }

    public function exportGroupShortArray(GroupEntity $entity, User $user = null) {
        $owner = [
            'id' => $entity->getOwner()->getId(),
        ];
        $result = [
            'id' => $entity->getId(),
            'name' => $entity->getName(),
            'visibility' => $entity->getVisibility(),
            'owner' => $owner,
        ];
        if ($user) {
            $result['memberStatus'] = $entity->getMemberStatus($user);
            $member = $entity->findMemberByUser($user);
            if ($member) {
                $result['inviteId'] = $member->getId();
            }
        }
        return $result;
    }
}