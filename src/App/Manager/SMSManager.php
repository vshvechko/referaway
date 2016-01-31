<?php

namespace App\Manager;


class SMSManager extends AbstractManager{

    public function sendActivationCode($phone, $code) {
        $logger = $this->serviceLocator->get('logger');
        $logger->debug(__CLASS__ . '->sendShortCode');

        $logger->err('SEND SHORT CODE METHOD NOT IMPLEMENTED');
    }
}