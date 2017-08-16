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
use app\models\Agroagent;
use yii\web\Session;


class DjiAgentUser extends Component
{
    private $_user = false;

    public function login($username,$password)
    { 
        $get_str = json_encode($_REQUEST);       
        $get_str .= "SERVER=".json_encode($_SERVER);
        $this->add_log($get_str, 'adminagent_login');

        $user = $this->getUser($username);
        if (!$user) {
            //用户不存在
            $data = array('status' => 1000,'extra' => array('msg'=>'用户不存在'));
            $get_str .= "redata=".json_encode($data);
            $this->add_log($get_str, 'adminagent_login');
            return $data;
        }
        if ($user->status == 'pending') {
            $data = array('status' => 1002,'extra' => array('msg'=>'正在审核中'));
            $get_str .= "redata=".json_encode($data);
            $this->add_log($get_str, 'adminagent_login');
            return $data;
        }else if ($user->status != 'agree') {
           $data = array('status' => 1003,'extra' => array('msg'=>'已经拒绝'));
           $get_str .= "redata=".json_encode($data);
           $this->add_log($get_str, 'adminagent_login');
           return $data;
        }

        if ($user->validatePassword($password)) {
            $session = Yii::$app->session; 
            $session->set('AGENTUSERID', $user->id);
            $session->set('AGENTUSERNAME', $user->username);
            $session->set('AGENTNAME', $user->agentname);  
            $session->set('AGENTCODE', $user->code);  
            $session->set('UPPERAGENTID', $user->upper_agent_id); 
            $session->set('AGENTTIME', time());     

            $data = array('status' => 200,'data' => $user,'extra' => array('msg'=>'ok'));
            $get_str .= "redata=".json_encode($data);
            $this->add_log($get_str, 'adminagent_login');
            return $data;
        }else{
            $data = array('status' => 1001,'extra' => array('msg'=>'密码错误'));
            $get_str .= "redata=".json_encode($data);
            $this->add_log($get_str, 'adminagent_login');
            return $data;
        }

       // return $this->get_create_uuid();
    }
     /**
     * 是否是电子邮件地址 是否为数字 
     *
     * @param mixed $value
     *
     * @return boolean
     */
    public function validate_is_email($value)
    {
        
       return preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $value);
    }


    public function get_ticket($appid, $timeout)
    {
        $session = Yii::$app->session; 
        $ticket = $this->get_create_uuid();
        //'sid' => $session->getId(),
        $data = array( 'appid' => $appid);
        $data['agent_id'] = $session->get('AGENTUSERID');
        $data['username'] = $session->get('AGENTUSERNAME');
        $data['agentname'] = $session->get('AGENTNAME');
        $data['code'] = $session->get('AGENTCODE');
        $data['upper_agent_id'] = $session->get('UPPERAGENTID');
       
        $redis = Yii::$app->redis;      
        $redis->setex($ticket, $timeout, json_encode($data));        
        return $ticket;
    }

    public function get_ticket_info($ticket)
    {
        $redis = Yii::$app->redis; 
        $info = $redis->get($ticket); 
        //请求一次后就失效
        //$redis->del($ticket);     
        return $info;
    }

    public function get_password($username,$datetime,$timeout)
    {
        $ticket = $this->get_create_uuid();
        $data = array('username' => $username, 'datetime' => $datetime);

        $redis = Yii::$app->redis;      
        $redis->setex($ticket, $timeout, json_encode($data));  
        //Yii::$app->cache->set($ticket, json_encode($data), $timeout);      
        return $ticket;
    }

    public function get_password_info($ticket,$del=1)
    {
        //return Yii::$app->cache->get($ticket);

        $redis = Yii::$app->redis; 
        $info = $redis->get($ticket); 
        if ($del == 1 ) {
           //请求一次后就失效
           $redis->del($ticket);  
        }           
        return $info;
    }


    public function logout()
    {
        $session = Yii::$app->session; 
        $session->remove('AGENTUSERID');
        $session->remove('AGENTUSERNAME');
        $session->remove('AGENTNAME');   
        $session->remove('AGENTCODE'); 
        $session->remove('UPPERAGENTID'); 
        $session->remove('AGENTTIME'); 
        return true;  

    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser($username)
    {
        if ($this->_user === false) {
            $this->_user = Agroagent::findByUsername($username);
        }
        return $this->_user;
    }
       
   

    protected function get_create_uuid()
    {
        return md5(uniqid('mpfd6ZE9gkLU9ox') . microtime()).self::uuidv4(false);
    }
    /**
     * Generate a UUID v4
     * //https://github.com/j20/php-uuid/blob/master/src/J20/Uuid/Uuid.php
     * The UUID is 36 characters with dashes, 32 characters without.
     *
     * @return string  E.g. 67f71e26-6d76-4d6b-9b6b-944c28e32c9d
     */
    public function uuidv4($dashes = true)
    {
        if ($dashes)
        {
            $format = '%s-%s-%04x-%04x-%s';
        }
        else
        {
            $format = '%s%s%04x%04x%s';
        }
        return sprintf($format,
            // 8 hex characters
            bin2hex(openssl_random_pseudo_bytes(4)),
            // 4 hex characters
            bin2hex(openssl_random_pseudo_bytes(2)),
            // "4" for the UUID version + 3 hex characters
            mt_rand(0, 0x0fff) | 0x4000,
            // (8, 9, a, or b) for the UUID variant + 3 hex characters
            mt_rand(0, 0x3fff) | 0x8000,
            // 12 hex characters
            bin2hex(openssl_random_pseudo_bytes(6))
        );
    }
    /**
     * Calculate the signature using HMAC-SHA1
     * This function is copyright Andy Smith, 2007.
     *    
     * @param string base_string
     * @param string consumer_secret
     * @param string token_secret
     * @return string  
     */
    public function signature ($base_string, $consumer_secret, $token_secret )
    {
        $key = $consumer_secret.'&'.$token_secret;
        $signature = base64_encode(hash_hmac("sha1", $base_string, $key, true));
        return $signature;
    }

    /*
    * 读取售后的数据
    */
    public function getCaseInfoByEmail($url,$email,$consumer_secret, $token_secret)
    {
        $luckkey = 'v1DJIAgentUsergetCaseInfoByEmail'.md5($email);   
        $luckdata = Yii::$app->cache->get($luckkey);
        if ( $luckdata ) {
           return $luckdata;
        } 
        $invokeId = time();
        $imgdata = array();
        $imgdata['email'] = $email;
        $imgdata['sign'] = $this->signature($email, $consumer_secret, $token_secret );
        $header = array('User-Agent: Opera/9.80 (Windows NT 6.2; Win64; x64) Presto/2.12.388 Version/12.15','Referer: http://www.dji.com','Content-Type: multipart/form-data');
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        //curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_TIMEOUT,5); 
        curl_setopt($ch, CURLOPT_POST, 0);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $imgdata);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // if any redirection after upload
        $data = curl_exec($ch);//运行curl
        $data = json_decode($data,true);
        if ($data && $data['status'] == '0') {
             Yii::$app->cache->set($luckkey, $data, 600);
        }
        return $data;
    }


     // 写入文件
    protected function add_log($msg, $type = 'djiuser')
    {
        $ip = $this->get_client_ip();
        $logfile = __DIR__.'/../runtime/logs/'.date('Ymd').'-'.$type.'.log';
        if ( ! file_exists($logfile))
        {
            file_put_contents($logfile, '');
        }
        $SERVER_ADDR = $_SERVER["SERVER_ADDR"];
        file_put_contents($logfile, date('Y/m/d H:i:s').":  $msg >>> $ip - $type  >> SERVER_ADDR=$SERVER_ADDR \r\n", FILE_APPEND);
    }

    protected function get_client_ip() {
        $ip = $_SERVER['REMOTE_ADDR'];
        if (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
            foreach ($matches[0] AS $xip) {
                if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                    $ip = $xip;
                    break;
                }
            }
        }
        return $ip;
    }


    /*
    *  返回对应的错误信息
    *
    */
    public function get_locale_msg($locale,$status)
    {
       if ($locale == 'cn') {
            switch ($status)
            {
                case 1000:
                  $msg = " 用户不存在.";
                  break;  
                default:
                  $msg = "系统繁忙.";
            }
       }else{
            switch ($status)
            {
                
                case 1000:
                   $msg = "This area is not eligible for unlocking.";
                  break;                                   
                default:
                  $msg = "System is engaged.";
            }
       }
       return $msg;

    }
    

}
