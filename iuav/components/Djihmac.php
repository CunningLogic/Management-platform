<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\InvalidValueException;

class Djihmac extends Component
{
    

    public function getSign($params_string,$return = 0)
    {
        if (isset(Yii::$app->params['GWServer']) ) {  
            if ($return == 1) {
              $hmackey = Yii::$app->params['GWServer']['HMACKEYRETURN'];
            } else if($return == 2) {
              $hmackey = 'xTgF747f9QD8KZ9acVorbymeayKiV'; //用于保险
            }
            else{
              $hmackey = Yii::$app->params['GWServer']['HMACKEY'];
            }        
            
        } 
        //echo $hmackey;
        $sign = strtoupper(hash_hmac("sha256", $params_string, $hmackey));
        //echo $sign;exit;
        return $sign;
    }

    public function getAesDecrypt($encrypted_string)
    {
      $hmackey = 'df34dsf234323';
      if (isset(Yii::$app->params['GWServer']) ) {  
         $hmackey = Yii::$app->params['GWServer']['AES128KEY'];             
      }       
      return openssl_decrypt( base64_decode($encrypted_string), "aes-128-cbc", $hmackey, true);
    }

    public function getAesEncrypt($params_string,$return = 0)
    {
      $hmackey = 'df34dsf234323';
      if (isset(Yii::$app->params['GWServer']) ) {  
         if ($return == 1) {
              $hmackey = Yii::$app->params['GWServer']['AES128KEYRETURN'];
         }else{
              $hmackey = Yii::$app->params['GWServer']['AES128KEY'];
         }           
      } 
      return @base64_encode( openssl_encrypt( $params_string, "aes-128-cbc", $hmackey, true) );
     
    }

    

}
