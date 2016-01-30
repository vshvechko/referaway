<?php

namespace App\Resource\ViewModel\Helper;

use App\Entity\Group as GroupEntity;

trait Group {
    public function exportGroupArray(GroupEntity $entity) {
        return [
            'id' => $entity->getId(),
            'name' => $entity->getName(),
            'owner' => ['id' => $entity->getOwner()->getId()],
        ];
    }
}