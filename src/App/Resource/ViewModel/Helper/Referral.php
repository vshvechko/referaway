<?php

namespace App\Resource\ViewModel\Helper;


use App\Entity\ReferralCustomField;
use App\Entity\ReferralImage;
use App\Manager\ResourceManagerInterface;
use App\Resource\ViewModel\Helper\User as UserHelper;

class Referral
{
    public function exportArray(\App\Entity\Referral $entity, ResourceManagerInterface $imgService) {
        return [
            'id' => $entity->getId(),
            'type' => $entity->getType(),
            'name' => $entity->getName(),
            'note' => $entity->getNote(),
            'status' => $entity->getStatus(),
            'revenue' => $entity->getRevenue(),
            'created' => $entity->getCreated()->format(\DateTime::ISO8601),
            'dateCompleted' => $entity->getDateCompleted() ? $entity->getDateCompleted()->format(\DateTime::ISO8601) : null,
            'isRead' => $entity->getIsRead(),
            'owner' => [
                'id' => $entity->getOwner()->getId(),
                'firstName' => $entity->getOwner()->getFirstName(),
                'lastName' => $entity->getOwner()->getLastName(),
            ],
            'target' => (new UserHelper())->exportContactShortArray($entity->getTarget()),
            'customFields' => array_map(
                function (ReferralCustomField $field) {
                    return $this->exportCustomField($field);
                },
                $entity->getCustomFields()
            ),
            'images' => array_map(
                function (ReferralImage $image) use ($imgService) {
                    return $this->exportImage($image, $imgService);
                },
                $entity->getImages()
            ),
        ];
    }

    protected function exportCustomField(ReferralCustomField $field) {
        return [
            'type' => $field->getType(),
            'value' => $field->getValue()
        ];
    }

    protected function exportImage(ReferralImage $image, ResourceManagerInterface $imgService) {
        return [
            'id' => $image->getId(),
            'url' => $imgService->getUrl($image->getImage()),
        ];
    }
}