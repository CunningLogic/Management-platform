<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\components;

use Yii;
use yii\base\Component;

class Helper extends Component
{
    /***
     * 统一返回格式
     * status 0 代表成功 其他为失败，具体失败码需要定义
     */
    public static function result($status = 0, $message = '', $data = [])
    {
        return [
            'status'    => $status,
            'message'   => $message,
            'data'      => $data,
        ];
    }

    /**
     * 成功
     */
    public static function success($data = [])
    {
        return self::result(0, '', $data);
    }

    /***
     * 失败
     ***/
    public static function fail($message = '', $data = [])
    {
        return self::result(1, $message, $data);
    }
    
    /*
     * 将ip后两段标星号
     */
    public static function starIp($ip) 
    {
        $ip =  explode('.', $ip);
        $ip[3] = $ip[4] = '*';
        return implode('.', $ip);
    }

    /**
     * 获取当前毫秒数，保留整数
     */
    public static function microtime()
    {
        return intval(microtime(true) * 1000);
    }

}
