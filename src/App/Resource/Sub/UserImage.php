<?php

namespace App\Resource\Sub;


use App\DAO\UserDAO;
use App\Exception\StatusException;
use App\Resource\AbstractMediaResource;

class UserImage extends AbstractMediaResource
{
    public function post($id) {
        $user = $this->authenticateUser();
        if ($id != $user->getId()) {
            throw new StatusException('Authentication error', self::STATUS_UNAUTHORIZED);
        }

        $oldImage = $user->getImage();

        $dao = new UserDAO($this->getEntityManager());
        $fileName = $this->saveImage();
        $user->setImage($fileName);
        $dao->save($user);

        $this->deleteImage($oldImage);

        return ['url' => $this->getService()->getUrl($fileName)];
    }
    
    public function delete($id, $subId = null)
    {
        $user = $this->authenticateUser();
        if ($user->getId() != $id) {
            throw new StatusException('Not Authorized', self::STATUS_UNAUTHORIZED);
        }

        $oldImage = $user->getImage();

        $dao = new UserDAO($this->getEntityManager());
        $user->setImage(null);
        $dao->save($user);

        $this->deleteImage($oldImage);
    }
}