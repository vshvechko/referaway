<?php

namespace App\Resource\Sub;


use App\DAO\ContactDAO;
use App\Exception\StatusException;
use App\Resource\AbstractMediaResource;
use Respect\Validation\Validator as v;
use App\Entity\Contact as ContactEntity;

class ContactImage extends AbstractMediaResource
{
    public function post($id) {
        $user = $this->authenticateUser();

        $dao = new ContactDAO($this->getEntityManager());
        /**
         * @var ContactEntity $contact
         */
        $contact = $dao->findById($id);
        if (!$contact) {
            throw new StatusException('Not found', self::STATUS_NOT_FOUND);
        }
        if ($contact->getOwner() != $user) {
            throw new StatusException('Permission violated', self::STATUS_UNAUTHORIZED);
        }
        $oldImage = $contact->getImage();
        $fileName = $this->saveImage();
        $contact->setImage($fileName);
        $dao->save($contact);

        $this->deleteImage($oldImage);

        return ['url' => $this->getService()->getUrl($fileName)];
    }

    public function delete($id, $subId = null)
    {
        $user = $this->authenticateUser();

        $dao = new ContactDAO($this->getEntityManager());
        /**
         * @var ContactEntity $contact
         */
        $contact = $dao->findById($id);
        if (!$contact) {
            throw new StatusException('Not found', self::STATUS_NOT_FOUND);
        }
        if ($contact->getOwner() != $user) {
            throw new StatusException('Permission violated', self::STATUS_UNAUTHORIZED);
        }
        $oldImage = $contact->getImage();
        $contact->setImage(null);
        $dao->save($contact);

        $this->deleteImage($oldImage);
    }
}