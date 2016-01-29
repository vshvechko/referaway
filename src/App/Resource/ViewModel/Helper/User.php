<?php

namespace App\Resource\ViewModel\Helper;

use App\Entity\User as UserEntity;

trait User {
    public function exportUserArray(UserEntity $user) {
        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'phone' => $user->getPhone(),
            'business' => $user->getBusiness(),
            'address' => $user->getAddress(),
            'country' => $user->getCountry(),
            'city' => $user->getCity(),
            'zip' => $user->getZip(),
        ];
    }
}