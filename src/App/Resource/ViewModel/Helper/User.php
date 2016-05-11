<?php

namespace App\Resource\ViewModel\Helper;

use App\Entity\User as UserEntity;
use App\Entity\Contact as ContactEntity;
use App\Entity\ContactCustomField;
use App\Manager\ResourceManagerInterface;

trait User {
    public function exportUserArray(UserEntity $user, ResourceManagerInterface $imgService) {
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
            'isActive' => $user->getIsActive(),
            'image' => $imgService->getUrl($user->getImage())
        ];
    }

    public function exportContactArray(ContactEntity $contact, ResourceManagerInterface $imgService) {
        return [
            'id' => $contact->getId(),
            'type' => $contact->getType(),
//            'email' => $contact->getEmail(),
//            'phone' => $contact->getPhone(),
            'firstName' => $contact->getFirstName(),
            'lastName' => $contact->getLastName(),
            'business' => $contact->getBusiness(),
            'note' => $contact->getNote(),
            'image' => $imgService->getUrl($contact->getImage()),
            'customFields' => array_map(
                function (ContactCustomField $field) {
                    return $this->exportCustomField($field);
                },
                $contact->getCustomFields()
            ),
        ];
    }

    protected function exportCustomField(ContactCustomField $field) {
        return [
            'type' => $field->getType(),
            'value' => $field->getValue(),
            'meta' => $field->getMeta()
        ];
    }
}