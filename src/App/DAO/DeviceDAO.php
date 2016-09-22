<?php

namespace App\DAO;


class DeviceDAO extends AbstractDAO
{

    protected function getRepositoryName()
    {
        return 'App\Entity\Device';
    }
}