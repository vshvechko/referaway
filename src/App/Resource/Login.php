<?php

namespace App\Resource;


use App\DAO\UserDAO;
use App\Exception\StatusException;
use App\Helper\EncryptionHelper;
use App\Resource\ViewModel\Helper\User;

class Login extends AbstractResource {
    use User;
    /**
     * @var UserDAO
     */
    private $userService;

    /**
     * Get user service
     */
    public function init() {
        $this->setUserService(new UserDAO($this->getEntityManager()));
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

    public function post() {
        $data = $this->getRequest()->getParsedBody();

        try {
            if (empty($data['email']))
                throw new \InvalidArgumentException('Email missed');
            if (empty($data['password']))
                throw new \InvalidArgumentException('Password missed');

            $encryptionHelper = $this->getServiceLocator()->get('encryptionHelper');
            $authManager = $this->getServiceLocator()->get('authService');

            $user = $this->getUserService()->findByEmail($data['email']);
            if ($user) {
                /**
                 * @var EncryptionHelper $encryptionHelper
                 */
                if (!$encryptionHelper->verify($data['password'], $user->getPassword())) {
                    $user = null;
                }
            }

            if (is_null($user))
                throw new StatusException('Authentication error', self::STATUS_UNAUTHORIZED);

            $token = $authManager->generateToken();
            $user->setToken($token);
            $this->getUserService()->save($user);

            return [
                'user' => $this->exportUserArray($user, $this->getServiceLocator()->get('imageService')),
                'accessToken' => [
                    'token' => $user->getToken(),
                    'type' => 'Bearer',
                    'expiresIn' => null
                ]
            ];
        } catch (\InvalidArgumentException $e) {
            throw new StatusException($e->getMessage(), self::STATUS_BAD_REQUEST);
        }
    }

}