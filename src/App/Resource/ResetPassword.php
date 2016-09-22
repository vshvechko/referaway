<?php

namespace App\Resource;

use App\DAO\UserDAO;
use App\Exception\StatusException;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Validator as v;

class ResetPassword extends AbstractResource
{

    /**
     * @var UserDAO
     */
    private $userService;

    public function init() {
        $this->userService = new UserDAO($this->getEntityManager());
    }


    public function post($id = null)
    {
        try {
            $data = $this->getRequest()->getParsedBody();
            if (!is_array($data)) {
                $data = [];
            }

            $this->addValidator('email', v::email()->length(null, 32));
            $this->validateArray($data);

            $user = $this->userService->findByEmail($data['email']);
            if (!$user) {
                throw new StatusException('Not Found', self::STATUS_NOT_FOUND);
            }

            $pass = $this->getServiceLocator()->get('encryptionHelper')->generatePassword();
            $encoded = $this->getServiceLocator()->get('encryptionHelper')->getHash($pass);
            $user->setPassword($encoded);
            $this->userService->save($user);

            $emailManager = $this->getServiceLocator()->get('mailManager');

            try {
                $emailManager->sendPasswordEmail($user->getEmail(), $pass);
            } catch (\Exception $e) {
                $logger = $this->getServiceLocator()->get('logger');
                $logger->err(sprintf('%s in %s:%s', $e->getMessage(), $e->getFile(), $e->getLine()));
                $logger->debug($e->getTraceAsString());
            }
        } catch (ValidationException $e) {
            throw new StatusException($e->getMainMessage(), self::STATUS_BAD_REQUEST);
        }
    }
}