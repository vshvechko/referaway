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

    public function get($id, $subId = null) {
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
                $data[] = (new UserHelper())->exportContactArray($entity, $this->getServiceLocator()->get('imageService'));
            }
            $data = ['contacts' => $data];
        } else {
            $entity = $this->getService()->getUserContact($user, $id);
            if ($entity === null) {
                throw new StatusException('Contact not found', self::STATUS_NOT_FOUND);
            }
            $data = ['contact' => (new UserHelper())->exportContactArray($entity, $this->getServiceLocator()->get('imageService'))];
        }

        return $data;
    }

    public function post($id = null) {
        $logger = $this->getServiceLocator()->get('logger');
        $logger->debug(__METHOD__);

        $user = $this->authenticateUser();

        $data = $this->getRequest()->getParsedBody();

        try {
            $emailValidator = v::email()->length(null, 32);
            $phoneValidator = v::phone()->length(null, 32);
            $addressValidator = v::notEmpty()->length(null, 255);
//            $this->addValidator('email', $emailValidator->setName('email'));
//            $this->addValidator('phone', $phoneValidator->setName('phone'));
            $this->addValidator('firstName', v::notEmpty()->length(1, 32)->setName('firstName'));
            $this->addValidator('lastName', v::notEmpty()->length(1, 32)->setName('lastName'));
            $this->addValidator('business', v::optional(v::length(null, 32))->setName('business'));
            $this->addValidator(
                'type',
                v::in([ContactEntity::TYPE_VENDOR, ContactEntity::TYPE_CUSTOMER])
                    ->setName('type')
            );
            $this->validateArray($data);

            if (!empty($data['customFields']) && is_array($data['customFields'])) {
                $this->clearValidators();
                $this->addValidator(
                    'type',
                    v::in([ContactCustomField::TYPE_EMAIL, ContactCustomField::TYPE_PHONE, ContactCustomField::TYPE_ADDRESS])
                        ->setName('custom field type')
                );
                $emailValidator->setName('custom email');
                $phoneValidator->setName('custom phone');
                $this->addValidator('meta', v::optional(v::length(null, 32))->setName('meta'));
                foreach ($data['customFields'] as $fieldData) {
                    if (!empty($fieldData['type'])) {
                        switch ($fieldData['type']) {
                            case ContactCustomField::TYPE_EMAIL:
                                $this->addValidator('value', $emailValidator);
                                break;
                            case ContactCustomField::TYPE_ADDRESS:
                                $this->addValidator('value', $addressValidator);
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

            return ['contact' => (new UserHelper())->exportContactArray($entity, $this->getServiceLocator()->get('imageService'))];
        } catch (ValidationException $e) {
            throw new StatusException($e->getMainMessage(), self::STATUS_BAD_REQUEST);
        } catch (\InvalidArgumentException $e) {
            throw new StatusException($e->getMessage(), self::STATUS_BAD_REQUEST);
        }
    }

    public function put($id) {
        $logger = $this->getServiceLocator()->get('logger');
        $logger->debug(__METHOD__);

        $user = $this->authenticateUser();

        $contact = $this->getService()->findById($id);
        if (!$contact) {
            throw new StatusException('Not found', self::STATUS_NOT_FOUND);
        }

        $data = $this->getRequest()->getParsedBody();

        try {
            $emailValidator = v::email()->length(null, 32);
            $phoneValidator = v::phone()->length(null, 32);
            $addressValidator = v::notEmpty()->length(null, 255);
//            $this->addValidator('email', $emailValidator->setName('email'));
//            $this->addValidator('phone', $phoneValidator->setName('phone'));
            $this->addValidator('firstName', v::notEmpty()->length(1, 32)->setName('firstName'));
            $this->addValidator('lastName', v::notEmpty()->length(1, 32)->setName('lastName'));
            $this->addValidator('business', v::optional(v::length(null, 32))->setName('business'));
            $this->addValidator(
                'type',
                v::in([ContactEntity::TYPE_VENDOR, ContactEntity::TYPE_CUSTOMER])
                    ->setName('type')
            );
            $this->validateArray($data);

            if (!empty($data['customFields']) && is_array($data['customFields'])) {
                $this->clearValidators();
                $this->addValidator(
                    'type',
                    v::in([ContactCustomField::TYPE_EMAIL, ContactCustomField::TYPE_PHONE, ContactCustomField::TYPE_ADDRESS])
                        ->setName('custom field type')
                );
                $this->addValidator('meta', v::optional(v::length(null, 32))->setName('meta'));
                $emailValidator->setName('custom email');
                $phoneValidator->setName('custom phone');
                foreach ($data['customFields'] as $fieldData) {
                    if (!empty($fieldData['type'])) {
                        switch ($fieldData['type']) {
                            case ContactCustomField::TYPE_EMAIL:
                                $this->addValidator('value', $emailValidator);
                                break;
                            case ContactCustomField::TYPE_ADDRESS:
                                $this->addValidator('value', $addressValidator);
                                break;
                            case ContactCustomField::TYPE_PHONE:
                                $this->addValidator('value', $phoneValidator);
                                break;
                        }
                    }
                    $this->validateArray($fieldData);
                }
            }

            $entity = $this->getService()->updateContact($contact, $data, $user);

            return ['contact' => (new UserHelper())->exportContactArray($entity, $this->getServiceLocator()->get('imageService'))];
        } catch (ValidationException $e) {
            throw new StatusException($e->getMainMessage(), self::STATUS_BAD_REQUEST);
        } catch (\InvalidArgumentException $e) {
            throw new StatusException($e->getMessage(), self::STATUS_BAD_REQUEST);
        }
    }

    public function delete($id, $subId = null) {
        $logger = $this->getServiceLocator()->get('logger');
        $logger->debug(__METHOD__);

        $user = $this->authenticateUser();
        /**
         * @var ContactEntity $contact
         */
        $contact = $this->service->findById($id);
        if (!$contact) {
            throw new StatusException('Contact not found', self::STATUS_NOT_FOUND);
        }
        if ($contact->getOwner()->getId() != $user->getId()) {
            throw new StatusException('Permission violated', self::STATUS_UNAUTHORIZED);
        }

        $imgService = $this->getServiceLocator()->get('imageService');
        $oldImage = $contact->getImage();
        
        $this->getService()->removeContact($contact, $imgService);
        
        if ($oldImage) {
            $imgService->delete($oldImage);
        }
    }

}