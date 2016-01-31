<?php

namespace App\Resource;


use App\DAO\UserDAO;
use App\Exception\StatusException;
use App\Resource;

class Register extends AbstractResource {
    use Resource\ViewModel\Helper\User;
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
                throw new \InvalidArgumentException('"email" missed');
            if (empty($data['password']))
                throw new \InvalidArgumentException('"password" missed');
            if (empty($data['firstName']))
                throw new \InvalidArgumentException('"firstName" missed');
            if (empty($data['phone']))
                throw new \InvalidArgumentException('"phone" missed');
            if (empty($data['lastName']))
                throw new \InvalidArgumentException('"lastName" missed');
            if ($this->getUserService()->isEmailExist($data['email']))
                throw new \InvalidArgumentException('email "' . $data['email'] . '"" exists already');

            $data['password'] = $this->getServiceLocator()->get('encryptionHelper')->getHash($data['password']);

            $user = $this->getUserService()->createUser($data);

            $encoder = $this->getServiceLocator()->get('encryptionHelper');
            $authManager = $this->getServiceLocator()->get('authService');
            $smsManager = $this->getServiceLocator()->get('smsService');

            $code = $encoder->generateShortCode();
            $user->setActivationCode($code);

            $token = $authManager->generateToken();
            $user->setToken($token);

            $this->getUserService()->save($user);
            $smsManager->sendActivationCode($user->getPhone(), $code);
            return [
                'user' => $this->exportUserArray($user),
                'accessToken' => [
                    'token' => $user->getToken(),
                    'type' => 'Bearer',
                    'expiresIn' => null
                ],
                // TODO remove code, send by sms instead
                'code' => $user->getActivationCode()
            ];
        } catch (\InvalidArgumentException $e) {
            throw new StatusException($e->getMessage(), self::STATUS_BAD_REQUEST);
        }
    }
}