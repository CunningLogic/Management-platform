<?php

namespace app\controllers;

use app\models\AgroMissionComplete;
use app\models\AgroSuperAdminLevel;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use app\models\LoginForm;
use GeoIp2\Database\Reader;
use app\components\DjiUser;
use app\components\Djihmac;
use app\models\Agroactiveinfo;
use app\models\Agroflyer;
use app\models\Agroactiveflyer;
use app\models\Agrotask;
use app\models\Agroteamtask;
use app\models\Agroflight;
use app\models\Iuavflightdata;
use app\models\Agropolicies;
use app\models\Agroflyerworkinfo;
use app\models\Agronetworkcard;

class ApimgController extends Controller
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'contentNegotiator' => [
                'class' => 'yii\filters\ContentNegotiator',
                'formats' => [
                    'json'
                ],
            ],
        ]);
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }
    // 生成验证码
    protected function get_sign($params_string)
    {           
        $Djihmac = new Djihmac();
        $sign = $Djihmac->getSign($params_string);  
        return $sign;        
    }

    /* 
    *  用户注册 http://ag.aasky.net/apimg/register
    *  @parameter email 注册邮箱
    *  @parameter passwd  账号密码
    *  @parameter nick_name 昵称
    *  return 
    * {"status":0,"status_msg":"ok",'message':}
    * "status":1003表示参数不合法或者不匹配;
    * 
    */
    public function actionRegister()
    {
        $email = Yii::$app->request->post("email");
        $passwd = Yii::$app->request->post("passwd");
        $nick_name = Yii::$app->request->post("nick_name");

        $status_msg = 'failed';
        if(empty($email) || empty($passwd) || empty($nick_name)) {
            $result = array('status' => 1003, 'status_msg'=>$status_msg, 'message' => '参数不足或者不匹配',);
            return $result; 
        }
        $appId = Yii::$app->params['GWServer']['GWAPIAPPID']; 
        $gwapi = Yii::$app->params['GWServer']['GWAPIURL']; 
        $postData = array('email'=>$email,'passwd'=>$passwd, 'nick_name' => $nick_name, 'app_id'=>$appId); 
        $url = $gwapi."/gwapi/api/accounts/account_create"; 
        $data = (new DjiUser)->postGateway($url, $postData); 
        return $data;
    }
    //用户注册 user/register
    public function actionLizhanbin()
    {
        $email = 'lizhanbin0214@163.com';
        $passwd = '123456';
        $appId = Yii::$app->params['GWServer']['GWAPIAPPID']; 
        $phone = '15625178938';
        $area_code = '';
        $gwapi = Yii::$app->params['GWServer']['GWAPIURL']; 
        $postData = array('email'=>$email,'passwd'=>$passwd,'app_id'=>$appId,'area_code'=>$area_code,'phone'=>$phone); 
        $url = $gwapi."/gwapi/api/accounts/user_all_login"; 
        $data = (new DjiUser)->postGateway($url, $postData); var_dump($data);die;
    }

    /* 
    * 用户登录返回是否需要验证码 http://dev.iuav.dji.com/apimg/logininit
    *  @parameter uuid 登录设备的唯一id
    *  @parameter time  当前时间戳
    *  @parameter os  android,ios
    *  @parameter version  app版本号
    *  @parameter signature 签名 strtoupper(hash_hmac("sha256", $uuid.$time.$os.$version, $hmackey))
    *  return 
    * {"status":0,"status_msg":"ok","now_time":1472718282,"signature":"EC673EBC3AE9C1D52B53A0BE10DF8048FA9F8F89830AF3ECAF503E0D5A9D90B6","message":"","show_code":""}
    *  返回值signature 签名strtoupper(hash_hmac("sha256", $status.$status_msg.$now_time, $returnhmackey))
    * 如果show_code有值表示需要显示验证码
    * "status":200表示参数不足或者不匹配;"status":1001表示参数不合法或者签名不对;"status":1002表示操作过于频繁,5秒后重试;"status":1003表示参数不合法或者不匹配;
    * "status":305表示用户名或者密码错误;"status":311表示用户名或者密码错误;
    */
    public function actionLogininit()
    {
        $uuid = Yii::$app->request->post('uuid', '');
        $time = Yii::$app->request->post('time', '');
        $os = Yii::$app->request->post('os', '');
        $version = Yii::$app->request->post('version', '');
        $signature = Yii::$app->request->post('signature', '');
        $paramsString = $uuid.$time.$os.$version;
        $Djihmac = new Djihmac();
        $status_msg = 'failed';  
        $now_time = time();   

        if (empty($uuid) || empty($signature)) {
            $status = 200;            
            $returnSign =  $Djihmac->getSign($status.$status_msg.$now_time,1);  
            $result = array('status' => $status, 'status_msg'=>$status_msg, 'now_time'=> $now_time,'signature' => $returnSign,'message' => '参数不足或者不匹配',);
            return $result;  
        }
        $nowSign = $Djihmac->getSign($paramsString);
        //echo $nowSign;
        if ($nowSign != $signature) {
            $status = 1001;
            $returnSign =  $Djihmac->getSign($status.$status_msg.$now_time,1);  
            $result = array('paramsString' => $paramsString,'nowSign' => $nowSign,'status' => $status, 'status_msg'=>$status_msg, 'now_time'=> $now_time,'signature' => $returnSign, 'message' => '参数不合法或者签名不对');
            return $result;  
        }
        $djiUser = new DjiUser();
        $show_code = $djiUser->get_captcha($uuid,1);
        $status = 0;
        $status_msg = 'ok';
        $returnSign =  $Djihmac->getSign($status.$status_msg.$now_time,1);
        if ($show_code) {
            $show_code = $Djihmac->getAesEncrypt($show_code,1);
        }
        
        $result = array('status' =>0, 'status_msg'=>'ok', 'now_time'=> $now_time,'signature' => $returnSign, 'message' => '' ,'show_code' => $show_code);
        return $result; 

    }
    /* 
    * 用户登录接口 http://dev.iuav.dji.com/apimg/login
    *  @parameter email 会员账号
    *  @parameter passwd 密码
    *  @parameter captcha 验证码 根据条件是否显示
    *  @parameter uuid 登录设备的唯一id
    *  @parameter time  当前时间戳
    *  @parameter os  android,ios
    *  @parameter version  app版本号
    *  @parameter signature 签名 strtoupper(hash_hmac("sha256", $email.$passwd.$captcha.$uuid.$time.$os.$version, $hmackey))
    *  return 
    * {"status":0,"status_msg":"ok","message":"User login success!","item_total":0,"items":[{"nick_name":"weiping.huang@dji.com","cookie_name":"_meta_key","cookie_key":null,"active":false,"email":"weiping.huang@dji.com","token":"ce709f2fe6284dfd8d0775fe968bd57596434728","validity":0,"user_id":"23617225210722647","register_phone":"","area_code":"","inner_email":false,"subscription":true}]}
    * "status":200表示参数不足或者不匹配;"status":1001表示参数不合法或者签名不对;"status":1002表示操作过于频繁,5秒后重试;"status":1003表示参数不合法或者不匹配;
    * "status":305表示用户名或者密码错误;"status":311表示用户名或者密码错误;
    */
    public function actionLogin()
    {
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'apimg/login');
        }

        $email = Yii::$app->request->post('email', ''); 
        $passwd = Yii::$app->request->post('passwd', ''); 
        $captcha = Yii::$app->request->post('captcha', '');
        $uuid = Yii::$app->request->post('uuid', '');
        $time = Yii::$app->request->post('time', '');
        $os = Yii::$app->request->post('os', '');
        $version = Yii::$app->request->post('version', '');
        $signature = Yii::$app->request->post('signature', '');
        $djiUser = new DjiUser();
        $getStr = json_encode($_REQUEST);
        $now_time = time();
        //写入日志
        $djiUser->add_log($getStr, 'apimg_login');
        $Djihmac = new Djihmac();
        //echo $Djihmac->getAesEncrypt($passwd);exit;
        $status_msg = 'failed';
        if (empty($email) || empty($passwd) || empty($signature)) {
            $status = 200;            
            $returnSign =  $Djihmac->getSign($status.$status_msg.$now_time,1);  
            $result = array('status' => $status, 'status_msg'=>$status_msg, 'now_time'=> $now_time,'signature' => $returnSign,'message' => '参数不足或者不匹配',);
            return $result;  
        }
        $paramsString = $email.$passwd.$captcha.$uuid.$time.$os.$version;
        
        $nowSign = $Djihmac->getSign($paramsString);
        //echo $nowSign; exit;
        if ($nowSign != $signature) {
            $status = 1001;
            $returnSign =  $Djihmac->getSign($status.$status_msg.$now_time,1); 
            if (YII_DEBUG) { 
              $result = array('paramsString' => $paramsString,'nowSign' => $nowSign,'status' => $status, 'status_msg'=>$status_msg, 'now_time'=> $now_time,'signature' => $returnSign, 'message' => '参数不合法或者签名不对');
            }else{
              $result = array('status' => $status, 'status_msg'=>$status_msg, 'now_time'=> $now_time,'signature' => $returnSign, 'message' => '参数不合法或者签名不对');
            }
            return $result;  
        }
        $emailkey = __CLASS__.__FUNCTION__.md5($signature);   
        $emaildata = Yii::$app->cache->get($emailkey);
        if ( $emaildata ) {
            return $emaildata;
        }       
        $luckkey = __CLASS__.__FUNCTION__.'luck_V1_'.md5($signature);   
        $luckdata = Yii::$app->cache->get($luckkey);
        if ( $luckdata ) {           
            $status = 1002;           
            $returnSign =  $Djihmac->getSign($status.$status_msg.$now_time,1);  
            $result = array('status' => $status, 'status_msg'=>$status_msg, 'now_time'=> $now_time,'signature' => $returnSign, 'message' => '操作过于频繁,5秒后重试');
            return $result; 
        }
        Yii::$app->cache->set($luckkey, 1, 5);
        
        if (!$djiUser->validate_is_account($email)) {
          $status = 1003;           
            $returnSign =  $Djihmac->getSign($status.$status_msg.$now_time,1);  
            $result = array('status' => $status, 'status_msg'=>$status_msg, 'now_time'=> $now_time,'signature' => $returnSign, 'message' => '参数不合法或者不匹配');
          return $result; 
        }
        $passwd = $Djihmac->getAesDecrypt($passwd);
        if ($captcha) {
            $captcha = $Djihmac->getAesDecrypt($captcha);
        }
        $result = $djiUser->user_all_login($email,$passwd,'','',$uuid,$captcha);

        if ($result && $result['status'] == '0' && $result['status_msg'] == 'ok' ) {
            $sa_uid = array();
            $sa_uid['uid'] = $result['items']['0']['user_id'];
            $super_admin_level = AgroSuperAdminLevel::getUidLevel($sa_uid);
            $result['account_type'] = $super_admin_level ? intval($super_admin_level) : 0;

            $where = $newItems = array();
            $newItems['user_type'] = 0;
            $newItems['team_info'] = array();
            if (isset($result['items']['0']['user_id'])) {
                $newItems['user_type'] = 2;
                $where['uid'] = $result['items']['0']['user_id'];
                $where['deleted'] = '0';
                $tmp['lock_begin'] = $tmp['lock_end'] = '0';
                $activeInfo = Agroactiveinfo::getAndEqualWhere($where, 0,0);
                if ($activeInfo && is_array($activeInfo)) { //作为老板能控制的飞机
                  $newItems['user_type'] = '1';
                  foreach ($activeInfo as $key => $value) {
                      $newItems['sn'][] = $value['hardware_id'];//老板能控的所有飞机
                      if($value['lock_begin'] && $value['lock_begin'] && strlen($value['lock_begin']) == 10) {
                            $tmp['lock_begin'] = 1000 * mktime(0, 0, 0, substr($value['lock_begin'], 5,2), substr($value['lock_begin'], 8,2), substr($value['lock_begin'], 0,4));
                            $tmp['lock_end'] = 1000 * mktime(0, 0, 0, substr($value['lock_end'], 5,2), substr($value['lock_end'], 8,2), substr($value['lock_end'], 0,4));
                            if($tmp['lock_end'] > time() * 1000 && time() * 1000 > $tmp['lock_begin']) { //判断时间段锁定是否到期
                                $value['locked'] = '1';
                            }
                      }
                      $newItems['ext_sn'][] = array('team_id'=>$value['team_id'], 'flycsn'=>$value['hardware_id'], 'lock_status'=>$value['locked'], 'lock_start'=>$tmp['lock_begin'], 'lock_end'=>$tmp['lock_end']);              
                  }
                } 
                //作为飞手能控制的飞机 （包括老板作为飞手的）
                $whereflyer = array();
                $whereflyer['flyer_uid'] = $result['items']['0']['user_id'];
                $whereflyer['deleted'] = '0';
                $flyerinfo = Agroactiveflyer::getActiveInfoWhere($whereflyer); 
                foreach ($flyerinfo as $key => $value) {
                  $newItems['sn'][] = $value['hardware_id']; 
                  if($value['lock_begin'] && $value['lock_begin'] && strlen($value['lock_begin']) == 10) {
                        $tmp['lock_begin'] = 1000 * mktime(0, 0, 0, substr($value['lock_begin'], 5,2), substr($value['lock_begin'], 8,2), substr($value['lock_begin'], 0,4));
                        $tmp['lock_end'] = 1000 * mktime(0, 0, 0, substr($value['lock_end'], 5,2), substr($value['lock_end'], 8,2), substr($value['lock_end'], 0,4));
                        if($tmp['lock_end'] > time() * 1000 && time() * 1000 > $tmp['lock_begin']) { //判断时间段锁定是否到期
                            $value['locked'] = '1';
                        }
                  }          
                  $newItems['ext_sn'][] = array('team_id'=>$value['team_id'], 'flycsn'=>$value['hardware_id'], 'lock_status'=>$value['locked'], 'lock_start'=>$tmp['lock_begin'], 'lock_end'=>$tmp['lock_end']);
                }

                $fields = 'agro_team.name,agro_team.id,agro_team.app_login_limit';
                $flyerInfo = Agroflyer::getTeamWhere($where,$fields,0,-1);
                if ( $flyerInfo && is_array( $flyerInfo)) {
                    $newItems['team_info'] = $flyerInfo;
                }
                $newItems['nick_name'] = $result['items']['0']['nick_name'];
                $newItems['email'] = $result['items']['0']['email'];
                $newItems['token'] = $result['items']['0']['token'];
                $newItems['user_id'] = $result['items']['0']['user_id'];
                $newItems['country'] = '';
                $newItems['gender'] = '';
                $newItems['avatar'] = '';
           }
           $result['items'] = $newItems;  
           if ($result['show_code']) {
              $result['show_code'] = $Djihmac->getAesEncrypt($result['show_code'],1);
           }   

        }else{
           if ($result['show_code']) {
              $result['show_code'] = $Djihmac->getAesEncrypt($result['show_code'],1);
           }
        }
        $result['now_time'] = $now_time;
        $result['signature'] =  $Djihmac->getSign($result['status'].$result['status_msg'].$result['now_time'],1);
        if (!isset($result['account_type'])) {
            $result['account_type'] = 0;
        }
        return $result;
    }
    /* 
    *  退出登录 http://dev.iuav.dji.com/apimg/logout
    *  @parameter token 登录令牌
    *  @parameter uuid 登录设备的唯一id
    *  @parameter time  当前时间戳
    *  @parameter os  android,ios
    *  @parameter version  app版本号
    *  @parameter signature 签名 strtoupper(hash_hmac("sha256", $token.$uuid.$time.$os.$version, $hmackey))
    *  return 
    * {"status":0,"status_msg":"ok","message":"User logout success!" }
    * "status":200表示参数不足或者不匹配;"status":1001表示参数不合法或者签名不对;
    */
    public function actionLogout()
    {
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'apimg/logout');
        }
        $token = Yii::$app->request->post('token', '');
        $uuid = Yii::$app->request->post('uuid', '');
        $time = Yii::$app->request->post('time', '');
        $os = Yii::$app->request->post('os', '');
        $version = Yii::$app->request->post('version', '');
        $signature = Yii::$app->request->post('signature', '');
        $djiUser = new DjiUser();
        $getStr = json_encode($_REQUEST);
        //写入日志
        $djiUser->add_log($getStr, 'apimg_logout');
        if (empty($token) || empty($signature)) {
            $status = 200;           
            $result = array('status' => $status, 'status_msg'=>'failed', 'message' => '参数不足或者不匹配');
            return $result;  
        }
        $paramsString = $token.$uuid.$time.$os.$version;
        $Djihmac = new Djihmac();
        $nowSign = $Djihmac->getSign($paramsString);
        //echo $nowSign;
        if ($nowSign != $signature) {
            $status = 1001;           
            $result = array('status' => $status, 'status_msg'=>'failed', 'message' => '参数不合法或者签名不对');
            return $result;  
        }       
        $result =array('status' => 0, 'status_msg'=>'ok', 'message' => 'User logout success!');     
        return $result;        
    } 

    /* 
    *  忘记密码 http://dev.iuav.dji.com/apimg/resetpasswd
    *  @parameter email 登录邮箱
    *  @parameter uuid 登录设备的唯一id
    *  @parameter time  当前时间戳
    *  @parameter os  android,ios
    *  @parameter version  app版本号
    *  @parameter signature 签名 strtoupper(hash_hmac("sha256", $token.$uuid.$time.$os.$version, $hmackey))
    *  return 
    * {"status":0,"status_msg":"ok","message":"User resetpasswd success!" } 提示会给用户发送一个邮件
    * "status":200表示参数不足或者不匹配;"status":1001表示参数不合法或者签名不对;"status":1002表示操作过于频繁,5秒后重试;
    */
    public function actionResetpasswd()
    {
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'apimg/resetpasswd');
        }
        $email = Yii::$app->request->post('email', '');
        $uuid = Yii::$app->request->post('uuid', '');
        $time = Yii::$app->request->post('time', '');
        $os = Yii::$app->request->post('os', '');
        $version = Yii::$app->request->post('version', '');
        $signature = Yii::$app->request->post('signature', '');
        $djiUser = new DjiUser();
        $getStr = json_encode($_REQUEST);
        //写入日志
        $djiUser->add_log($getStr, 'apimg_resetpasswd');
        if (empty($token) || empty($signature)) {
            $status = 200;           
            $result = array('status' => $status, 'status_msg'=>'failed', 'message' => '参数不足或者不匹配');
            return $result;  
        }
        $paramsString = $token.$uuid.$time.$os.$version;
        $Djihmac = new Djihmac();
        $nowSign = $Djihmac->getSign($paramsString);
        //echo $nowSign;
        if ($nowSign != $signature) {
            $status = 1001;           
            $result = array('status' => $status, 'status_msg'=>'failed', 'message' => '参数不合法或者签名不对');
            return $result;  
        }
        $luckkey = __CLASS__.__FUNCTION__.'luck_'.md5($signature);   
        $luckdata = Yii::$app->cache->get($luckkey);
        if ( $luckdata ) {           
            $status = 1002;           
            $result = array('status' => $status, 'status_msg'=>'failed', 'message' => '操作过于频繁,5秒后重试');
            return $result; 
        }
        Yii::$app->cache->set($luckkey, 1, 5);
        $result =array('status' => 0, 'status_msg'=>'ok', 'message' => 'User resetpasswd success!');     
        return $result;        
    }
    /*
    * 验证APP所连接的飞行器是否是该企业账户旗下的 http://dev.iuav.dji.com/apimg/checksn
    *  @parameter sn 硬件id
    *  @parameter token 登录令牌
    *  @parameter uuid 登录设备的唯一id
    *  @parameter time  当前时间戳
    *  @parameter os  android,ios
    *  @parameter version  app版本号
    *  @parameter signature 签名 strtoupper(hash_hmac("sha256", $sn.$token.$uuid.$time.$os.$version, $hmackey))
    *  return 
    * {"status":0,"status_msg":"ok","message":"飞机正常"}
    * "status":200表示参数不足或者不匹配;"status":1001表示参数不合法或者签名不对;
    */
    public function actionChecksn()
    {
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'apimg/checksn');
        }
        $sn = Yii::$app->request->post('sn', '');
        $token = Yii::$app->request->post('token', '');
        $uuid = Yii::$app->request->post('uuid', '');
        $time = Yii::$app->request->post('time', '');
        $os = Yii::$app->request->post('os', '');
        $version = Yii::$app->request->post('version', '');
        $signature = Yii::$app->request->post('signature', '');
        $djiUser = new DjiUser();
        $getStr = json_encode($_REQUEST);
        //写入日志
        $djiUser->add_log($getStr, 'apimg_resetpasswd');
        if (empty($token) || empty($signature) || empty($sn) ) {
            $status = 200;           
            $result = array('status' => $status, 'status_msg'=>'failed', 'message' => '参数不足或者不匹配');
            return $result;  
        }        
        $paramsString = $sn.$token.$uuid.$time.$os.$version;
        $returnKey = __CLASS__.__FUNCTION__."_data_".md5($sn.$token);   
        $returnData = Yii::$app->cache->get($returnKey);
        if ( $returnData ) {
           return $returnData;
        }      
        $userData = $this->getUserInfo($signature,$paramsString,$token);
        if ($userData && $userData['status'] == '0' && $userData['status_msg'] == 'ok') {
          //var_dump($userData);exit;
          $where = array();
          $where['hardware_id'] = $sn;          
          $where['deleted'] = '0';
          $activeInfo = Agroactiveinfo::getAndEqualWhere($where, 0,1);
          if ($activeInfo && is_array($activeInfo)) {
             if ($activeInfo['0']['locked'] == 1 ) {
                 $status = 3001;           
                 $result = array('status' => $status, 'status_msg'=>'failed', 'message' => '飞机已经锁定');
                 Yii::$app->cache->set($returnKey, $result, 120);
                 return $result;  
             }else if ($activeInfo['0']['uid'] != $userData['items']['0']['account_info']['user_id'] ) {
                 $where['flyer_uid'] = $userData['items']['0']['account_info']['user_id'];
                 $where['active_id'] = $activeInfo['0']['id'];
                 $where['team_id'] = $activeInfo['0']['team_id'];                
                 $fields = 'agro_flyer.id as flyerid';
                 $flyerInfo = Agroactiveflyer::getFlyerWhere($where,$fields,0, -1);
                 if (empty($flyerInfo)) {
                     $status = 3002;           
                     $result = array('status' => $status, 'status_msg'=>'ok', 'message' => '飞手权限不足');
                     Yii::$app->cache->set($returnKey, $result, 120);
                     return $result;   
                 }
             }
             $status = 0;           
             $result = array('status' => $status, 'status_msg'=>'ok', 'message' => '飞机正常');
             Yii::$app->cache->set($returnKey, $result, 600);
             return $result;     
          }
          $status = 3004;           
          $result = array('status' => $status, 'status_msg'=>'ok', 'message' => '飞机未激活');
          Yii::$app->cache->set($returnKey, $result, 120);
          return $result;   

          
        }         
        return $userData;        
    }

    /*
    *  同步飞行任务：获取所归属的team的某一时间内的飞行任务 http://dev.iuav.dji.com/apimg/listtask
    *  @parameter team_id 团队id
    *  @parameter starttime 开始时间 2016-01-01
    *  @parameter endtime 结束时间   2016-01-01
    *  @parameter string page  页面 
    *  @parameter string size  每页数目 
    *  @parameter token 登录令牌
    *  @parameter uuid 登录设备的唯一id
    *  @parameter time  当前时间戳
    *  @parameter os  android,ios
    *  @parameter version  app版本号
    *  @parameter signature 签名 strtoupper(hash_hmac("sha256", $team_id.$starttime.$endtime.$page.$size.$token.$uuid.$time.$os.$version, $hmackey))
    *  return 
    *  {"status":0,"status_msg":"ok","message":"","data":""}
    * "status":200表示参数不足或者不匹配;"status":1001表示参数不合法或者签名不对;
    */
    public function actionListtask()
    {
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'apimg/listtask');
        }
        $team_id = Yii::$app->request->post('team_id', '');
        $starttime = Yii::$app->request->post('starttime', '');
        $endtime = Yii::$app->request->post('endtime', '');
        $page = intval(Yii::$app->request->post('page', ''));
        $size = intval(Yii::$app->request->post('size', ''));
        $token = Yii::$app->request->post('token', '');
        $uuid = Yii::$app->request->post('uuid', '');
        $time = Yii::$app->request->post('time', '');
        $os = Yii::$app->request->post('os', '');
        $version = Yii::$app->request->post('version', '');
        $signature = Yii::$app->request->post('signature', '');
        $djiUser = new DjiUser();
        $getStr = json_encode($_REQUEST);
        
        //写入日志
        $djiUser->add_log($getStr, 'apimg_listtask');

        if (empty($token) || empty($signature) ) {
            return array('status' => 200, 'status_msg'=>'failed', 'message' => '参数不足或者不匹配');  
        }        
        $paramsString = $team_id.$starttime.$endtime.$page.$size.$token.$uuid.$time.$os.$version;  
        $userData = $this->getUserInfo($signature,$paramsString,$token);
        if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
            return $userData;
        }
    
        if ($size < 1 || $size > 100 ) {
            $size = 30;
        }
        if ($page < 1 ) {
            $page = 1;
        }
        $start = ($page-1) * $size;
        $start = $start < 0 ? 0 : $start ;   
        $share = array();   
        $where = array();                          

        //列出分享而来的任务
        $whereShare['team_id'] = $team_id;
        $whereShare['deleted'] = '0'; 
        $fields = 'agro_task.id,name,date,time,type,crop,crop_stage,prevent,lat,lng,location,battery_times,interval,share';
        $shareTask = Agroteamtask::getShareTaskWhere($whereShare, 0, 0, 'id', 1, $fields); 
        foreach ($shareTask as $key => $value) {
            $value['share'] = '1';
            $share[] = $value;
        }

        //$where['starttime'] = strtotime($starttime." 00:00:00");
        //$where['endtime'] = strtotime($endtime." 23:59:59");
        
        //该团队规划的任务
        $where['team_id'] = $team_id;
        $where['deleted'] = '0';
        $fields = 'id,name,date,time,type,crop,crop_stage,prevent,lat,lng,location,battery_times,interval,share';
        $taskInfo = Agrotask::getDateWhere($where, $fields, $start, $size);   
        if($share) {
            $taskInfo = array_merge_recursive($taskInfo, $share);
        }     
        $result = array('status' => 0, 'status_msg'=>'ok', 'message' => ''); 
        $result['data'] = $taskInfo;

        return $result;             
    }

    /*
    *  飞行(作业)任务：获取所归属的team的飞行任务详情 http://dev.iuav.dji.com/apimg/taskinfo
    *  @parameter team_id 团队id
    *  @parameter taskid task表id
    *  @parameter token 登录令牌
    *  @parameter uuid 登录设备的唯一id
    *  @parameter time  当前时间戳
    *  @parameter os  android,ios
    *  @parameter version  app版本号
    *  @parameter signature 签名 strtoupper(hash_hmac("sha256", $team_id.$taskid.$token.$uuid.$time.$os.$version, $hmackey))
    *  return 
    * {"status":0,"status_msg":"ok","message":"","data":""}
    * "status":200表示参数不足或者不匹配;"status":1001表示参数不合法或者签名不对;
    */
    public function actionTaskinfo()
    {
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'apimg/taskinfo');
        }
        $team_id = Yii::$app->request->post('team_id', '');
        $taskid = Yii::$app->request->post('taskid', '');
        $token = Yii::$app->request->post('token', '');
        $uuid = Yii::$app->request->post('uuid', '');
        $time = Yii::$app->request->post('time', '');
        $os = Yii::$app->request->post('os', '');
        $version = Yii::$app->request->post('version', '');
        $signature = Yii::$app->request->post('signature', '');
        $djiUser = new DjiUser();
        $getStr = json_encode($_REQUEST);
        //写入日志
        $djiUser->add_log($getStr, 'apimg_taskinfo');
        if (empty($token) || empty($signature) || empty($taskid)) {
            $status = 200;           
            $result = array('status' => $status, 'status_msg'=>'failed', 'message' => '参数不足或者不匹配');
            return $result;  
        }        
        $paramsString = $team_id.$taskid.$token.$uuid.$time.$os.$version;
        $returnKey = __CLASS__.__FUNCTION__."_data_".md5($taskid.$token);   
        $returnData = Yii::$app->cache->get($returnKey);
        if ( $returnData ) {
           return $returnData;
        }      
        $userData = $this->getUserInfo($signature,$paramsString,$token,0);

        if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
            return $userData;
        }
     
        $where = array();
        $where['uid'] = $userData['items']['0']['account_info']['user_id'];
        $where['deleted'] = '0';
        if ($team_id == '0') {               
            $activeInfo = Agroactiveinfo::getAndEqualWhere($where, 0,1);
            if ($activeInfo && is_array($activeInfo)) {
                $where = array();
                $where['upper_uid'] = $userData['items']['0']['account_info']['user_id'];
                $where['deleted'] = '0';
            } else {                 
                $status = 3002;           
                $result = array('status' => $status, 'status_msg'=>'ok', 'message' => '飞手权限不足');
                Yii::$app->cache->set($returnKey, $result, 120);
                return $result;  
            }
        } else {                
             $where['team_id'] = $team_id;                             
             $flyerInfo = Agroflyer::getAndEqualWhere($where,0,1);
             if ( $flyerInfo && is_array( $flyerInfo) ) {
                if ($flyerInfo['0']['upper_uid'] == $flyerInfo['0']['uid']) {
                    $where = array();
                    $where['upper_uid'] = $userData['items']['0']['account_info']['user_id'];
                    $where['deleted'] = '0';
                } else {
                    $where = array();
                    $where['team_id'] = $team_id; 
                    $where['deleted'] = '0';
                }                  
             } else {
                $status = 3002;           
                $result = array('status' => $status, 'status_msg'=>'ok', 'message' => '飞手权限不足');
                Yii::$app->cache->set($returnKey, $result, 60);
                return $result;  
             }
        }
        $whereTask['deleted'] = '0';
        $whereTask['id'] = $taskid;          
        $taskInfo = Agrotask::getAndEqualWhere($whereTask,0,1); 
        if ($taskInfo && is_array($taskInfo)) {
            $where = array();
            $where['uid'] = $taskInfo['0']['uid'];
            $where['upper_uid'] = $taskInfo['0']['upper_uid'];
            $flyerInfo = Agroflyer::getUidData($where,'nickname,realname,avatar');
            if ($flyerInfo && is_array($flyerInfo)) {
                $taskInfo['0']['nickname'] = $flyerInfo['0']['nickname'];
                //$taskInfo['0']['realname'] = $flyerInfo['0']['realname'];
                //$taskInfo['0']['avatar'] = $flyerInfo['0']['avatar'];
            }
        }
        $status = 0;           
        $result = array('status' => $status, 'status_msg'=>'ok', 'message' => '','data' => $taskInfo);
        Yii::$app->cache->set($returnKey, $result, 120);
        return $result;               
    }

    /*
    *  飞行(作业)任务：添加或者编辑所归属的team的飞行任务详情 http://dev.iuav.dji.com/apimg/addtask
    *  @parameter team_id 团队id
    *  @parameter taskjson 或者为json [{"id":0,"name":"Qv"},{"id":0,"name":"Qdsfdsv"}]
    *  @parameter id task表id 为0表示新增，其他表示修改任务详情  ----json开始
    *  @parameter name 任务名称
    *  @parameter date 创建时间
    *  @parameter area 此次任务的面积    
    *  @parameter time 此次任务所用的时间
    *  @parameter type 任务类型
    *  @parameter crop 农作物
    *  @parameter crop_stage 生育时期
    *  @parameter prevent 防治对象
    *  @parameter radar_height
    *  @parameter spray_flow
    *  @parameter work_speed
    *  @parameter spray_width  
    *  @parameter key_point 航点的关键点，如A,B点
    *  @parameter home 多个返航点的坐标
    *  @parameter obstacle_point 一个或多个障碍物的相关坐标点
    *  @parameter plan_edge_poit 规划边缘航点
    *  @parameter edge_poit  边缘航点
    *  @parameter way_point  航点
    *  @parameter lat  纬度
    *  @parameter lng  经度
    *  @parameter location  执行本次任务的地点   
    *  @parameter battery_times  更换电池次数
    *  @parameter interval  自动作业时的作业间距
    *  @parameter calibrate_point 标定点坐标 L
    *  @parameter app_type  Android or ios ----json结束
    *  @parameter token 登录令牌
    *  @parameter uuid 登录设备的唯一id
    *  @parameter time  当前时间戳
    *  @parameter os  android,ios
    *  @parameter version  app版本号
    *  @parameter signature 签名 strtoupper(hash_hmac("sha256", $team_id.$token.$uuid.$time.$os.$version, $hmackey))
    *  return 
    * {"status":0,"status_msg":"ok","message":"","data":""}
    * "status":200表示参数不足或者不匹配;"status":1001表示参数不合法或者签名不对;
    */
    public function actionAddtask()
    {
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'apimg/addtask');
        }
        $team_id = Yii::$app->request->post('team_id', '');       
        $token = Yii::$app->request->post('token', '');
        $uuid = Yii::$app->request->post('uuid', '');
        $time = Yii::$app->request->post('time', '');
        $os = Yii::$app->request->post('os', '');
        $version = Yii::$app->request->post('version', '');
        $signature = Yii::$app->request->post('signature', '');
        $taskid = Yii::$app->request->post('id', '');
        $taskjson = Yii::$app->request->post('taskjson', '');
        $model = array();
        $model['name'] = Yii::$app->request->post('name', '');
        $model['date'] = Yii::$app->request->post('date', '');
        $model['time'] = Yii::$app->request->post('time', '');
        $model['type'] = Yii::$app->request->post('type', '');
        $model['crop'] = Yii::$app->request->post('crop', '');
        $model['crop_stage'] = Yii::$app->request->post('crop_stage', '');
        $model['prevent'] = Yii::$app->request->post('prevent', '');
        $model['radar_height'] = Yii::$app->request->post('radar_height', '');
        $model['spray_flow'] = Yii::$app->request->post('spray_flow', '');
        $model['work_speed'] = Yii::$app->request->post('work_speed', '');
        $model['spray_width'] = Yii::$app->request->post('spray_width', '');
        $model['key_point'] = Yii::$app->request->post('key_point', '');
        $model['home'] = Yii::$app->request->post('home', '');
        $model['obstacle_point'] = Yii::$app->request->post('obstacle_point', '');
        $model['plan_edge_poit'] = Yii::$app->request->post('plan_edge_poit', '');
        $model['edge_poit'] = Yii::$app->request->post('edge_poit', '');
        $model['way_point'] = Yii::$app->request->post('way_point', '');
        $model['lat'] = Yii::$app->request->post('lat', '');
        $model['lng'] = Yii::$app->request->post('lng', '');
        $model['location'] = Yii::$app->request->post('location', '');
        $model['battery_times'] = Yii::$app->request->post('battery_times', '');
        $model['interval'] = Yii::$app->request->post('interval', '');
        $model['calibrate_point'] = Yii::$app->request->post('calibrate_point', '');
        //新增协议字段 2017.02.16
        $model['spraying_dir'] = Yii::$app->request->post('spraying_dir', '');
        $model['have_break_info'] = Yii::$app->request->post('have_break_info', '');
        $model['last_spraying_break_dir'] = Yii::$app->request->post('last_spraying_break_dir', '');
        $model['last_spraying_break_index'] = Yii::$app->request->post('last_spraying_break_index', '');
        $model['last_spraying_break_point'] = Yii::$app->request->post('last_spraying_break_point', '');

        $model['app_type'] = Yii::$app->request->post('app_type', '');
        $model['team_id'] = $team_id;
        $model['isInGeo'] = Yii::$app->request->post('isInGeo', '');
        $model['geoStartTime'] = Yii::$app->request->post('geoStartTime', '');
        $model['geoEndTime'] = Yii::$app->request->post('geoEndTime', '');

        $djiUser = new DjiUser();
        $getStr = json_encode($_REQUEST);
        //写入日志
        $djiUser->add_log($getStr, 'apimg_addtask');
        if (empty($token) || empty($signature) ) {
            $status = 200;           
            $result = array('status' => $status, 'status_msg'=>'failed', 'message' => '参数不足或者不匹配');
            return $result;  
        }        
        $paramsString = $team_id.$token.$uuid.$time.$os.$version;
        $returnKey = __CLASS__.__FUNCTION__."_data_".md5($token);   
        $returnData = Yii::$app->cache->get($returnKey);
        if ( $returnData ) {
           return $returnData;
        }      
        $userData = $this->getUserInfo($signature,$paramsString,$token);
        //var_dump($userData);
        if ($userData && $userData['status'] == '0' && $userData['status_msg'] == 'ok') {
            $model['uid'] = $userData['items']['0']['account_info']['user_id'];
            $where = array();
            $where['uid'] = $userData['items']['0']['account_info']['user_id'];
            $where['deleted'] = '0';
            $phone = 0;
            $flyerName['flyerid'] = $where['uid'];
            $flyerName['deleted'] = '0';
            $flyerName['teamid'] = $team_id;
            $model['operator'] = Agroflyer::getNameByID($flyerName);
            if ($team_id == '0') { //如果是老板              
                $activeInfo = Agroactiveinfo::getAndEqualWhere($where, 0,1);
                if ($activeInfo && is_array($activeInfo)) {                   
                    $model['upper_uid'] = $userData['items']['0']['account_info']['user_id']; 
                    $phone = $activeInfo['0']['phone'];                  
                }else{                 
                    $status = 3002;           
                    $result = array('status' => $status, 'status_msg'=>'ok', 'message' => '飞手权限不足');
                    Yii::$app->cache->set($returnKey, $result, 120);
                    return $result;  
                }
            }else{                
                 $where['team_id'] = $team_id;                             
                 $flyerInfo = Agroflyer::getAndEqualWhere($where,0,1);
                 if ( $flyerInfo && is_array( $flyerInfo) ) {
                    $model['upper_uid'] = $flyerInfo['0']['upper_uid'];
                    $where = array();
                    $where['uid'] = $model['upper_uid'];
                    $where['deleted'] = '0';
                    $activeInfo = Agroactiveinfo::getAndEqualWhere($where, 0,1); 
                    if ($activeInfo && is_array($activeInfo)) {                   
                        $phone = $activeInfo['0']['phone'];  
                    }                    
                 }else{
                    $status = 3002;           
                    $result = array('status' => $status, 'status_msg'=>'ok', 'message' => '飞手权限不足');
                    Yii::$app->cache->set($returnKey, $result, 60);
                    return $result;  
                 }
            }
            $taskResultArray = array();
            if ($taskid > 0) {  //修改任务
                $where = array();
                $where['id'] = $taskid;
                $where['team_id'] = $team_id;
                $where['upper_uid'] = $model['upper_uid'];          
                $taskInfo = Agrotask::getAndEqualWhere($where,0,1);
                if ($taskInfo) {
                    $taskResult = Agrotask::updateInfo($model);
                }else{
                    $model['team_id'] = $team_id;
                    $model['upper_uid'] = $model['upper_uid'];                    
                    $taskResult = Agrotask::add($model);
                }
                $taskResultArray[] = $taskResult;
            } else if ($taskjson) {  //新增任务
                //echo $taskjson;exit;
                $taskArray = json_decode($taskjson,true);               
                foreach ($taskArray as $key => $value) {
                    $value['operator'] = $model['operator'];
                    if (isset($value['id']) && $value['id'] > 0) {
                        $where = array();
                        $where['id'] = $value['id'];
                        $where['team_id'] = $team_id;
                        $where['upper_uid'] = $model['upper_uid'];          
                        $taskInfo = Agrotask::getAndEqualWhere($where,0,1);
                        if ($taskInfo) {
                            $taskResult = Agrotask::updateInfo($value);
                            $taskResultArray[] = array('id' =>$value['id'],'reid' => $taskResult );
                        }else{
                            $value['team_id'] = $team_id;
                            $value['uid'] = $model['uid']; 
                            $value['upper_uid'] = $model['upper_uid']; 
                            if (empty($value['location']) && $value['lng'] && $value['lat']) {                  
                              $value['location'] = $djiUser->regeo($value['lng'],$value['lat']) ;
                            }                     
                            $taskResult = Agrotask::add($value);
                            $taskResultArray[] = array('reid' => $taskResult );
                        }
                    }else{
                        $value['team_id'] = $team_id;
                        $value['uid'] = $model['uid'];     
                        $value['upper_uid'] = $model['upper_uid'];
                        if (empty($value['location']) && $value['lng'] && $value['lat']) {                  
                           $value['location'] = $djiUser->regeo($value['lng'],$value['lat']) ;
                        }      
                        $taskResult = Agrotask::add($value);
                        $taskResultArray[] = array('reid' => $taskResult );
                    }                                     
                }
            }else{
                $model['team_id'] = $team_id;
                $model['upper_uid'] = $model['upper_uid'];
                if (empty($model['location']) && $model['lng'] && $model['lat']) {                  
                     $model['location'] = $djiUser->regeo($model['lng'],$model['lat']) ;
                }                               
                $taskResult = Agrotask::add($model);
                $taskResultArray[] = $taskResult;
            }
            if($model['isInGeo'] && $model['isInGeo'] == 1) { //如果该任务处在限飞区，则需要申请解禁，并向老板发送短信。
                $start_time = $model['geoStartTime'];
                $end_time = $model['geoEndTime'];
                $name = $model['name'];
                $text = "任务 $name 申请解除禁飞区域，时间为 $start_time 到 $end_time ，请登录管理平台处理该事件";
                $return = $this->sendmsg($phone, $text);
                if($return['status'] != 0) {
                    $result = array('status' => 1007, 'status_msg'=>'failed', 'message' => $return['detail']);
                    return $result;
                }
            } 
            $status = 0;           
            $result = array('status' => $status, 'status_msg'=>'ok', 'message' => '','data' => $taskResultArray);
            return $result;       
        }         
        return $userData;        
    }
    protected function sendmsg($phone, $text)
    {
        $status_msg = 'failed';
        $apikey = "b010bf811f9266567ddda76e4cd81fb1"; //修改为您的apikey(https://www.yunpian.com)登陆官网后获取
        $ch = curl_init();
        //设置验证方式 
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept:text/plain;charset=utf-8', 'Content-Type:application/x-www-form-urlencoded','charset=utf-8'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);// 设置返回结果为流
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);//设置超时时间
        curl_setopt($ch, CURLOPT_POST, 1);// 设置通信方式 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // 发送短信
        $data=array('text'=>$text,'apikey'=>$apikey,'mobile'=>$phone);
        $json_data = $this->send($ch,$data);
        (new DjiUser)->add_log($json_data, 'limited_sendmssg');
        $array = json_decode($json_data, true);
        if(isset($array['http_status_code']) && $array['http_status_code'] != 0) {
            $data = array('status' => 1008, 'status_msg'=> $status_msg,'message'=>$array['detail']);
            curl_close($ch);
            return $data;
        }
        curl_close($ch);
        $data = array('status' => 0, 'status_msg'=> 'ok','message'=>'发送成功');
        return $data;
    }
    protected function send($ch,$data){
        curl_setopt ($ch, CURLOPT_URL, 'https://sms.yunpian.com/v2/sms/single_send.json');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        return curl_exec($ch);
    }
    /*
    *  查询飞行器相关信息 http://dev.iuav.dji.com/apimg/hardwareinfo
    *  @parameter sn 硬件id(飞控id)   
    *  @parameter token 登录令牌
    *  @parameter uuid 登录设备的唯一id
    *  @parameter time  当前时间戳
    *  @parameter os  android,ios
    *  @parameter version  app版本号
    *  @parameter signature 签名 strtoupper(hash_hmac("sha256", $sn.$token.$uuid.$time.$os.$version, $hmackey))
    *  return 
    * {"status":0,"status_msg":"ok","message":"","data":""}
    * "status":200表示参数不足或者不匹配;"status":1001表示参数不合法或者签名不对;
    */
    public function actionHardwareinfo()
    {
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'apimg/hardwareinfo');
        }
        $sn = Yii::$app->request->post('sn', '');       
        $token = Yii::$app->request->post('token', '');
        $uuid = Yii::$app->request->post('uuid', '');
        $time = Yii::$app->request->post('time', '');
        $os = Yii::$app->request->post('os', '');
        $version = Yii::$app->request->post('version', '');
        $signature = Yii::$app->request->post('signature', '');
        $djiUser = new DjiUser();
        $getStr = json_encode($_REQUEST);

        //写入日志
        $djiUser->add_log($getStr, 'apimg_hardwareinfo');
        if (empty($token) || empty($signature) ) {
            $status = 200;           
            $result = array('status' => $status, 'status_msg'=>'failed', 'message' => '参数不足或者不匹配');
            return $result;  
        }        

        $paramsString = $sn.$token.$uuid.$time.$os.$version;
        $returnKey = __CLASS__.__FUNCTION__."_data_".md5($sn.$token);   
        $returnData = Yii::$app->cache->get($returnKey);
        if ( $returnData ) {
           return $returnData;
        }      

        $userData = $this->getUserInfo($signature,$paramsString,$token);
        if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
            return $userData;
        }

        $user_id = $userData['items']['0']['account_info']['user_id'];          
        $where = array();          
        $where['deleted'] = '0';
        $where['hardware_id'] = $sn;
        $activeInfo = Agroactiveinfo::getAndEqualWhere($where, 0,1);
        if (!$activeInfo) {
            $status = 3002;           
            $result = array('status' => $status, 'status_msg'=>'ok', 'message' => '权限不足');
            Yii::$app->cache->set($returnKey, $result, 120);
            return $result; 
        }

        if ($activeInfo['0']['uid'] != $user_id) { 
            $where['flyer_uid'] = $userData['items']['0']['account_info']['user_id'];
            $where['active_id'] = $activeInfo['0']['id'];
            $where['team_id'] = $activeInfo['0']['team_id'];                
            $fields = 'agro_flyer.id as flyerid';
            $flyerInfo = Agroactiveflyer::getFlyerWhere($where,$fields,0, -1);
            if (empty($flyerInfo)) {
                 $status = 3002;           
                 $result = array('status' => $status, 'status_msg'=>'ok', 'message' => '权限不足');
                 Yii::$app->cache->set($returnKey, $result, 120);
                 return $result;   
            }
        }
        $activeResult = array();
        $activeResult['pol_no'] = $activeInfo['0']['pol_no'];
        $activeResult['exp_tm'] = $activeInfo['0']['exp_tm'];
        //$activeResult['exp_tm'] = substr($activeResult['exp_tm'], 0,4).'-'.substr($activeResult['exp_tm'], 4,2).'-'.substr($activeResult['exp_tm'], 6,2);
        $activeResult['nickname'] = $activeInfo['0']['nickname'];
        $activeResult['created_at'] = $activeInfo['0']['created_at'];
        $activeResult['locked'] = $activeInfo['0']['locked'];
        $nameinfo = Agroflyer::findOne(['upper_uid'=>$activeInfo['0']['uid'], 'uid'=>$activeInfo['0']['uid'], 'deleted'=>'0']);
        if ($nameinfo) $activeResult['bossname'] = $nameinfo->account;
        else $activeResult['bossname'] = '';
      
        $status = 0;           
        $result = array('status' => $status, 'status_msg'=>'ok', 'message' => '','data' => $activeResult);
        Yii::$app->cache->set($returnKey, $result, 120);
        return $result;       
      
    }

    /*
    *  查询飞手相关信息 http://dev.iuav.dji.com/apimg/flyerinfo
    *  @parameter team_id 团队id   
    *  @parameter token 登录令牌
    *  @parameter uuid 登录设备的唯一id
    *  @parameter time  当前时间戳
    *  @parameter os  android,ios
    *  @parameter version  app版本号
    *  @parameter signature 签名 strtoupper(hash_hmac("sha256", $team_id.$token.$uuid.$time.$os.$version, $hmackey))
    *  return 
    * {"status":0,"status_msg":"ok","message":"","data":""}
    * "status":200表示参数不足或者不匹配;"status":1001表示参数不合法或者签名不对;
    */
    public function actionFlyerinfo()
    {
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'apimg/flyerinfo');
        }
        $team_id = Yii::$app->request->post('team_id', '');       
        $token = Yii::$app->request->post('token', '');
        $uuid = Yii::$app->request->post('uuid', '');
        $time = Yii::$app->request->post('time', '');
        $os = Yii::$app->request->post('os', '');
        $version = Yii::$app->request->post('version', '');
        $signature = Yii::$app->request->post('signature', '');
        $djiUser = new DjiUser();
        $getStr = json_encode($_REQUEST);
        //写入日志
        $djiUser->add_log($getStr, 'apimg_flyerinfo');
        if (empty($token) || empty($signature) ) {
            $status = 200;           
            $result = array('status' => $status, 'status_msg'=>'failed', 'message' => '参数不足或者不匹配');
            return $result;  
        }        
        $paramsString = $team_id.$token.$uuid.$time.$os.$version;
        $returnKey = __CLASS__.__FUNCTION__."_data_".md5($team_id.$token);   
        $returnData = Yii::$app->cache->get($returnKey);
        if ( $returnData ) {
           return $returnData;
        }
        $userData = $this->getUserInfo($signature,$paramsString,$token);
        if ($userData && $userData['status'] == '0' && $userData['status_msg'] == 'ok') {  
            $user_id = $userData['items']['0']['account_info']['user_id'];          
            $where = $flyerResult = array();          
            $where['deleted'] = '0';
            $where['uid'] = $user_id;
            if ( $team_id > 0 ) {
                $where['team_id'] = $team_id;
            }
            $fields = 'agro_flyer.*,agro_team.name as teamName';            
            $flyerInfo = Agroflyer::getTeamWhere($where,$fields,0,-1);
            if ($flyerInfo && is_array($flyerInfo)) {
                $flyerResult['realname'] = $flyerInfo['0']['realname']; 
                $flyerResult['idcard'] = $flyerInfo['0']['idcard']; 
                $flyerResult['phone'] = $flyerInfo['0']['phone']; 
                $flyerResult['job_level'] = $flyerInfo['0']['job_level']; 
                $flyerResult['address'] = $flyerInfo['0']['address']; 
                //飞手作业信息
                $workInfo = Agroflyerworkinfo::getAndEqualWhere($where, 'sum(all_time) as time, sum(all_area) as area, sum(all_times) as times');
                $flyerResult['all_time'] = isset($workInfo['0']['time']) ? $workInfo['0']['time'] : 0;
                $flyerResult['all_area'] = isset($workInfo['0']['area']) ? $workInfo['0']['area'] : 0;
                $flyerResult['all_times'] = isset($workInfo['0']['times']) ? $workInfo['0']['times'] : 0;

                $flyerResult['upper_uid'] = $flyerInfo['0']['upper_uid'];           
            }
            $flyerResult['nick_name'] = $userData['items']['0']['account_info']['nick_name'];
            $flyerResult['email'] = $userData['items']['0']['account_info']['email'];
            $flyerResult['user_id'] = $userData['items']['0']['account_info']['user_id'];
            $flyerResult['country'] = '';
            $flyerResult['gender'] = '';
            $flyerResult['avatar'] = '';     
            $flyerResult['update_time'] = time();   
            $status = 0;           
            $result = array('status' => $status, 'status_msg'=>'ok', 'message' => '','data' => $flyerResult);
            Yii::$app->cache->set($returnKey, $result, 120);
            return $result;       
        }         
        return $userData;        
    }

    /*
    *  离线飞行记录上传：http://dev.iuav.dji.com/apimg/addflight  
    *  @parameter team_id 团队id  登录用户的团队id
    *  @parameter flightjson 或者为json [{"team_id":0,"user_id":"23617225210722647","version":"1","timestamp":1473763277970,"longi":113.9589026061317,"lati":22.54281553502602,"alti":0,"product_sn":"test26","spray_flag":0,"motor_status":0,"radar_height":0,"velocity_x":0,"velocity_y":0,"farm_delta_y":0,"farm_mode":2,"pilot_num":0,"session_num":1473763277970,"frame_index":2,"frame_flag":1,"flight_version":"3.2.10.255","plant":0,"work_area":0,"inside_signature":"8CB5B6BDEFC129AFDD94D3F5B0EC2F852EAD8AF9736016E7E23A03F65138E29C"}]
    *  @parameter team_id 团队id  ----json开始
    *  @parameter user_id 飞手id
    *  @parameter version 协议版本
    *  @parameter timestamp 飞行时间戳 对应协议GPS_millisecond
    *  @parameter longi GPS经度
    *  @parameter lati GPS纬度
    *  @parameter alti 融合高度
    *  @parameter product_sn 飞控id，硬件id
    *  @parameter spray_flag 
    *  @parameter motor_status  
    *  @parameter radar_height
    *  @parameter velocity_x  
    *  @parameter velocity_y 
    *  @parameter farm_delta_y
    *  @parameter farm_mode 
    *  @parameter pilot_num 
    *  @parameter session_num 对应协议session_GPS_millisecond
    *  @parameter frame_index   
    *  @parameter frame_flag  0:正在飞;1:代表降落（最后一条飞行记录）
    *  @parameter flight_version   
    *  @parameter plant  
    *  @parameter work_area
    *  @parameter inside_signature strtoupper(hash_hmac("sha256", $team_id.$user_id.$timestamp.$longi.$lati.$product_sn, $hmackey)) ----json结束
    *  @parameter token 登录令牌
    *  @parameter uuid 登录设备的唯一id
    *  @parameter time  当前时间戳
    *  @parameter os  android,ios
    *  @parameter version  app版本号
    *  @parameter signature 签名 strtoupper(hash_hmac("sha256", $team_id.$token.$uuid.$time.$os.$version, $hmackey))
    *  return 
    * {"status":0,"status_msg":"ok","message":"","data":[{"reid":1}]}
    * "status":200表示参数不足或者不匹配;"status":1001表示参数不合法或者签名不对;
    */
    public function actionAddflight()
    {
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'apimg/addflight');
        }
        $team_id = Yii::$app->request->post('team_id', 0);        
        $token = Yii::$app->request->post('token', '');
        $uuid = Yii::$app->request->post('uuid', '');
        $time = Yii::$app->request->post('time', '');
        $os = Yii::$app->request->post('os', '');
        $version = Yii::$app->request->post('version', '');
        $signature = Yii::$app->request->post('signature', '');      
        $taskjson = Yii::$app->request->post('flightjson', '');
        $model = array();       
        $djiUser = new DjiUser();
        $getStr = json_encode($_REQUEST);
        //写入日志
        $djiUser->add_log($getStr, 'apimg_addflight');
        if (empty($token) || empty($signature) || empty($taskjson)) {
            $status = 200;           
            $result = array('status' => $status, 'status_msg'=>'failed', 'message' => '参数不足或者不匹配');
            return $result;  
        }        
        $paramsString = $team_id.$token.$uuid.$time.$os.$version;
        $returnKey = __CLASS__.__FUNCTION__."_data_".md5($token);   
        $returnData = Yii::$app->cache->get($returnKey);
        if ( $returnData ) {
           return $returnData;
        }      
        $userData = $this->getUserInfo($signature,$paramsString,$token);
        //var_dump($userData);
        if ($userData && $userData['status'] == '0' && $userData['status_msg'] == 'ok') {
            $model['uid'] = $userData['items']['0']['account_info']['user_id'];
            $where = array();
            $where['uid'] = $userData['items']['0']['account_info']['user_id'];
            $where['deleted'] = '0';
            if ($team_id == '0') {               
                $activeInfo = Agroactiveinfo::getAndEqualWhere($where, 0,1);
                if ($activeInfo && is_array($activeInfo)) {                   
                    $model['upper_uid'] = $userData['items']['0']['account_info']['user_id'];                   
                }else{                 
                    $status = 3002;           
                    $result = array('status' => $status, 'status_msg'=>'ok', 'message' => '飞手权限不足');
                    Yii::$app->cache->set($returnKey, $result, 120);
                    return $result;  
                }
            }else{                
                 $where['team_id'] = $team_id;                             
                 $flyerInfo = Agroflyer::getAndEqualWhere($where,0,1);
                 if ( $flyerInfo && is_array( $flyerInfo) ) {
                    $model['upper_uid'] = $flyerInfo['0']['upper_uid'];
                 }else{
                    $status = 3002;           
                    $result = array('status' => $status, 'status_msg'=>'ok', 'message' => '飞手权限不足');
                    Yii::$app->cache->set($returnKey, $result, 60);
                    return $result;  
                 }
            }
            if($taskjson) {
                //echo $taskjson;exit;
                $taskArray = json_decode($taskjson,true);               
                foreach ($taskArray as $key => $value) {
                    if (isset($value['id']) && $value['id'] > 0) {
                       
                    }else{
                        //$team_id.$user_id.$timestamp.$longi.$lati.$product_sn
                        $paramsString = $value['team_id'].$value['user_id'].$value['timestamp'].$value['longi'].$value['lati'].$value['product_sn'];
                        $Djihmac = new Djihmac();
                        $nowSign = $Djihmac->getSign($paramsString);
                        //echo $nowSign;
                        if (!isset($value['inside_signature']) || $nowSign != $value['inside_signature']) {
                            $status = 1001;
                            $status_msg = 'failed';     
                            if (YII_DEBUG) { 
                              $result = array('nowSign' => $nowSign,'status' => $status, 'status_msg'=>$status_msg, 'message' => '参数不合法或者签名不对!!!');
                            }else{
                              $result = array('status' => $status, 'status_msg'=>'failed', 'message' => '参数不合法或者签名不对!!!');
                            }           
                            return $result;  
                        }
                        $flyerData = Agroactiveinfo::getSNData(array('hardware_id' => $value['product_sn']), 'uid',0,1);
                        if ($flyerData && is_array($flyerData)) {
                             $value['upper_uid'] =  $flyerData['0']['uid'];
                        }            
                        $taskResult = Iuavflightdata::add($value);
                        $taskResultArray[] = array('reid' => $taskResult );
                    }                   
                    
                }
            }
            $status = 0;           
            $result = array('status' => $status, 'status_msg'=>'ok', 'message' => '','data' => $taskResultArray);
            return $result;       
        }         
        return $userData;        
    }



    protected function getUserInfo($signature,$paramsString,$token,$lucked=1)
    {
        $status_msg = 'failed'; 
        $errorIpKey = __CLASS__.__FUNCTION__."_errorip_".md5($this->get_client_ip());   
        $errorIpData = Yii::$app->cache->get($errorIpKey);
        if ( $errorIpData  && $errorIpData > 30) {
           return array('status' => 1002,"status_msg" => "failed","message" => "系统错误!",);
        } 
        if (empty($token))
        {
          $data = array('status' => 1011,'status_msg'=> $status_msg,'message'=>'登录已经过期,请重新登录');
          return $data;
        } 
        $Djihmac = new Djihmac();
        $nowSign = $Djihmac->getSign($paramsString);
        //echo $nowSign;
        if ($nowSign != $signature) {
            $status = 1001;      
            if (YII_DEBUG) { 
              $result = array('nowSign' => $nowSign,'status' => $status, 'status_msg'=>$status_msg, 'message' => '参数不合法或者签名不对');
            }else{
              $result = array('status' => $status, 'status_msg'=>'failed', 'message' => '参数不合法或者签名不对');
            }           
            return $result;  
        }
        $actionName = 'apimg_';
        if ($lucked == 1) {
            $luckkey = $actionName.'_getUserInfo_'.md5($token);
            $luckdata = Yii::$app->cache->get($luckkey);
            if ( $luckdata ) {            
             $data = array('status' => 1004,'status_msg'=> $status_msg,'message'=>'3秒内重复提交,请稍后重试');
             return $data;
            }
            Yii::$app->cache->set($luckkey, 1, 1);   
        }    
        $djiUser = new DjiUser();       
        $userData = $djiUser->get_account_info_by_key('',$token);
        if ($userData && $userData['status'] == '0' && $userData['status_msg'] == 'ok') {
          return $userData;
        }else{
           Yii::$app->cache->set($errorIpKey, $errorIpData+1, 3600);
           return $userData;
        }
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

    /*激活信息接口
    *  代理商上传激活信息：测试环境http://ag.aasky.net/apimg/activeinfo  正式环境https://ag.dji.com/apimg/activeinfo
    *  @parameter string hardware_id 飞控号
    *  @parameter string body_code 飞机机身码  
    *  @parameter string   type    飞机机型号
    *  @parameter string  idcard   用户身份证号
    *  @parameter string  phone    用户手机号
    *  $params_string = $order_id.$value['body_code'].$value['hardware_id'].$postdata['idcard'].$postdata['phone'].$value['type'];              
    *  $sign = $Djihmac->getSign($params_string); 
    */
    public function actionActiveinfo()
    {
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ('apimg/activeinfo');
        }
        $model['order_id'] = Yii::$app->request->post('order_id', ''); //激活id
        $model['hardware_id'] = Yii::$app->request->post('hardware_id', ''); //飞控id
        $model['body_code'] = Yii::$app->request->post('body_code', ''); //机身序列号 
        $model['idcard'] = Yii::$app->request->post('idcard', ''); //身份证   
        $model['phone'] = Yii::$app->request->post('phone', '');  //手机号
        $model['type'] = Yii::$app->request->post('type', '');  //飞机型号

        $model['ip'] = $this->get_client_ip();
        $model['deleted'] = '0'; 
        $model['is_active'] = '0'; //处于还未激活状态
        $signature = Yii::$app->request->post('signature', ''); 

        $paramsString = $model['order_id'].$model['body_code'].$model['hardware_id'].$model['idcard'].$model['phone'].$model['type'];
        $Djihmac = new Djihmac();
        $nowSign = $Djihmac->getSign($paramsString);
        //echo $nowSign;die;
        if ($nowSign != $signature) {
            $status = 1001;      
            if (YII_DEBUG) { 
              $result = array('nowSign' => $nowSign,'status' => $status, 'status_msg'=>'fail', 'message' => '参数不合法或者签名不对');
            }else{
              $result = array('status' => $status, 'status_msg'=>'failed', 'message' => '参数不合法或者签名不对');
            }           
            return $result;  
        }

        if (empty($model['body_code']) || empty($model['idcard']) || empty($model['phone'])) {
            $status = 200;           
            $result = array('status' => $status, 'status_msg'=>'failed', 'message' => '参数不足或者不匹配');
            return $result;  
        } 

        $djiUser = new DjiUser();
        $getStr = json_encode($_REQUEST);
        $djiUser->add_log($getStr, 'apimg_active');//写入日志

        $result = Agroactiveinfo::add($model);
        if($result) {
            $result = array('status' => 0, 'status_msg'=>'ok', 'message' => '');
            return $result; 
        } else {
            $result = array('status' => 1001, 'status_msg'=>'fail', 'message' => '');
            return $result; 
        }
    }
    /*
    *  代理商上传保险信息：测试环境http://ag.aasky.net/apimg/insurance  正式环境https://ag.dji.com/apimg/activeinfo
    *  @parameter string  order_id 激活id
    *  @parameter string  pol_no   保单号
    *  @parameter string  exp_tm   保险结束时间,格式为：YYYYMMDDHHMMSS
    *  $params_string = $order_id.$value['pol_no'].$value['exp_tm']           
    *  $sign = $Djihmac->getSign($params_string); 
    */
    public function actionInsurance()
    {
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ('apimg/activeinfo');
        }
        $model['order_id'] = Yii::$app->request->post('order_id', ''); //激活id 
        $model['pol_no'] = Yii::$app->request->post('pol_no', ''); //保单号
        $model['exp_tm'] = Yii::$app->request->post('exp_tm', ''); //保险结束时间,格式为：YYYYMMDDHHMMSS

        $signature = Yii::$app->request->post('signature', '');

        $paramsString = $model['order_id'].$model['pol_no'].$model['exp_tm'];
        $Djihmac = new Djihmac();
        $nowSign = $Djihmac->getSign($paramsString);
        if ($nowSign != $signature) {
            $status = 1001;      
            if (YII_DEBUG) { 
              $result = array('nowSign' => $nowSign,'status' => $status, 'status_msg'=>'failed', 'message' => '参数不合法或者签名不对');
            }else{
              $result = array('status' => $status, 'status_msg'=>'failed', 'message' => '参数不合法或者签名不对');
            }           
            return $result;  
        }

        if (empty($model['order_id']) || empty($model['pol_no']) || empty($model['exp_tm'])) {
            $status = 200;           
            $result = array('status' => $status, 'status_msg'=>'failed', 'message' => '参数不足或者不匹配');
            return $result;  
        } 
        $model['query_flag'] = '1';//1-获取保单成功,0-失败

        $release = Agroactiveinfo::findAll(['order_id'=> $model['order_id']]);
        foreach ($release as $key => $value) {
            $value->pol_no = $model['pol_no'];
            $value->exp_tm = $model['exp_tm'];
            $value->query_flag = $model['query_flag'];
            $value->save();
        }
        if($release) {
            $result = array('status' => 0, 'status_msg'=>'ok', 'message' => '');
            return $result; 
        } else {
            $result = array('status' => 1001, 'status_msg'=>'fail', 'message' => '');
            return $result; 
        }
    }

    // app上传作业结果确认书
    public function actionUploadmissioncomplete() {
        /*
        $model['task_id'] = Yii::$app->request->post('task_id', '');
        $model['task_name'] = Yii::$app->request->post('taskName', '');
        $model['pilot_id'] = Yii::$app->request->post('pilotID', '');
        $model['pilot_name'] = Yii::$app->request->post('pilotName', '');
        $model['team_id'] = Yii::$app->request->post('teamID', '');
        $model['team_name'] = Yii::$app->request->post('teamName', '');
        $model['start_spray_time'] = Yii::$app->request->post('startSprayTime', '');
        $model['stop_spray_time'] = Yii::$app->request->post('stopSprayTime', '');
        $model['plan_area'] = Yii::$app->request->post('planArea', '');
        $model['spraying_area'] = Yii::$app->request->post('sprayingArea', '');
        $model['set_flow_mu'] = Yii::$app->request->post('setFlowMu', '');
        $model['total_flow'] = Yii::$app->request->post('totalFlow', '');
        $model['spray_mode'] = Yii::$app->request->post('sprayMode', '');
        */

        // auth info
        $token = Yii::$app->request->post('token');
        $missionInfo = Yii::$app->request->post('missionInfo');
        $signature = Yii::$app->request->post('signature');

        if (!isset($token) || !isset($missionInfo)) {
            return array('status' => 1009, 'status_msg'=>'failed', 'message' => '参数不足或者不匹配');
        }

        $data = json_decode($missionInfo, true);
        if (!$data) {
            return array('status' => 1010, 'status_msg' => 'failed', 'message' => '解析参数失败');
        }

        $dji_user = new DjiUser();
        $user_data = $dji_user->get_account_info_by_key('', $token);
        if (!$user_data || $user_data['status'] != '0' || $user_data['status_msg'] != 'ok') {
            return array('status' => 1011,'status_msg'=> 'failed','message'=>'登录已经过期,请重新登录');
        }

        // write log
        $djiUser = new DjiUser();
        $getStr = json_encode($_REQUEST);
        $djiUser->add_log($getStr, 'apimg_upload_mission_complete');

        // check auth
        /*
        $paramsString = $model['task_id'].$model['start'].$model['end'].$model['plan_area']
            .$model['spray_area'].$model['per_mou_usage'].$model['total_usage'].$model['spray_mode']
            .$model['spray_dir'].$model['is_in_geo'].$model['geo_starttime'].$model['geo_endtime']
            .$model['have_break_info'].$model['last_spray_break_dir'].$model['last_spray_break_idx']
            .$model['last_spray_break_point'].$model['work_times'];
        $userData = $this->getUserInfo($signature, $paramsString, $token);
        if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
        }
        */

        $result = AgroMissionComplete::bulkAdd($data);

        return array('status' => 0, 'status_msg' => 'ok', 'data' => $result);
    }

    /* app上传网卡信息
    *  APP上传网卡信息：http://ag.dji.com/apimg/setnetworkinfo  
    *  @parameter string product_id   飞控号
    *  @parameter string network_card 网卡号  
    *  @parameter string  imei        遥控器号
    *  @parameter string  account     用户账号
    */
    public function actionSetnetworkcardinfo() {
        $model['token'] = Yii::$app->request->post('token');
        $model['network_card'] = Yii::$app->request->post('network_card');
        $model['imei'] = Yii::$app->request->post('imei');
        $model['product_id'] = Yii::$app->request->post('product_id');
        $model['account'] = Yii::$app->request->post('account');

        if (!isset($model['token']) || !isset($model['network_card']) 
                || !isset($model['imei']) || !isset($model['product_id']) || !isset($model['account'])) {

            return array('status' => 1009, 'status_msg'=>'failed', 'message' => '参数不足或者不匹配');
        }

        $dji_user = new DjiUser();
        $user_data = $dji_user->get_account_info_by_key('', $model['token']);
        if (!$user_data || $user_data['status'] != '0' || $user_data['status_msg'] != 'ok') {
            return array('status' => 1011,'status_msg'=> 'failed','message'=>'登录已经过期,请重新登录');
        }

        //check update?
        $info = Agronetworkcard::findOne(['imei'=>$model['imei']]);
        if ($info) {
            $info->network_card = $model['network_card'];
            $info->imei = $model['imei'];
            $info->product_id = $model['product_id'];
            $info->account = $model['account'];
            $info->save();

            return array('status' => 0, 'status_msg' => 'ok', 'data' => '更新成功');
        }

        //insert
        $result = Agronetworkcard::add($model);

        return array('status' => 0, 'status_msg' => 'ok', 'data' => $result);
    }

    /* app获取网卡信息
    *  APP获取网卡信息：http://ag.dji.com/apimg/getnetworkinfo   
    *  @parameter string  imei        遥控器号
    */
    public function actionGetnetworkcardinfo() {
        $model['token'] = Yii::$app->request->post('token');
        $model['imei'] = Yii::$app->request->post('imei');

        if (!isset($model['token']) || !isset($model['imei'])) {
            return array('status' => 1009, 'status_msg'=>'failed', 'message' => '参数不足或者不匹配');
        }

        $dji_user = new DjiUser();
        $user_data = $dji_user->get_account_info_by_key('', $model['token']);
        if (!$user_data || $user_data['status'] != '0' || $user_data['status_msg'] != 'ok') {
            return array('status' => 1011,'status_msg'=> 'failed','message'=>'登录已经过期,请重新登录');
        }

        //get
        $info = Agronetworkcard::findOne(array('imei' => $model['imei']));

        if ($info) {
            return array('status' => 0, 'status_msg' => 'ok', 'data' => $info);
        } 

        return array('status' => 1001, 'status_msg'=>'fail', 'message' => '获取网卡信息失败');
    }

    public function actionPing() {
        return array('status' => 0);
    }
}
