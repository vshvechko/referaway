<?php

namespace App\Resource;


use App\Manager\ResourceManagerInterface;

abstract class AbstractMediaResource extends AbstractResource
{
    const REQUEST_ID = 'id';

    /**
     * @var ResourceManagerInterface
     */
    protected $service;

    /**
     * @return ResourceManagerInterface
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @param ResourceManagerInterface $service
     */
    public function setService($service)
    {
        $this->service = $service;
    }

    public function init()
    {
        $this->setService($this->getServiceLocator()->get('imageService'));
    }

    protected function saveImage() {
        $fileContent = $this->getRequest()->getBody()->getContents();
        $image = imagecreatefromstring($fileContent);
        if ($image === false) {
            throw new \InvalidArgumentException('Cannot read image', self::STATUS_BAD_REQUEST);
        }
        $fileName = $this->getService()->upload($image);
        return $fileName;
    }

    protected function deleteImage($fileName) {
        $this->getService()->delete($fileName);
    }

}