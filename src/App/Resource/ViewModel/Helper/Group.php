<?php

namespace App\Resource\ViewModel\Helper;

use App\Entity\Contact;
use App\Entity\ContactCustomField;
use App\Entity\Group as GroupEntity;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Manager\ResourceManagerInterface;

trait Group {
    public function exportGroupArray(GroupEntity $entity, ResourceManagerInterface $imageService, User $user = null) {
        $owner = [
            'id' => $entity->getOwner()->getId(),
            'email' => $entity->getOwner()->getEmail(),
            'firstName' => $entity->getOwner()->getFirstName(),
            'lastName' => $entity->getOwner()->getLastName(),
            'business' => $entity->getOwner()->getBusiness(),
            'phone' => $entity->getOwner()->getPhone(),
            'image' => $imageService->getUrl($entity->getOwner()->getImage()),
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
                'memberStatus' => $userGroup->getMemberStatus(),
                'image' => $imageService->getUrl($member->getImage()),
            ];
        }

        $result = [
            'id' => $entity->getId(),
            'name' => $entity->getName(),
            'visibility' => $entity->getVisibility(),
            'owner' => $owner,
            'contacts' => $members,
            'image' => $imageService->getUrl($entity->getImage()),
        ];
        if ($user) {
            $result['isAdmin'] = $entity->canAdmin($user);
        }

        return $result;
    }

    public function exportGroupShortArray(GroupEntity $entity, ResourceManagerInterface $imageService, User $user = null) {
        $owner = [
            'id' => $entity->getOwner()->getId(),
        ];
        $result = [
            'id' => $entity->getId(),
            'name' => $entity->getName(),
            'visibility' => $entity->getVisibility(),
            'owner' => $owner,
            'image' => $imageService->getUrl($entity->getImage()),
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