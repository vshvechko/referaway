<?php

namespace App\Resource\Media;


use App\DAO\UserDAO;

class User extends AbstractMediaResource
{
    public function put($id) {
        $user = $this->authenticateUser();

        $oldImage = $user->getImage();

        $dao = new UserDAO($this->getEntityManager());
        $fileName = $this->saveImage();
        $user->setImage($fileName);
        $dao->save($user);

        $this->deleteImage($oldImage);

        return ['url' => $this->getService()->getUrl($fileName)];
    }
    
    public function delete($id)
    {
        $user = $this->authenticateUser();

        $oldImage = $user->getImage();

        $dao = new UserDAO($this->getEntityManager());
        $user->setImage(null);
        $dao->save($user);

        $this->deleteImage($oldImage);
    }
}