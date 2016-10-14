<?php

namespace App\Resource;

use App\DAO\CategoryDAO;
use App\DAO\ContactDAO;
use App\Entity\User as UserEntity;
use App\Exception\StatusException;
use App\Resource;
use App\DAO\UserDAO;
use App\Resource\ViewModel\Helper\User as UserHelper;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Validator as v;

class User extends AbstractResource {
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
        $this->setService(new UserDAO($this->getEntityManager()));
        $this->setContactService(new ContactDAO($this->getEntityManager()));
        $this->setCategoryService(new CategoryDAO($this->getEntityManager()));
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
     * Update user
     * @param $id
     * @return array|mixed
     * @throws StatusException
     */
    public function put($id, $subId = null) {
        $user = $this->authenticateUser();
        if ($id != $user->getId()) {
            throw new StatusException('Authentication error', self::STATUS_UNAUTHORIZED);
        }

        $data = $this->getRequest()->getParsedBody();
        if (!is_array($data)) {
            throw new StatusException('Wrong Format', self::STATUS_BAD_REQUEST);
        }


        try {
            $this->addValidator('email', v::optional(v::notEmpty()->length(1, 32)->setName('email')));
            $this->addValidator('phone', v::optional(v::notEmpty()->phone()->setName('phone')));
            $this->addValidator('firstName', v::optional(v::notEmpty()->length(1, 32)->setName('firstName')));
            $this->addValidator('lastName', v::optional(v::notEmpty()->length(1, 32)->setName('lastName')));
            $this->addValidator('business', v::optional(v::length(null, 32))->setName('business'));
            $this->addValidator('password', v::optional(v::length(5, 32))->setName('password'));
            $this->addValidator('address', v::optional(v::length(null, 255))->setName('address'));
            $this->addValidator('city', v::optional(v::length(null, 32))->setName('city'));
            $this->addValidator('country', v::optional(v::length(null, 32))->setName('country'));
            $this->addValidator('zip', v::optional(v::length(5, 6))->setName('zip'));
            $this->addValidator('state', v::optional(v::length(null, 32)->setName('state')));
            $this->addValidator('title', v::optional(v::length(null, 32)->setName('title')));

            $this->validateArray($data);

            if (!empty($data['email'])) {
                if ($this->getService()->isEmailExist($data['email'], $user->getId()))
                    throw new \InvalidArgumentException('email "' . $data['email'] . '" exists already');
            }
            if (!empty($data['phone'])) {
                if ($this->getService()->isPhoneExist($data['phone'], $user->getId()))
                    throw new \InvalidArgumentException('phone "' . $data['phone'] . '" exists already');
            }
            if (!empty($data['password'])) {
                $user->setPassword($this->getServiceLocator()->get('encryptionHelper')->getHash($data['password']));
            }

            if (array_key_exists('categoryId', $data)) {
                $category = null;
                if ($data['categoryId']) {
                    $category = $this->getCategoryService()->findById($data['categoryId']);
                    if (is_null($category)) {
                        throw new StatusException('Category not found', self::STATUS_NOT_FOUND);
                    }
                }
                $user->setCategory($category);
            }

            $user->populate($data);

            $this->getService()->save($user);

            $this->getContactService()->assignContactsToUser($user);

            return [
                'user' => (new UserHelper())->exportUserArray($user, $this->getServiceLocator()->get('imageService')),
            ];
        } catch (ValidationException $e) {
            throw new StatusException($e->getMainMessage(), self::STATUS_BAD_REQUEST);
        } catch (\InvalidArgumentException $e) {
            throw new StatusException($e->getMessage(), self::STATUS_BAD_REQUEST);
        }
    }

    /**
     * @return UserDAO
     */
    public function getService() {
        return $this->userService;
    }

    /**
     * @param UserDAO $userService
     */
    public function setService($userService) {
        $this->userService = $userService;
    }

}