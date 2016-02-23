<?php

namespace App\Helper;


class EncryptionHelper {

    public function getHash($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public function verify($password, $hash) {
        return password_verify($password, $hash);
    }

    public function generateShortCode() {
        return base_convert(time(), 10, 36);
    }

    public function generatePassword($length = 8) {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890#$%';
        $pass = [];
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < $length; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass);
    }
}