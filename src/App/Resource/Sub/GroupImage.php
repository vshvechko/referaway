<?php

namespace App\Resource\Sub;


use App\DAO\GroupDAO;
use App\Exception\StatusException;
use App\Entity\Group as GroupEntity;
use App\Resource\AbstractMediaResource;

class GroupImage extends AbstractMediaResource
{
    public function post($id, $subId = null) {
        $user = $this->authenticateUser();

        $dao = new GroupDAO($this->getEntityManager());
        /**
         * @var GroupEntity $group
         */
        $group = $dao->findById($id);
        if (!$group) {
            throw new StatusException('Not found', self::STATUS_NOT_FOUND);
        }
        if (!$group->canAdmin($user)) {
            throw new StatusException('Permission violated', self::STATUS_UNAUTHORIZED);
        }
        $oldImage = $group->getImage();
        $fileName = $this->saveImage();
        $group->setImage($fileName);
        $dao->save($group);

        $this->deleteImage($oldImage);

        return ['url' => $this->getService()->getUrl($fileName)];
    }

    public function delete($id, $subId = null)
    {
        $user = $this->authenticateUser();

        $dao = new GroupDAO($this->getEntityManager());
        /**
         * @var GroupEntity $group
         */
        $group = $dao->findById($id);
        if (!$group) {
            throw new StatusException('Not found', self::STATUS_NOT_FOUND);
        }
        if (!$group->canAdmin($user)) {
            throw new StatusException('Permission violated', self::STATUS_UNAUTHORIZED);
        }
        $oldImage = $group->getImage();
        $group->setImage(null);
        $dao->save($group);

        $this->deleteImage($oldImage);
    }
}