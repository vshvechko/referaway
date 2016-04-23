<?php

namespace App\Manager;



class MailManager extends AbstractManager {

    /**
     * @return \PHPMailer
     */
    public function getMailer() {
        return $this->serviceLocator->get('mailer');
    }

    public function sendPasswordEmail($to, $password) {
        $logger = $this->serviceLocator->get('logger');
        $logger->debug('sendPasswordEmail');

        $settings = $this->serviceLocator->get('settings')['email']['messages']['password'];
        $subj = $settings['subject'];
        $body = str_replace('{{PASSWORD}}', $password, file_get_contents($settings['template']));

        $mail = $this->getMailer();
        $mail->addAddress($to);
        $mail->Subject = $subj;
        $mail->Body = $body;
//        var_dump($mail); exit;
        if (!$mail->send()) {
            throw new \Exception('Mailer Error: ' . $mail->ErrorInfo);
        }
    }
}