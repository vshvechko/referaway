<?php

namespace App\Resource;


use App\DAO\ContactDAO;
use App\DAO\ReferralDAO;
use App\Entity\ReferralCustomField;
use App\Entity\ReferralImage;
use App\Exception\StatusException;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Respect\Validation\Exceptions\ValidationException;
use Slim\Http\Response;
use App\Entity\Referral as ReferralEntity;
use Respect\Validation\Validator as v;
use App\Resource\ViewModel\Helper\Referral as ReferralHelper;

class Referral extends AbstractResource
{
    const REQUEST_TARGET = 'contactId';
    const REQUEST_CUSTOM_FIELDS = 'customFields';
    const REQUEST_FILTER = 'filter';
    const REQUEST_SEARCH = 'search';
    const FILTER_SENT = 'sent';
    const FILTER_RECEIVED = 'received';

    /**
     * @var ReferralDAO
     */
    private $service;
    /**
     * @var ContactDAO
     */
    private $contactService;

    public function __construct(ServerRequestInterface $request, Response $response, ContainerInterface $di)
    {
        parent::__construct($request, $response, $di);
        $this->setService(new ReferralDAO($this->getEntityManager()));
        $this->setContactService(new ContactDAO($this->getEntityManager()));
    }

    /**
     * @return ReferralDAO
     */
    protected function getService() {
        return $this->service;
    }

    /**
     * @param ReferralDAO $service
     */
    public function setService($service) {
        $this->service = $service;
    }

    public function getContactService() {
        return $this->contactService;
    }

    public function setContactService($service) {
        $this->contactService = $service;
    }

    public function get($id, $subId = null) {
        $user = $this->authenticateUser();
        
        if ($id) {
            $referral = $this->getService()->findById($id);
            if (is_null($referral)) {
                throw new StatusException('Not found', self::STATUS_NOT_FOUND);
            }
            return ['referral' => (new ReferralHelper())->exportArray($referral, $this->getServiceLocator()->get('imageService'))];

        } else {
            $params = $this->getRequest()->getQueryParams();
            $search = !empty($params[self::REQUEST_SEARCH]) ? $params[self::REQUEST_SEARCH] : null;
            $filter = !empty($params[self::REQUEST_FILTER]) ? $params[self::REQUEST_FILTER] : null;

            switch ($filter) {
                case self::FILTER_SENT:
                    $referrals = $this->getService()->findSentReferralsByUser($user, $search);
                    break;
                case self::FILTER_RECEIVED:
                    $referrals = $this->getService()->findReceivedReferralsByUser($user, $search);
                    break;
                default:
                    $referrals = $this->getService()->findReferralsByUser($user, $search);
                    break;
            }

            /**
             * @var ReferralEntity $referral
             */
            $data = [];
            foreach ($referrals as $referral) {
                $data[] = (new ReferralHelper())->exportArray($referral, $this->getServiceLocator()->get('imageService'));
            }
            return ['referrals' => $data];
        }
        
    }

    public function post($id = null) {
        $user = $this->authenticateUser();
        $data = $this->getRequest()->getParsedBody();

        try {
            $addressValidator = v::notEmpty()->length(null, 255);
            $emailValidator = v::email()->length(null, 32);
            $phoneValidator = v::phone()->length(null, 32);

            $this->addValidator('name', v::notEmpty()->length(1, 255)->setName('Name'));
            $this->addValidator(
                'type',
                v::in([ReferralEntity::TYPE_CUSTOMER, ReferralEntity::TYPE_VENDOR, ReferralEntity::TYPE_SELF])
                    ->setName('Type')
            );
            $this->addValidator(self::REQUEST_CUSTOM_FIELDS, v::optional(v::arrayType())->setName(self::REQUEST_CUSTOM_FIELDS));
            if ($data['type'] != ReferralEntity::TYPE_SELF) {
                $this->addValidator(self::REQUEST_TARGET, v::notEmpty()->setName(self::REQUEST_TARGET));
            }
            $this->validateArray($data);

            $target = null;
            if ($data['type'] != ReferralEntity::TYPE_SELF) {
                $target = $this->getContactService()->findById($data[self::REQUEST_TARGET]);
                if (!$target || $target->getOwner() != $user) {
                    throw new StatusException('Target not found', self::STATUS_NOT_FOUND);
                }
            }

            if (!empty($data[self::REQUEST_CUSTOM_FIELDS]) && is_array($data[self::REQUEST_CUSTOM_FIELDS])) {
                $this->clearValidators();
                $this->addValidator(
                    'type',
                    v::in([ReferralCustomField::TYPE_EMAIL, ReferralCustomField::TYPE_PHONE, ReferralCustomField::TYPE_ADDRESS])
                        ->setName('custom field type')
                );
                $emailValidator->setName('custom email');
                $phoneValidator->setName('custom phone');
                $addressValidator->setName('custom address');
                foreach ($data['customFields'] as $fieldData) {
                    if (!empty($fieldData['type'])) {
                        switch ($fieldData['type']) {
                            case ReferralCustomField::TYPE_ADDRESS:
                                $this->addValidator('value', $addressValidator);
                                break;
                            case ReferralCustomField::TYPE_EMAIL:
                                $this->addValidator('value', $emailValidator);
                                break;
                            case ReferralCustomField::TYPE_PHONE:
                                $this->addValidator('value', $phoneValidator);
                                break;
                        }
                    }
                    $this->validateArray($fieldData);
                }
            }
            
            $entity = $this->getService()->createReferral($data, $user, $target, $data[self::REQUEST_CUSTOM_FIELDS]);

            return ['referral' => (new ReferralHelper())->exportArray($entity, $this->getServiceLocator()->get('imageService'))];
        } catch (ValidationException $e) {
            throw new StatusException($e->getMainMessage(), self::STATUS_BAD_REQUEST);
        } catch (\InvalidArgumentException $e) {
            throw new StatusException($e->getMessage(), self::STATUS_BAD_REQUEST);
        }
    }

    public function delete($id, $subId = null) {
        $user = $this->authenticateUser();
        /**
         * @var ReferralEntity $referral
         */
        $referral = $this->getService()->findById($id);
        if (is_null($referral)) {
            throw new StatusException('Not found', self::STATUS_NOT_FOUND);
        }
        if (!$referral->canAdmin($user)) {
            throw new StatusException('Permission violated', self::STATUS_UNAUTHORIZED);
        }

        $images = [];
        /**
         * @var ReferralImage $image
         */
        foreach ($referral->getImages() as $image) {
            $images[] = $image->getImage();
        }
        $this->getService()->remove($referral);

        if (!empty($images)) {
            $imgService = $this->getServiceLocator()->get('imageService');
            foreach ($images as $fileName) {
                $imgService->delete($fileName);
            }
        }
    }

}