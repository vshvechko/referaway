<?php

namespace App\Helper;

class ResponseDataFormatter {
    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';

    public function getSuccess($data = null) {
        return ['status' => self::STATUS_SUCCESS, 'data' => $data];
    }

    public function getFailure($message) {
        return ['status' => self::STATUS_ERROR, 'message' => $message];
    }
}