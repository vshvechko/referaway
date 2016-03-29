<?php

namespace App\Manager;


interface ResourceManagerInterface
{
    /**
     * @param mixed $file
     * @param null $fileName
     * @return mixed
     */
    public function upload($file, $fileName = null);

    /**
     * @param $fileName
     * @return mixed
     */
    public function delete($fileName);

    /**
     * @param string $fileName
     * @return string
     */
    public function getUrl($fileName);
}