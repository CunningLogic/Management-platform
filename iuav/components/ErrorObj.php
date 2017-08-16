<?php

namespace app\components;

class ErrorObj
{
    public static $AUTH_FAILED = ['status' => 1001, 'status_msg' => 'failed', 'message' => '登录失效'];

    public $status;
    public $status_msg;
    public $message;
    public $data;

    function __construct($s, $sm, $m, $d = null) {
        $this->status = $s;
        $this->status_msg = $sm;
        $this->message = $m;
        $this->data = $d;
    }

    public static function create($s, $sm, $m, $d = null) {
        return new ErrorObj($s, $sm, $m, $d);
    }

    public function to_array() {
        return [
            'status' => $this->status,
            'status_msg' => $this->status_msg,
            'message' => $this->message,
            'data' => $this->data
        ];
    }
}