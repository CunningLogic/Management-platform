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

class DjiUser extends Component
{
    public function getTest()
    {
       
        return 1;
    }
    
    //退出登录
    public function logout()
    {
        $session = Yii::$app->session; 
        $session->remove('IUAVUSERID');
        $session->remove('IUAVUSERTIME'); 
        return true;  

    }

    /**
     * 是否是电子邮件地址   是否为数字字符串
     *
     * @param mixed $value
     *
     * @return boolean
     */
    public function validate_is_account($value)
    {
       if (is_numeric($value)) {
            return true;
        }
        //return preg_match('/^[a-z0-9]+[._\-\+]*@([a-z0-9]+[-a-z0-9]*\.)+[a-z0-9]+$/i', $value);
        return preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $value);
    }
    //增加user_id 和  register_phone
    public function getModelData($model)
    {
       $get_str = json_encode($model);
        try {
           if ($model['token']) {
              if ($model['os'] == 'default') {
                  $userInfo = $this->get_account_info_by_key($model['token']); 
                  if (is_array($userInfo)) {
                      $get_str .= json_encode($userInfo);
                  }else{
                      $get_str .= $userInfo;
                  }                  
                  $this->add_log($get_str,"getModelData");
                  if ($userInfo && $userInfo['status'] == '0' && $userInfo['status_msg'] == 'ok' ) {
                      $model['user_id'] = $userInfo['items']['0']['account_info']['user_id'];
                      $model['account'] = $userInfo['items']['0']['account_info']['email'];
                      $model['register_phone'] = $userInfo['items']['0']['account_info']['register_phone'];
                  }
              }elseif ($model['os'] == 'ios' || $model['os'] == 'android' ) {
                  $userInfo = $this->get_account_info_by_key('',$model['token']);
                   if (is_array($userInfo)) {
                      $get_str .= json_encode($userInfo);
                  }else{
                      $get_str .= $userInfo;
                  }                  
                  $this->add_log($get_str,"getModelData");

                  if ($userInfo && $userInfo['status'] == '0' && $userInfo['status_msg'] == 'ok' ) {
                      $model['user_id'] = $userInfo['items']['0']['account_info']['user_id'];
                      $model['account'] = $userInfo['items']['0']['account_info']['email'];
                      $model['register_phone'] = $userInfo['items']['0']['account_info']['register_phone'];
                  }
              }
            }
          
        } catch (Exception $e) {
          
        }        
        return $model;

    }
    public function getLocale($country) 
    {
       if (in_array($country, array('cn'))) {
           $locale = 'zh_CN';
       } elseif (in_array($country, array('tw', 'mo', 'hk'))) {
           $locale = 'zh_TW';
       } elseif (in_array($country, array('kr'))) {
           $locale = 'ko_KR';
       } elseif (in_array($country, array('ja'))) {
           $locale = 'ja_JP';
       } else {
           $locale = 'en_US';
       } 
       return $locale;    
    }

    public function getBackUrl($act = '')
    {
       $returnUrl = "https://ag.dji.com/"; 
       if (isset(Yii::$app->params['GWServer']) ) {
            $returnUrl = Yii::$app->params['GWServer']['returnUrl']; 
          
        } 
        //$backUrl = ($returnUrl."usermg/");      
        $backUrl = Yii::$app->request->getReferrer();  
        if(empty($backUrl)) {
            $backUrl = ($returnUrl."usermg/");
        }                                                                    
        return $backUrl; 
    }

    public function getLoginUrl($back_url = '',$locale = '')
    {
        $loginUrl = 'https://account.dji.com/user/login.html'; 
        $appId = 'dji_ag';
        if (isset(Yii::$app->params['GWServer']) ) {
            $loginUrl = Yii::$app->params['GWServer']['loginUrl'];
            $appId = Yii::$app->params['GWServer']['GWAPIAPPID'];
          
        } 
        $queryArray = [
            'appId'     => $appId,
            'backUrl'   => $back_url,
            'locale'   => $locale,
        ];
        
        return $loginUrl . '?' . http_build_query($queryArray);
    }

    public function getLogoutUrl($back_url = '')
    {
        $logoutUrl = 'https://account.dji.com/user/logout.html'; 
        $appId = 'dji_ag';
        if (isset(Yii::$app->params['GWServer']) ) {
            $logoutUrl = Yii::$app->params['GWServer']['logoutUrl'];
            $appId = Yii::$app->params['GWServer']['GWAPIAPPID'];
          
        }  
        $queryArray = [
            'appId'     => $appId,
            'backUrl'   => $back_url
        ];
        return $logoutUrl . '?' . http_build_query($queryArray);
    }

    /*
    * 直接创建会员，并且发送激活邮件
    */
    public function account_create($email,$passwd,$nick_name,$user_type,$area_code,$phone,$sms_code) 
    {
        if (empty($passwd) || empty($user_type)) {
            return false;
        }
        $luckkey = __CLASS__.__FUNCTION__.md5($email.$phone.$user_type.$passwd);   
        $luckdata = Yii::$app->cache->get($luckkey);
        if ( $luckdata ) {
           return $luckdata;
        }    
        if (isset(Yii::$app->params['GWServer']) ) {
            $gwapi = Yii::$app->params['GWServer']['GWAPIURL']; 
            $gwapikey = Yii::$app->params['GWServer']['GWAPIFLYSAFEKEY'];   
            $appId = Yii::$app->params['GWServer']['GWAPIAPPID'];  
        } 
        if (empty($gwapi) || empty($gwapikey) || empty($appId) ) {
            return array('Status' => 101);
        }
        $accessToken = $this->getAccessToken($gwapi,$gwapikey,$appId);
        $postData = array('email'=>$email,'passwd'=>$passwd,'app_id'=>$appId,'nick_name'=>$nick_name,'user_type'=>$user_type,'area_code'=>$area_code,'phone'=>$phone,'sms_code'=>$sms_code); 
        $url = $gwapi."/gwapi/api/accounts/account_create"; 
        $data = $this->postGateway($url, $postData);        
        if ($data && $data['status'] == '0' && $data['status_msg'] == 'ok' ) {
             return $data;
        }else{           
            Yii::$app->cache->set($luckkey, 0, 10); 
        }        
        return $data;
    }
    public function get_captcha($uuid,$nocache=0)
    {
        if ($nocache === 1) {
          $captchaKey = $this->get_captcha_key();    
          $captchaData = Yii::$app->cache->get($captchaKey);
          if (empty($captchaData)) {
              return '';
          }
        }
        $captchaKey = __CLASS__.__FUNCTION__."get_captcha".md5($this->get_client_ip().$uuid);   
        $captchaData = Yii::$app->cache->get($captchaKey);
        if ($captchaData && empty($nocache)) {
           return $captchaData;
        }
       //产生随机字符
        $authcode = '';
        for($i = 0; $i < 4; $i ++) {
            $randAsciiNumArray  = array (rand(48,57),rand(65,90));
            $randAsciiNum = $randAsciiNumArray [rand ( 0, 1 )];
            $randStr = chr ( $randAsciiNum );                
            $authcode .= $randStr; 
        }
        Yii::$app->cache->set($captchaKey, $authcode, 300);
        return $authcode;

    }
    //是否出现验证码key
    public function get_captcha_key()
    {
       return "v2DJIUserComp_captcha_".md5($this->get_client_ip()); 
    }

    /*
    * 会员登陆
    */
    public function user_all_login($email,$passwd,$area_code,$phone,$uuid='',$captcha='') 
    {
        $captchaKey = $this->get_captcha_key();   
        $captchaData = Yii::$app->cache->get($captchaKey);
        $show_code = $nowcaptchaData = '';
        if ($captchaData) {            
           $nowcaptchaData = $this->get_captcha($uuid);
           $show_code = $this->get_captcha($uuid,1);
        }

        if (empty($passwd) || (empty($email) && empty($phone))) {
            return array('status' => 201,"status_msg" => "failed","message" => "参数不合法",'show_code' => $show_code);
        }
        $luckkey = __CLASS__.__FUNCTION__.md5($email.$phone.$area_code.$passwd);   
        $luckdata = Yii::$app->cache->get($luckkey);
        if ( $luckdata ) {
           //return $luckdata;
        }
        $errorkey = __CLASS__.__FUNCTION__."_erroripv1_".md5($email.$phone.$area_code);   
        $errorData = Yii::$app->cache->get($errorkey);
        if ( $errorData  && $errorData > 5) {
           return array('status' => 2001,"status_msg" => "failed","message" => "用户名或者密码错误数超过限制次数",'show_code' => $show_code);
        }
        $errorIpKey = __CLASS__.__FUNCTION__."_erroripv1_".md5($this->get_client_ip());   
        $errorIpData = Yii::$app->cache->get($errorIpKey);

        if ( $show_code && $errorIpData  && $errorIpData > 10 && empty($captcha)) {
           return array('status' => 2003,"status_msg" => "failed","message" => "验证码错误!",'show_code' => $show_code);
        } 

        if ($show_code && ($nowcaptchaData || $captcha) && strtolower($nowcaptchaData) != strtolower($captcha) ) {
            return array('status' => 2003,"status_msg" => "failed","message" => "验证码错误!!",'show_code' => $show_code);
        }        
        if ( $errorIpData  && $errorIpData > 1 && empty($captchaData) ) {    
            Yii::$app->cache->set($captchaKey, 1, 3600);
        }
        if ( $errorIpData  && $errorIpData > 30) {
           return array('status' => 2002,"status_msg" => "failed","message" => "用户名或者密码错误数超过限制次数!",'show_code' => $show_code);
        }      
        if (isset(Yii::$app->params['GWServer']) ) {
            $gwapi = Yii::$app->params['GWServer']['GWAPIURL']; 
            $gwapikey = Yii::$app->params['GWServer']['GWAPIFLYSAFEKEY'];   
            $appId = Yii::$app->params['GWServer']['GWAPIAPPID'];  
        } 
        if (empty($gwapi) || empty($gwapikey) || empty($appId) ) {
            return array('status' => 201,"status_msg" => "failed","message" => "参数不合法!",'show_code' => $show_code);
        }
        $postData = array('email'=>$email,'passwd'=>$passwd,'app_id'=>$appId,'area_code'=>$area_code,'phone'=>$phone); 
        $url = $gwapi."/gwapi/api/accounts/user_all_login"; 
        $data = $this->postGateway($url, $postData);        
        if ($data && $data['status'] == '0' && $data['status_msg'] == 'ok' ) {
             $data['show_code'] = $show_code;  
             return $data;
        }else{ 
            if ($data && ($data['status'] == '311' || $data['status'] == '305')) {
                $data['status'] = 305;
                $data['message'] = "用户名或者密码错误";
            } 
            $data['show_code'] = $show_code;             
            Yii::$app->cache->set($luckkey, $data, 10);
            Yii::$app->cache->set($errorkey, $errorData+1, 1800);
            Yii::$app->cache->set($errorIpKey, $errorIpData+1, 3600);
        }        
        return $data;
    }


    
    public function get_meta_key_by_ticket($ticket,$actionType) 
    {
        if (empty($ticket) || empty($actionType)) {
            return false;
        }
        $luckkey = __CLASS__.__FUNCTION__.md5($ticket.$actionType);   
        $luckdata = Yii::$app->cache->get($luckkey);  
        if ( $luckdata ) {
           return $luckdata;
        }    
        if (isset(Yii::$app->params['GWServer']) ) {
            $gwapi = Yii::$app->params['GWServer']['GWAPIURL']; 
            $gwapikey = Yii::$app->params['GWServer']['GWAPIFLYSAFEKEY'];   
            $appId = Yii::$app->params['GWServer']['GWAPIAPPID'];  
        } 
        if (empty($gwapi) || empty($gwapikey) || empty($appId) ) {
            return array('Status' => 101);
        }

        //$accessToken = $this->getAccessToken($gwapi,$gwapikey,$appId);
        $key_str = $ticket.$actionType.$appId;       
        $sign =  base64_encode(hash_hmac('SHA1', $key_str, $gwapikey,true));
        $imgdata = array('ticket'=>$ticket,'actionType'=>$actionType,'appid'=>$appId,'sign'=>$sign); 
        $url = $gwapi."/gwapi/api/accounts/v1/result"; 
        $data = $this->postGateway($url, $imgdata);      
        if ($data && $data['status'] == '0' && $data['status_msg'] == 'ok' ) {
             Yii::$app->cache->set($luckkey, $data, 3600);
        }else{           
            Yii::$app->cache->set($luckkey, 0, 10); 
        }        
        return $data;
    }


    public function get_user($account)
    {
        if (empty($account)) {
            return false;
        }
        $luckkey = __CLASS__.__FUNCTION__.md5($account);   
        $luckdata = Yii::$app->cache->get($luckkey);
        if ( $luckdata ) {
           return $luckdata;
        }    
        if (isset(Yii::$app->params['GWServer']) ) {
            $gwapi = Yii::$app->params['GWServer']['GWAPIURL']; 
            $gwapikey = Yii::$app->params['GWServer']['GWAPIFLYSAFEKEY'];   
            $appId = Yii::$app->params['GWServer']['GWAPIAPPID'];  
        } 
        if (empty($gwapi) || empty($gwapikey) || empty($appId) ) {
            return array('Status' => 101);
        }
        $imgdata = array();
        if (is_numeric($account)) {
            $imgdata['uid'] = $account;
        }else{
            $imgdata['email'] = $account;
        }
        $url = $gwapi."/gwapi/api/accounts/get_user";
        $data = $this->postGateway($url, $imgdata);        
        if ($data && $data['status'] == '0' && $data['status_msg'] == 'ok' ) {
             Yii::$app->cache->set($luckkey, $data, 3600);
        }else{
            $luckkey = 'EventsGetAccessToken'.md5($gwapi.$appId);   
            Yii::$app->cache->set($luckkey, 0, 10); 
        }
        
        return $data;
    }
    //通过邮箱或者手机号获取账号基本信息
    public function direct_get_user($login_name)
    {
        if (empty($login_name)) {
            return false;
        }
        $luckkey = __CLASS__.__FUNCTION__.md5($login_name)."v11";   
        $luckdata = Yii::$app->cache->get($luckkey);
        if ( $luckdata ) {
           return $luckdata;
        }        
        if (isset(Yii::$app->params['GWServer']) ) {
            $gwapi = Yii::$app->params['GWServer']['GWAPIURL']; 
            $gwapikey = Yii::$app->params['GWServer']['GWAPIFLYSAFEKEY'];   
            $appId = Yii::$app->params['GWServer']['GWAPIAPPID'];  
        } 
        if (empty($gwapi) || empty($gwapikey) || empty($appId) ) {
            return array('Status' => 101);
        }        
        $imgdata = array();
        $imgdata['login_name'] = $login_name;  

        $url = $gwapi."/gwapi/api/accounts/direct_get_user";
        $data = $this->postGateway($url, $imgdata);       
        if ($data && $data['status'] == '0' && $data['status_msg'] == 'ok' ) {
             Yii::$app->cache->set($luckkey, $data, 3600);
        }else{
            $luckkey = 'EventsGetAccessToken'.md5($gwapi.$appId);   
            Yii::$app->cache->set($luckkey, 0, 10); 
        }
        
        return $data;
    }

    //通知官网用户已经购买农业无人机
    public function fogachine($user_id)
    {
        if (empty($user_id)) {
            return false;
        }
        $luckkey = __CLASS__.__FUNCTION__.md5($user_id);   
        $luckdata = Yii::$app->cache->get($luckkey);
        if ( $luckdata ) {
            return $luckdata;
        }        
        if (isset(Yii::$app->params['GWServer']) ) {
            $gwapi = Yii::$app->params['GWServer']['GWAPIURL']; 
            $gwapikey = Yii::$app->params['GWServer']['GWAPIFLYSAFEKEY'];   
            $appId = Yii::$app->params['GWServer']['GWAPIAPPID'];  
        } 
        if (empty($gwapi) || empty($gwapikey) || empty($appId) ) {
            return array('Status' => 101);
        }
        if (isset(Yii::$app->params['DJISTOREAPI']) ) {
            $djiAppId = Yii::$app->params['DJISTOREAPI']['appId']; 
            $app_secret = Yii::$app->params['DJISTOREAPI']['app_secret'];   
            
        } 
        if (empty($djiAppId) || empty($app_secret)) {
            return array('Status' => 101);
        }
        $accessToken = $this->getAccessToken($gwapi,$gwapikey,$appId);
        $imgdata = array();        
        $invokeId = time();
        $url = $gwapi."/gwapi";
        $api_path = '/api/users/'.$user_id.'/fog_machine';
        $url .= $api_path;
        $http_verb = 'POST';
        $timestamp = date("D, d M y H:i:s O",time());
        $parent_string =  $djiAppId."\nPOST\n".$api_path."\n".$timestamp."\n";
        $sign = hash_hmac("md5", $parent_string, $app_secret);
        $ip = $this->get_client_ip();
        $imgdata = array();
        $header = array('Content-Type: application/json');
        $header[] = 'userAgentIp-gw: '.$ip;
        $header[] = 'consumerAppId-gw: '.$appId;
        $header[] = 'providerAppId-gw: store';
        $header[] = 'accessToken-gw: '.$accessToken;
        $header[] = 'invokeId-gw: '.$invokeId;
        $header[] = 'directDomain-gw: ';
        $header[] = 'X-APP-ID: '.$djiAppId;     
        $header[] = 'X-DJI-Sign: '.$sign;
        $header[] = 'X-Timestamp: '.$timestamp;
        $header[] = 'Accept: application/vnd.dji-v4';     
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        //curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_TIMEOUT,15); 
        curl_setopt($ch, CURLOPT_POST, 0);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $imgdata);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // if any redirection after upload
        $data = curl_exec($ch);//运行curl
        if ($data && $data  == '{"message":"OK"}' ) {          
             Yii::$app->cache->set($luckkey, $data, 3600);
        }        
        return $data;
    }


    /*
    *  通过token读取用户的信息
    */
    public function get_account_info_by_key($meta_key,$token='')
    {
        if (isset(Yii::$app->params['GWServer']) ) {
            $gwapi = Yii::$app->params['GWServer']['GWAPIURL']; 
            $gwapikey = Yii::$app->params['GWServer']['GWAPIFLYSAFEKEY'];
            $appId = Yii::$app->params['GWServer']['GWAPIAPPID'];         
        }        
        if (empty($gwapi) || empty($gwapikey) || empty($appId) ) {
            return array('status' => 101);
        }
        $luckkey = 'get_account_info_by_key'.md5($meta_key.$token);   
        $luckdata = Yii::$app->cache->get($luckkey);
        if ( $luckdata ) {
           return $luckdata;
        } 
        if (!$token) {
            $token = $this->get_token_by_meta_key($gwapi,$gwapikey,$meta_key,$appId);       
            if ($token && $token['status'] == '0' && $token['status_msg'] == 'ok' ) {
               $token = $token['items']['0']['token'];
            }else{
               return $token;
            }
        }       
        
        $url = $gwapi."/gwapi/api/accounts/get_account_info_by_key";
        $imgdata = array();
        $imgdata['token'] = $token;
        $data = $this->postGateway($url, $imgdata); 
        if ($data && $data['status'] == '0' && $data['status_msg'] == 'ok' ) {
             Yii::$app->cache->set($luckkey, $data, 3600);
        }
        return $data;

    }
    public function get_token_by_meta_key($gwapi,$gwapikey,$meta_key,$appId='dji_events')
    {
        $luckkey = 'get_token_by_meta_key'.md5($meta_key);   
        $luckdata = Yii::$app->cache->get($luckkey);
        if ( $luckdata ) {
           return $luckdata;
        } 
        $url = $gwapi."/gwapi/api/accounts/get_token_by_meta_key";
        $imgdata = array();
        $imgdata['meta_key'] = $meta_key; 
        $data = $this->postGateway($url, $imgdata);       
        if ($data && $data['status'] == '0' && $data['status_msg'] == 'ok' ) {
             Yii::$app->cache->set($luckkey, $data, 3600);
        }else{
            $luckkey = 'EventsGetAccessToken'.md5($gwapi.$appId);   
            Yii::$app->cache->set($luckkey, 0, 10); 
        }
        
        return $data;
    }

    protected function getAccessToken($gwapi,$gwapikey,$appId='dji_events')
    {  
        $luckkey = 'iuavGetAccessToken'.md5($gwapi.$appId);   
        $luckdata = Yii::$app->cache->get($luckkey); 
        if ( $luckdata ) {
           return $luckdata;
        }    
        try {
          $ip = $this->get_client_ip();
          $opts = array(
                 'http' => array(
                  'method'=>'GET',
                  'timeout' => 10,
                  'header'=>"userAgentIp-gw: ".$ip."\r\n"
              )
          );     
          $context = stream_context_create($opts);
          $locUrl =  $gwapi."/api/token/challengeCode?appId=".$appId;       
          $challengeCode = file_get_contents($locUrl,false,$context);
          $signCode = urlencode(base64_encode(hash_hmac("sha1", $challengeCode, $gwapikey,true)  ));
          $url = $gwapi."/api/token?appId=".$appId."&signCode=$signCode&challengeCode=$challengeCode";       
          $token = file_get_contents($url,false,$context);      
          $token = json_decode($token,true);
          $accessToken =  $token['accessToken'];          
          Yii::$app->cache->set($luckkey, $accessToken, 3600);
              
        } catch (Exception $e) {

           return false;

        }    
        return $accessToken;

    }

    public function postGateway($url, $postdata) 
    {
       if (isset(Yii::$app->params['GWServer']) ) {
            $gwapi = Yii::$app->params['GWServer']['GWAPIURL']; 
            $gwapikey = Yii::$app->params['GWServer']['GWAPIFLYSAFEKEY'];   
            $appId = Yii::$app->params['GWServer']['GWAPIAPPID'];  
        } 
        if (empty($gwapi) || empty($gwapikey) || empty($appId) ) {
            return array('status' => 101);
        }
        $ip = $this->get_client_ip();
        $accessToken = $this->getAccessToken($gwapi,$gwapikey,$appId);  
        $invokeId = time();          
        $header = array('User-Agent: Opera/9.80 (Windows NT 6.2; Win64; x64) Presto/2.12.388 Version/12.15','Referer: http://www.dji.com','Content-Type: multipart/form-data');
        $header[] = 'userAgentIp-gw: '.$ip;
        $header[] = 'consumerAppId-gw: '.$appId;
        $header[] = 'providerAppId-gw: member_center';
        $header[] = 'accessToken-gw: '.$accessToken;
        $header[] = 'invokeId-gw: '.$invokeId;
        $header[] = 'directDomain-gw: ';
        $header[] = 'domain: ';                         
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        //curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_TIMEOUT,5); 
        curl_setopt($ch, CURLOPT_POST, 0);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // if any redirection after upload
        $data = curl_exec($ch);//运行curl
        $data = json_decode($data,true);                
        return $data;
    }
     
    /*
    *  ERR_NOT_AUTH                = 1001
    *  ERR_DB_FAILED               = 1002
    *  ERR_PARAMS_ERR              = 1003
    *  ERR_NOT_ONLINE              = 1004
    *  ERR_REQ_REALTIME_SVR_FAILED = 1005
    *  ERR_SEND_APP_FAILED         = 1006
    *  ERR_APP_RESPONE_TIMEOUT     = 1007
    *
    *  CMD_FREEZE                 = "freeze"
    *  CMD_UNFREEZE               = "unfreeze"
    */
    public function runGoSendmsg($sn, $msg)
    {
        $goSendmsgUrl = "";
        if(isset(Yii::$app->params['GWServer'])) {
            $goSendmsgUrl = Yii::$app->params['GWServer']['goSendmsgUrl'];
            $goAuthToken = Yii::$app->params['GWServer']['goAuthToken'];  
        }
        if (empty($goSendmsgUrl)) {
            return false;
        }
        $ip = $this->get_client_ip();
        $header[] = 'AuthToken: '.$goAuthToken;
        $header[] = 'userAgentIp-gw: '.$ip;
        $url =  $goSendmsgUrl."?msg=".$msg."&sn=$sn"; 
        $ch = curl_init($url);   
        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回  
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回  
        $result = curl_exec($ch);  
        $this->add_log($url, 'runGoSendmsg'); 
        $this->add_log($result, 'runGoSendmsg');
        if(curl_errno($ch))
        {   
            $this->add_log(curl_errno($ch), 'runGoSendmsg');
            return false;
        }  
        $result = json_decode($result, true);   
        return $result;
    }
    //解锁飞机，通知app
    public function runGoRealTimeLock($sendAPP)
    {
        $goRealTimeLockUrl = "";
        if (isset(Yii::$app->params['GWServer']) ) {
            $goRealTimeLockUrl = Yii::$app->params['GWServer']['goRealTimeLockUrl']; 
            $goAuthToken = Yii::$app->params['GWServer']['goAuthToken'];              
        }
        if (empty($goRealTimeLockUrl)) {
          return false;
        }
        $ip = $this->get_client_ip();
        $header[] = 'AuthToken: '.$goAuthToken;
        $header[] = 'userAgentIp-gw: '.$ip;
        $url =  $goRealTimeLockUrl."?"."sn=".$sendAPP['sn']."&cmd=".$sendAPP['cmd']."&bossid=".$sendAPP['bossid'];
        $ch = curl_init($url);   
        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回  
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回  
        $result = curl_exec($ch);  
        $this->add_log($url, 'runGoRealTimeLock'); 
        $this->add_log($result, 'runGoRealTimeLock');
        if(curl_errno($ch)) {   
            $this->add_log(curl_errno($ch), 'runGoRealTimeLock');
            return false;
        }  
        $result = json_decode($result, true);
        return $result;
    }
    //按时间段锁定飞机，通知app
    public function runGoTimeLock($sendAPP) {
        $goTimeLockUrl = "";
        if (isset(Yii::$app->params['GWServer']) ) {
            $goTimeLockUrl = Yii::$app->params['GWServer']['goTimeLockUrl']; 
            $goAuthToken = Yii::$app->params['GWServer']['goAuthToken'];              
        }
        if (empty($goTimeLockUrl)) {
            return false;
        }

        $ip = $this->get_client_ip();
        $header[] = 'AuthToken: '.$goAuthToken;
        $header[] = 'userAgentIp-gw: '.$ip;

        $url =  $goTimeLockUrl."?"."bossname=".$sendAPP['bossname']."&bossid=".$sendAPP['bossid']."&sn=".$sendAPP['sn']."&cmd=".$sendAPP['cmd']."&lock_begin=".$sendAPP['lock_begin']."&lock_end=".$sendAPP['lock_end']; 
        $ch = curl_init($url);   
        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回  
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回  
        $result = curl_exec($ch);  
        $this->add_log($url, 'runGoTimeLock'); 
        $this->add_log($result, 'runGoTimeLock');
        if (curl_errno($ch)) {   
            $this->add_log(curl_errno($ch), 'runGoTimeLock');
            return false;
        }  
        $result = json_decode($result, true);
     
        return $result;
    }

    public function regeo($longi,$lati)
    {
        $post_data = array();
        $post_data['location']  = $longi.','.$lati;
        $post_data['key']  = 'b1501370e873f5784f75d43d061c181a';
        $url = "http://restapi.amap.com/v3/geocode/regeo";       
        $result = $this->send_post($url,$post_data);
        if ($result) {
          $resultData = json_decode($result,true);          
          if ($resultData['status'] == '1' && $resultData['info'] == 'OK') {
              return empty($resultData['regeocode']['formatted_address']) ? '' : $resultData['regeocode']['formatted_address'];
          }
        }
        return '';
    }
    public function send_post($url,$post_data)
    {
       try {         
           $postdata = http_build_query($post_data);  
           $url =  $url.'?'.$postdata;
            $options = array(
                'http' => array(                
                    'timeout' => 15*60,
                  )
              );
            $context = stream_context_create($options);
            $result = file_get_contents($url,false,$context);
            return $result;
       } catch (Exception $e) {
           return '';
       }
        
    }

       
    // 写入文件
    public function add_log($msg, $type = 'djiuser')
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
        if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
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
                case 441:
                  $msg = " 格式不对.";
                  break;  
                case 401:
                  $msg = " 参数为空.";
                  break;
                case 410:
                  $msg = "账号格式不对.";
                  break;
                case 400:
                  $msg = "签名不对.";
                  break;
                case 402:
                  $msg = "请10秒后再请求.";
                  break;
                case 404:
                  $msg = "SN为空,请先添加SN码.";
                  break;
                case 405:
                  $msg = "用户未验证通过.";
                  break;
                case 406:
                  $msg = "一个sn号最多只有30个未过期的限飞区";
                  break;
                case 407:
                  $msg = "该限飞区不允许解禁";
                  break;
                default:
                  $msg = "系统繁忙.";
            }
       }else{
            switch ($status)
            {
                case 441:
                  $msg = " incorrect input ";
                  break;  
                case 401:
                   $msg = "incorrect input ";
                  break;
                case 410:
                   $msg = "account is not righit.";
                  break;
                case 400:
                   $msg = "signature is not righit.";
                  break;
                case 402:
                   $msg = "Please try again after 10 seconds.";
                  break;
                case 404:
                   $msg = "Please input correct serial number.";
                  break;
                case 405:
                   $msg = "The user's ID doesn't exist.";
                  break;
                case 406:
                   $msg = "Too many requisition.Please delete to add more.";
                  break; 
                case 407:
                   $msg = "This area is not eligible for unlocking.";
                  break;                                   
                default:
                  $msg = "System is engaged.";
            }
       }
       return $msg;

    }
    public function isMobile()
    {
        //return $_SERVER['HTTP_HOST'] == 'm.dji.com';
            #如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
        {
            return true;
        } 
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset ($_SERVER['HTTP_VIA']))
        { 
            // 找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
        } 
        // 脑残法，判断手机发送的客户端标志,兼容性有待提高
        if (isset ($_SERVER['HTTP_USER_AGENT']))
        {
            $clientkeywords = array ('nokia',
                'sony',
                'ericsson',
                'mot',
                'samsung',
                'htc',
                'sgh',
                'lg',
                'sharp',
                'sie-',
                'philips',
                'panasonic',
                'alcatel',
                'lenovo',
                'iphone',
                'ipod',
                'blackberry',
                'meizu',
                'android',
                'netfront',
                'symbian',
                'ucweb',
                'windowsce',
                'palm',
                'operamini',
                'operamobi',
                'openwave',
                'nexusone',
                'cldc',
                'midp',
                'wap',
                'mobile'
            ); 
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
            {
                return true;
            } 
        } 
        // 协议法，因为有可能不准确，放到最后判断
        if (isset ($_SERVER['HTTP_ACCEPT']))
        { 
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
            {
                return true;
            } 
        } 
        return false;

    }

    

}
