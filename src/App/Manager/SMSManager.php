<?php

namespace App\Manager;


use Interop\Container\ContainerInterface;

class SMSManager extends AbstractManager{

    protected $appKey;
    protected $appSecret;
    
    public function __construct(ContainerInterface $c)
    {
        parent::__construct($c);
        $settings = $c->get('settings')['sms'];
        $this->appKey = $settings['appKey'];
        $this->appSecret = $settings['appSecret'];
    }

    public function sendActivationCode($phone, $code) {
        $logger = $this->serviceLocator->get('logger');
        $logger->debug(__CLASS__ . '->sendShortCode');

        $logger->err('SEND SHORT CODE METHOD NOT IMPLEMENTED');
    }

    public function requestVerification($phone) {
        $user = sprintf('application\%s:%s', $this->appKey, $this->appSecret);
        
        $message = [
            'identity' => [
                'type' => 'number',
                'endpoint' => $phone
            ],
            'metadata' => [
                'os' => 'rest',
                'platform' => 'N/A'
            ],
            'method' => 'sms'
        ];
        $data = json_encode($message);
        $ch = curl_init('https://api.sinch.com/verification/v1/verifications');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_USERPWD,$user);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $result = curl_exec($ch);
        $error = null;
        if (curl_errno($ch)) {
            $error = new \Exception('Curl error: ' . curl_error($ch));
        }
        curl_close($ch);
        if ($error) {
            throw $error;
        }
    }

    public function reportVerification($phone, $code) {
        $user = sprintf('application\%s:%s', $this->appKey, $this->appSecret);
        $message = [
            'sms' => [
                'code' => $code,
            ],
            'method' => 'sms'
        ];
        $data = json_encode($message);
        $ch = curl_init('https://api.sinch.com/verification/v1/verifications/number/' . $phone);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_USERPWD,$user);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $result = curl_exec($ch);

        $error = null;
        if (curl_errno($ch)) {
            $error = new \Exception('Curl error: ' . curl_error($ch));
        }
        curl_close($ch);

        if ($error) {
            return false;
        }
        $data = json_decode($result);
//        var_dump($data); exit;
        if (isset($data->status) && $data->status == 'SUCCESSFUL') {
            return true;
        }
        return false;
    }

}