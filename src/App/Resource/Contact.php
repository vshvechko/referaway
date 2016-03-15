<?php

namespace App\Resource;


use App\DAO\ContactDAO;
use App\Entity\Contact as ContactEntity;
use App\Entity\ContactCustomField;
use App\Exception\StatusException;
use App\Resource\ViewModel\Helper\User as UserHelper;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Validator as v;

class Contact extends AbstractResource
{
    use UserHelper;

    /**
     * @var ContactDAO
     */
    private $service;

    /**
     * @return ContactDAO
     */
    protected function getService() {
        return $this->service;
    }

    /**
     * @param ContactDAO $service
     */
    public function setService($service) {
        $this->service = $service;
    }


    /**
     * Get user service
     */
    public function init() {
        $this->setService(new ContactDAO($this->getEntityManager()));
    }

    public function get($id) {
        $logger = $this->getServiceLocator()->get('logger');
        $logger->debug(__METHOD__);

        $user = $this->authenticateUser();
        if ($id === null) {
            $request = $this->getRequest();

            $params = $request->getQueryParams();
            $type = !empty($params['type']) ? $params['type'] : null;
            $s = !empty($params['s']) ? $params['s'] : null;

            $entities = $this->getService()->getUserContacts($user, $type, $s);
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
            $data = ['contact' => $this->exportContactArray($entity)];
        }

        return $data;
    }

    public function post() {
        $logger = $this->getServiceLocator()->get('logger');
        $logger->debug(__METHOD__);

        $user = $this->authenticateUser();

        $data = $this->getRequest()->getParsedBody();

        try {
            $emailValidator = v::email()->length(null, 32);
            $phoneValidator = v::phone()->length(null, 32);
            $this->addValidator('email', $emailValidator->setName('email'));
            $this->addValidator('phone', $phoneValidator->setName('phone'));
            $this->addValidator('firstName', v::notEmpty()->length(1, 32)->setName('firstName'));
            $this->addValidator('lastName', v::notEmpty()->length(1, 32)->setName('lastName'));
            $this->addValidator('business', v::optional(v::length(null, 32))->setName('business'));
            $this->addValidator('type', v::in([ContactEntity::TYPE_VENDOR, ContactEntity::TYPE_CUSTOMER])->setName('type'));
            $this->validateArray($data);

            if (!empty($data['customFields']) && is_array($data['customFields'])) {
                $this->clearValidators();
                $this->addValidator('type', v::in([ContactCustomField::TYPE_EMAIL, ContactCustomField::TYPE_PHONE])->setName('custom field type'));
                $emailValidator->setName('custom email');
                $phoneValidator->setName('custom phone');
                foreach ($data['customFields'] as $fieldData) {
                    if (!empty($fieldData['type'])) {
                        switch ($fieldData['type']) {
                            case ContactCustomField::TYPE_EMAIL:
                                $this->addValidator('value', $emailValidator);
                                break;
                            case ContactCustomField::TYPE_PHONE:
                                $this->addValidator('value', $phoneValidator);
                                break;
                        }
                    }
                    $this->validateArray($fieldData);
                }
            }

            $entity = $this->getService()->createContact($data, $user);

            return ['contact' => $this->exportContactArray($entity)];
        } catch (ValidationException $e) {
            throw new StatusException($e->getMainMessage(), self::STATUS_BAD_REQUEST);
        } catch (\InvalidArgumentException $e) {
            throw new StatusException($e->getMessage(), self::STATUS_BAD_REQUEST);
        }
    }

}