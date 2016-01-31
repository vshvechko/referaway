<?php

namespace App\Resource\ViewModel\Helper;

use App\Entity\Group as GroupEntity;

trait Group {
    public function exportGroupArray(GroupEntity $entity) {
        $owner = ['id' => $entity->getOwner()->getId(), 'email' => $entity->getOwner()->getEmail()];
        $members = [];
        foreach ($entity->getMembers() as $member) {
            $members[] = ['id' => $member->getId(), 'email' => $member->getEmail()];
        }

        return [
            'id' => $entity->getId(),
            'name' => $entity->getName(),
            'owner' => $owner,
            'members' => $members,
        ];
    }
}