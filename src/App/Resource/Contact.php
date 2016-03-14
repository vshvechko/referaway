<?php

namespace App\Resource;


use App\DAO\UserDAO;
use App\Entity\Contact as ContactEntity;
use App\Exception\StatusException;
use App\Resource\ViewModel\Helper\User as UserHelper;

class Contact extends AbstractResource
{
    use UserHelper;

    /**
     * @var UserDAO
     */
    private $service;

    /**
     * @return UserDAO
     */
    protected function getService() {
        return $this->service;
    }

    /**
     * @param UserDAO $service
     */
    public function setService($service) {
        $this->service = $service;
    }


    /**
     * Get user service
     */
    public function init() {
        $this->setService(new UserDAO($this->getEntityManager()));
    }

    public function get($id) {
        $logger = $this->getServiceLocator()->get('logger');
        $logger->debug(__CLASS__ . '::get');

        $user = $this->authenticateUser();
        if ($id === null) {
            $entities = $this->getService()->getUserContacts($user);
//            $entities = $user->getContacts();
            /**
             * @var ContactEntity $user
             */
            $data = [];
            foreach ($entities as $entity) {
                $data[] = $this->exportContactArray($entity);
            }
            $data = ['contacts' => $data];
        } else {
            $entity = $this->getService()->getUserContact($user, $id);
            if ($entity === null) {
                throw new StatusException('Contact not found', self::STATUS_NOT_FOUND);
            }
            $data = ['group' => $this->exportContactArray($entity)];
        }

        return $data;
    }

}