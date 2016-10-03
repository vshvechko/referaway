<?php

namespace App\Resource;


use App\DAO\CategoryDAO;
use App\DAO\ContactDAO;
use App\DAO\UserDAO;
use App\Entity\User as UserEntity;
use App\Exception\StatusException;
use App\Manager\SMSManager;
use App\Resource;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Validator as v;
use App\Resource\ViewModel\Helper\User as UserHelper;

class Register extends AbstractResource {
    /**
     * @var UserDAO
     */
    private $userService;
    /**
     * @var ContactDAO
     */
    private $contactService;
    /**
     * @var CategoryDAO
     */
    private $categoryService;

    /**
     * Get user service
     */
    public function init() {
        $this->setUserService(new UserDAO($this->getEntityManager()));
        $this->setContactService(new ContactDAO($this->getEntityManager()));
        $this->setCategoryService(new CategoryDAO($this->getEntityManager()));
    }

    /**
     * @return UserDAO
     */
    public function getUserService() {
        return $this->userService;
    }

    /**
     * @param UserDAO $userService
     */
    public function setUserService($userService) {
        $this->userService = $userService;
    }

    /**
     * @return ContactDAO
     */
    public function getContactService() {
        return $this->contactService;
    }

    /**
     * @param ContactDAO $contactService
     */
    public function setContactService($contactService) {
        $this->contactService = $contactService;
    }

    /**
     * @return CategoryDAO
     */
    public function getCategoryService() {
        return $this->categoryService;
    }

    /**
     * @param CategoryDAO $categoryService
     */
    public function setCategoryService($categoryService) {
        $this->categoryService = $categoryService;
    }

    public function post($id = null) {
        $data = $this->getRequest()->getParsedBody();
        if (!is_array($data)) {
            throw new StatusException('Wrong Format', self::STATUS_BAD_REQUEST);
        }


        try {

            $this->addValidator('email', v::notEmpty()->length(1, 32)->setName('email'));
            $this->addValidator('phone', v::notEmpty()->phone()->setName('phone'));
            $this->addValidator('firstName', v::notEmpty()->length(1, 32)->setName('firstName'));
            $this->addValidator('lastName', v::notEmpty()->length(1, 32)->setName('lastName'));
            $this->addValidator('business', v::optional(v::length(null, 32))->setName('business'));
            $this->addValidator('code', v::notEmpty()->setName('code'));
            $this->addValidator('state', v::optional(v::length(null, 32)->setName('state')));
            $this->addValidator('title', v::optional(v::length(null, 32)->setName('title')));
//            $this->addValidator('password', v::notEmpty()->length(5, 32)->setName('password'));

            $this->validateArray($data);

            if ($this->getUserService()->isEmailExist($data['email']))
                throw new \InvalidArgumentException('email "' . $data['email'] . '" exists already');

            /**
             * @var SMSManager $smsService
             */
            $smsService = $this->getServiceLocator()->get('smsService');
            if (!$smsService->reportVerification($data['phone'], $data['code'])) {
                throw new \InvalidArgumentException('Code is not valid');
            }

            $encoder = $this->getServiceLocator()->get('encryptionHelper');
            $pass = $encoder->generatePassword();
//            $pass = $data['password'];

            $authManager = $this->getServiceLocator()->get('authService');
            $emailManager = $this->getServiceLocator()->get('mailManager');

            $user = new UserEntity();
            $user->populate($data);
            $user->setPassword($encoder->getHash($pass));

            // category
            if (!empty($data['categoryId'])) {
                $category = $this->getCategoryService()->findById($data['categoryId']);
                if (is_null($category)) {
                    throw new StatusException('Category not found', self::STATUS_NOT_FOUND);
                }
                $user->setCategory($category);
            }

//            $code = $encoder->generateShortCode();
//            $user->setActivationCode($code)
//                ->setIsActive(UserEntity::STATUS_INACTIVE);
            $user->setIsActive(UserEntity::STATUS_ACTIVE);

            $token = $authManager->generateToken();
            $user->setToken($token);

            $this->getUserService()->save($user);

            $this->getContactService()->assignContactsToUser($user);

            try {
                $emailManager->sendPasswordEmail($user->getEmail(), $pass);
            } catch (\Exception $e) {
                $logger = $this->getServiceLocator()->get('logger');
                $logger->err(sprintf('%s in %s:%s', $e->getMessage(), $e->getFile(), $e->getLine()));
                $logger->debug($e->getTraceAsString());
            }
            return [
                'user' => (new UserHelper())->exportUserArray($user, $this->getServiceLocator()->get('imageService')),
                'accessToken' => [
                    'token' => $user->getToken(),
                    'type' => 'Bearer',
                    'expiresIn' => null
                ],
            ];
        } catch (ValidationException $e) {
            throw new StatusException($e->getMainMessage(), self::STATUS_BAD_REQUEST);
        } catch (\InvalidArgumentException $e) {
            throw new StatusException($e->getMessage(), self::STATUS_BAD_REQUEST);
        }
    }
}