<?php

namespace App\Manager;


class LocalImageManager extends AbstractManager implements ResourceManagerInterface
{

    protected $uploadDir;
    protected $uploadUrl;

    public function upload($file, $fileName = null)
    {
        if (is_resource($file)) {
            $imgName = $fileName ? $fileName : $this->generateImageName();
            if (imagepng($file, $this->uploadDir . DIRECTORY_SEPARATOR .$imgName)) {
                return $imgName;
            }
            throw new \Exception('Cannot save image');
        } else {
            throw new \AssertionError('Not Implemented');
        }
    }

    public function getUrl($filename)
    {
        if ($filename) {
            return $this->uploadUrl . '/' . $filename;
        }
        return null;
    }
    
    private function generateImageName() {
        return uniqid() . '.png'; 
    }

    /**
     * @param mixed $uploadDir
     * @return $this
     */
    public function setUploadDir($uploadDir)
    {
        $this->uploadDir = $uploadDir;
        return $this;
    }

    /**
     * @param mixed $uploadUrl
     * @return $this
     */
    public function setUploadUrl($uploadUrl)
    {
        $this->uploadUrl = $uploadUrl;
        return $this;
    }


    /**
     * @param $fileName
     * @return mixed
     */
    public function delete($fileName)
    {
        if ($fileName) {
            $path = $this->uploadDir . DIRECTORY_SEPARATOR . $fileName;
            if (file_exists($path)) {
                return unlink($path);
            }
        }
        return false;
    }
}