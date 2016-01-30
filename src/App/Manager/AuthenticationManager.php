<?php

namespace App\Manager;


class AuthenticationManager extends AbstractManager {

    public function getAuthToken() {
        $requestHeaders = apache_request_headers();
        $authorizationHeader = $requestHeaders['Authorization'];

        if (empty($requestHeaders['Authorization']))
            throw new \OAuthException('Not Authorized', 401);

        // validate the token
        $token = str_replace('Bearer ', '', $authorizationHeader);

        return $token;
    }
}