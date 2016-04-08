<?php

namespace App\Resource\ViewModel\Helper;


use App\Entity\ReferralCustomField;
use App\Entity\ReferralImage;
use App\Manager\ResourceManagerInterface;

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