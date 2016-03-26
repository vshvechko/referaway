<?php

namespace App\Resource\ViewModel\Helper;

use App\Entity\Contact;
use App\Entity\ContactCustomField;
use App\Entity\Group as GroupEntity;
use App\Entity\User;

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
         */
        foreach ($entity->getMembers() as $member) {
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
        }
        return $result;
    }
}