<?php

namespace App\Resource;


use App\Exception\StatusException;
use App\Manager\SMSManager;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Validator as v;

class VerifyPhone extends AbstractResource
{
    public function post($id = null)
    {
        try {
            $data = $this->getRequest()->getParsedBody();
            $this->addValidator('phone', v::phone()->length(null, 32));
            $this->validateArray($data);
            /**
             * @var SMSManager $smsService
             */
            $smsService = $this->getServiceLocator()->get('smsService');
            try {
                $smsService->requestVerification($data['phone']);
            } catch (\Exception $e) {
                $logger = $this->getServiceLocator()->get('logger');
                $logger->err(sprintf('%s in %s:%s', $e->getMessage(), $e->getFile(), $e->getLine()));
                $logger->debug($e->getTraceAsString());
                throw new StatusException('Cannot send activation code', self::STATUS_BAD_REQUEST);
            }
        } catch (ValidationException $e) {
            throw new StatusException($e->getMainMessage(), self::STATUS_BAD_REQUEST);
        }
    }
}