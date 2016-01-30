<?php

namespace App\Helper;


class EncryptionHelper {

    public function getHash($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public function verify($password, $hash) {
        return password_verify($password, $hash);
    }
}