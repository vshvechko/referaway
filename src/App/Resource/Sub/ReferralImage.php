<?php

namespace App\Resource\Sub;


use App\DAO\ReferralDAO;
use App\Entity\Referral as ReferralEntity;
use App\Entity\ReferralImage as ReferralImageEntity;
use App\Exception\StatusException;
use App\Resource\AbstractMediaResource;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Response;

class ReferralImage extends AbstractMediaResource
{
    /**
     * @var ReferralDAO
     */
    protected $dao;

    public function __construct(ServerRequestInterface $request, Response $response, ContainerInterface $di)
    {
        parent::__construct($request, $response, $di);
        $this->dao = new ReferralDAO($this->getEntityManager());
    }

    /**
     * @return ReferralDAO
     */
    public function getDao()
    {
        return $this->dao;
    }

    /**
     * @param ReferralDAO $service
     */
    public function setDao($service)
    {
        $this->dao = $service;
    }

    public function post($id) {
        $user = $this->authenticateUser();
        /**
         * @var ReferralEntity $referral
         */
        $referral = null;
        if ($id) {
            $referral = $this->getDao()->findById($id);
        }
        if (is_null($referral)) {
            throw new StatusException('Referral not found', self::STATUS_NOT_FOUND);
        }
        if ($referral->getOwner() != $user) {
            throw new StatusException('Permission violated', self::STATUS_UNAUTHORIZED);
        }

        $fileName = $this->saveImage();
        $img = new ReferralImageEntity();
        $img->setImage($fileName);
        $referral->addImage($img);
        $this->getDao()->save($referral);

        return ['image' => ['id' => $img->getId(), 'url' => $this->getService()->getUrl($img->getImage())]];
    }

    public function put($id, $subId = null) {
        $user = $this->authenticateUser();
        /**
         * @var ReferralEntity $referral
         */
        $referral = null;
        if ($id) {
            $referral = $this->getDao()->findById($id);
        }
        if (is_null($referral)) {
            throw new StatusException('Referral not found', self::STATUS_NOT_FOUND);
        }
        if ($referral->getOwner() != $user) {
            throw new StatusException('Permission violated', self::STATUS_UNAUTHORIZED);
        }
        $img = null;
        if ($subId) {
            $img = $this->getDao()->findImageById($subId);
        }
        if (is_null($img)) {
            throw new StatusException('Not found', self::STATUS_NOT_FOUND);
        }

        $fileName = $this->saveImage();

        $oldImage = $img->getImage();

        $img->setImage($fileName);
        $this->getDao()->save($img);
        $this->deleteImage($oldImage);

        return ['image' => ['id' => $img->getId(), 'url' => $this->getService()->getUrl($img->getImage())]];
    }

    public function delete($id, $subId = null)
    {
        $user = $this->authenticateUser();
        /**
         * @var ReferralEntity $referral
         */
        $referral = null;
        if ($id) {
            $referral = $this->getDao()->findById($id);
        }
        if (is_null($referral)) {
            throw new StatusException('Referral not found', self::STATUS_NOT_FOUND);
        }
        if ($referral->getOwner() != $user) {
            throw new StatusException('Permission violated', self::STATUS_UNAUTHORIZED);
        }

        /**
         * @var ReferralImageEntity $img
         */
        $img = null;
        if ($subId) {
            $img = $this->getDao()->findImageById($subId);
        }
        if (is_null($img)) {
            throw new StatusException('Not found', self::STATUS_NOT_FOUND);
        }

        $this->deleteImage($img->getImage());

        $this->getDao()->remove($img);
    }


}