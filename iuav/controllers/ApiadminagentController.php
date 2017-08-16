<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use app\models\Agroagent;
use app\models\ContactForm;

use GeoIp2\Database\Reader;
use PHPMailer;
use app\models\Agroagentmis;
use app\models\Agroagentbody;
use app\models\UserExchange;
use yii\base\ErrorException;
use app\components\DjiAgentUser;
use app\components\DjiUser;
use app\models\Agrostreet;
use app\models\Agroapplyinfo;
use app\models\Agroactiveinfo;
use app\models\Agrosninfo;

class ApiadminagentController extends Controller
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



    /* 
     *  忘记密码发送邮件 https://iuav.dji.com/apiadminagent/getpassword/地址 只支持post请求
     *  @parameter email 
     *
     *  return      
     * 
    */
    public function actionGetpassword()
    {
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'apiadminagent/getpassword');
        }
        $get_str = json_encode($_REQUEST);       
        $get_str .= "SERVER=".json_encode($_SERVER);
        $this->add_log($get_str, 'agentGetpassword');
        $email = Yii::$app->request->post("email");      
        
        //$request = Yii::$app->getRequest();        
        //$tpl = array('csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() );
         if (empty($email) ) {           
            $data = array('status' => 1003,'extra' => array('msg'=>'邮箱为空'));
            return $data;     
        }else{
            if (!DjiAgentUser::validate_is_email($email) ) {
                $data = array('status' => 1004,'extra' => array('msg'=>'邮箱格式不对'));
                return $data;  
            }    
            $luckkey = 'actionGetpassword'.md5($email);
            $luckdata = Yii::$app->cache->get($luckkey);
            if ( $luckdata ) {            
                return $luckdata;
            }

           $user = Agroagent::findByUsername($email); 
           if (empty($user)) {
                $data = array('status' => 1000,'extra' => array('msg'=>'用户不存在'));
                Yii::$app->cache->set($luckkey, $data, 600);
                return $data;
           }else{
                $datetime = time();
                $timeout = 3600*3;
                $DjiAgentUser = new DjiAgentUser();               
                $code = $DjiAgentUser->get_password($user->username,$datetime,$timeout);
                require(__DIR__ . '/../config/.config.php');                
                $url = $YII_GLOBAL['AGENTGETPASSWORD']['url']."adminagent/resetpassword/?code=".$code.'&datetime='.$datetime;
                $address = $email;
                $name = $user->username;
                $currency = 'cn';
                $comfile = __DIR__.'/../commands/PasswordSendEmail.php';
                // echo "nohup php $comfile $address $coupon '$name' > /dev/null &";
                @system("php $comfile $address \"$url\"  \"$name\" \"$currency\"  > /dev/null & ");        
                return $data = array('status' => 200,'extra' => array('msg'=>''));
           }
                   
           
        }
       

    }
    /*
    *  设置新密码就接口   https://iuav.dji.com/apiadminagent/resetpassword/地址 只支持post请求
    *  @parameter code 
    *  @parameter datetime
    *  @parameter newpassword  不用加密
    *  @parameter retpassword  不用加密
    *
    *  return    
    *
    */   
    public function actionResetpassword()
    {
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'apiadminagent/resetpassword');
        }
        $get_str = json_encode($_REQUEST);       
        $get_str .= "SERVER=".json_encode($_SERVER);
        $this->add_log($get_str, 'ApiagentResetpassword');
        $code = Yii::$app->request->post("code");
        $datetime =Yii::$app->request->post('datetime', '');   
        $newpassword =Yii::$app->request->post('newpassword', ''); 
        $retpassword =Yii::$app->request->post('retpassword', '');   
        if (empty($newpassword) || empty($retpassword) || empty($code) || empty($datetime)) {  
            $data = array('status' => 1003,'extra' => array('msg'=>'参数不合法'));
            return $data;            
        }
        if ($newpassword != $retpassword ) {  
            $data = array('status' => 1006,'extra' => array('msg'=>'两次密码不一致'));
            return $data;            
        }
        if (strlen($newpassword) < 8  ) {  
            $data = array('status' => 1006,'extra' => array('msg'=>'密码长度小于8位'));
            return $data;            
        }
        $DjiAgentUser = new DjiAgentUser();               
        $code = $DjiAgentUser->get_password_info($code);
        if (empty($code)) {
            $data = array('status' => 1004,'extra' => array('msg'=>'链接已失效'));
            return $data;  
        }else{
             $codedata = json_decode($code, TRUE);  
             if ( empty($codedata) || empty($codedata['username']) || ($codedata && $codedata['datetime'] != $datetime) ) {
                 $data = array('status' => 1004,'extra' => array('msg'=>'链接已失效','codedata' => ''));
                 return $data;  
             }
             $find = Agroagent::findByUsername($codedata['username']);
             if (empty($find)) {
                 $data = array('status' => 1005,'extra' => array('msg'=>'链接已失效','username' => 'no find'));
                 return $data;  
             }else{
                 $LoginFormList = array();
                 $LoginFormList['username'] = $codedata['username'];   
                 $LoginFormList['ip'] = $this->get_client_ip(); 
                 $LoginFormList['authKey'] = time(); 
                 $LoginFormList['accessToken'] = $LoginFormList['authKey'];   
                 $LoginFormList['password'] = md5($codedata['username'].$newpassword);                            
                 $password = md5($LoginFormList['authKey'].$LoginFormList['password']);
                 $LoginFormList['password'] =Yii::$app->getSecurity()->generatePasswordHash($password);
                 $reset = Agroagent::updatePassword($LoginFormList);
                 if ($reset > 0) {
                     $data = array('status' => 200,'extra' => array('msg'=>''));
                     return $data; 
                 }else{
                     $data = array('status' => 500,'extra' => array('msg'=>'系统忙'));
                     return $data; 
                 }

             }             
        }

    } 

    /*
    *  查询街道   https://iuav.dji.com/apiadminagent/getstreet/地址 只支持post请求
    *  @parameter area_no  区域id 
    *
    *  return    {"status":200,"data":[{"id":"50513","area_no":"659004","name":"兵团一零三团","street_no":"502","deleted":"0","source":"0","ext1":"","ext2":"","updated_at":"2016-03-09 18:33:34","created_at":"2016-03-09 18:33:34"}]}
    *
    */   
    public function actionGetstreet()
    {
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'apiadminagent/getstreet');
        }
        //$get_str = json_encode($_REQUEST);       
        //$get_str .= "SERVER=".json_encode($_SERVER);
        //$this->add_log($get_str, 'ApiagentGetstreet');
        $where = array();
        $where['area_no'] = Yii::$app->request->post("area_no");
        if (empty($where['area_no']) ) {  
            $data = array('status' => 1003,'extra' => array('msg'=>'参数不合法'));
            return $data;            
        }
        $luckkey = 'actionGetstreet'.md5($where['area_no']);
        $luckdata = Yii::$app->cache->get($luckkey);
        if ( $luckdata ) {            
            return $luckdata;
        }
        $data = Agrostreet::getAndEqualWhere($where);
        $result = array('status' => 200,'data' => $data,'extra' => array('msg'=>''));
        Yii::$app->cache->set($luckkey, $result, 3600);
        return $result; 

    }

    /*
    *  查询用户是否购买过农业无人机  接口地址 https://iuav.dji.com/apiadminagent/checkbuy/ 只支持post请求
    *  @key base64后的_meta_key
    *  @parameter datetime  当前时间戳
    *  @parameter sign  签名字符串  md5($key.$datetime.$privatekey);
    *
    *  return    {"status":0,"status_msg":"ok","checkbuy":1,"extra":{"msg":""}}
    *   checkbuy为1表示用户购买过农业无人机
    */   
    public function actionCheckbuy()
    {
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'apiadminagent/checkbuy');
        }
        $get_str = json_encode($_REQUEST);       
        $get_str .= "SERVER=".json_encode($_SERVER);
        $this->add_log($get_str, 'apiadminagentcheckbuy');
        $key = Yii::$app->request->post("key");
        $datetime =Yii::$app->request->post('datetime', '');  
        $sign = Yii::$app->request->post("sign"); 
        $privatekey = 'PjVWhGFbYcwZwG6e89hfaf';
        $nowsign = md5($key . $datetime.$privatekey);
       
        if (empty($key) ||  empty($sign) ||  empty($datetime)) {  
            $data = array('status' => 1000,'checkbuy'=> 0,'extra' => array('msg'=>'参数不合法'));
            return $data;            
        }
        //echo $nowsign."----";exit;
        if ($nowsign != $sign) {  
            $data = array('status' => 1001,'checkbuy'=> 0,'extra' => array('msg'=>'签名不对'));
            return $data;            
        }
        $luckkey = 'apiadminagentcheckbuy'.md5($key);
        $luckdata = Yii::$app->cache->get($luckkey);
        if ( $luckdata ) {            
            return $luckdata;
        }
        $meta_key = base64_decode($key);
        //echo $meta_key;exit;
        $djiUser = new DjiUser();
        $userData = $djiUser->get_account_info_by_key($meta_key);
        if ($userData && $userData['status'] == '0' && $userData['status_msg'] == 'ok') {
          if ($userData['items']['0']['account_info']['email']) {
             $where = array();
             $where['account'] =$userData['items']['0']['account_info']['email'];
             $data = Agroapplyinfo::getAndEqualWhere($where,0,1);
             if ($data && $data['0']['id'] > 0) {
               $result = array('status' => 0,'status_msg' => 'ok','checkbuy'=> 1,'extra' => array('msg'=>''));
               Yii::$app->cache->set($luckkey, $result, 3600);
               return $result;
             }else{
               $result = array('status' => 0,'status_msg' => 'ok','checkbuy'=> 0,'extra' => array('msg'=>''));
               Yii::$app->cache->set($luckkey, $result, 1200);
               return $result;
             }     

          }else{
             $data = array('status' => 1003,'checkbuy'=> 0,'extra' => array('msg'=>'用户没有邮箱'));
             return $data;        
          }            
        }else{
           $data = array('status' => 1002,'checkbuy'=> 0,'extra' => array('msg'=>'登录态不对','userData' => $userData));
           return $data;  
        }    
       

    }

     /* 
     *  农业无人机激活 https://iuav.dji.com/apiadminagent/activeverify/地址 只支持post请求     
     *  @parameter username 代理商登录账户
     *  @parameter body_code  整机系列号
     *  @parameter hardware_id  硬件id
     *  @parameter type  默认mg-1
     *  @parameter user_type  用户类别：personal(个人)，company(企业)
     *  @parameter account  dji账号邮箱
     *  @parameter company_name  企业名称
     *  @parameter company_number  企业注册号
     *  @parameter realname  真实用户名或者设备负责人姓名
     *  @parameter idcardtype 证件类型：01->身份证
     *  @parameter idcard  证件号码
     *  @parameter phone  手机号
     *  @parameter telephone 固定电话
     *  @parameter country  国家:kr(韩国)
     *  @parameter province  省份
     *  @parameter city  城市
     *  @parameter area  区
     *  @parameter street  街道
     *  @parameter address  详细 
     *  @parameter datetime  当前utc时间戳
     *  @parameter signature  签名 strtoupper(md5($username.$datetime.$key)); 
     *  
     *  return    {"status":200,"data":[{"body_code":"1321","hardware_id":"werew","type":"mg-1","active_id":2,"activation":"8997879"}],"extra":{"msg":""}}  
     *  "status":200 表示激活成功，activation 为激活码；
     *  "status":1000，参数不合法；"status":1001, 激活信息为空;"status":1002,激活信息不能超过5台;"status":1003,设备已经激活过;"status":1004,机身码和硬件id不匹配;
     *  "status":1005,机身码和代理不匹配;"status":1006,系统忙,请稍后重试;"status":1007,登录已经过期;"status":1008,重复提交,请5秒后重试!;"status":1009,参数不合法,用户相关;
     *  "status":1010,设备校验不通过;"status":1011,用户DJI帐号格式不对;"status":1012,ip限制;"status":1013,签名不对!;"status":1014,代理商不存在;
     *
    */
    public function actionActiveverify()
    {
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'apiadminagent/activeverify');
        }
        $get_str = json_encode($_REQUEST);       
        $get_str .= "&SERVER=".json_encode($_SERVER);
        $this->add_log($get_str, 'apiadminagent_activeverify');       
        $model = array();
        $info = Yii::$app->request->post("info");  //激活信息json格式[{"body_code":"1321","hardware_id":"werew","type":"mg-1"}]
        $check_info = Yii::$app->request->post("check_info",0); // 0: 正式提交，1：只是校验 
        $model['user_type'] = Yii::$app->request->post("user_type");  //用户类别：personal个人，company企业
        $model['account'] = Yii::$app->request->post("account");  //dji 账号
        $model['company_name'] = Yii::$app->request->post("company_name");  //企业名称
        $model['company_number'] = Yii::$app->request->post("company_number");  //企业注册号
        $model['realname'] = Yii::$app->request->post("realname");  //真实用户名
        $model['idcardtype'] = Yii::$app->request->post("idcardtype",'01'); //'证件类型：01->身份证,03->护照 ,02->往来港澳通行证',
        $model['idcard'] = Yii::$app->request->post("idcard"); //证件号码 
        $model['phone'] = Yii::$app->request->post("phone");  //手机号
        $model['telephone'] = Yii::$app->request->post("telephone");  //固定电话
        $model['country'] = Yii::$app->request->post("country"); 
        $model['province'] = Yii::$app->request->post("province"); 
        $model['city'] = Yii::$app->request->post("city");
        $model['area'] = Yii::$app->request->post("area");
        $model['street'] = Yii::$app->request->post("street");
        $model['address'] = Yii::$app->request->post("address");  //地址      
        $model['is_mall'] = Yii::$app->request->post("is_mall",0);  //保险单 0:不邮寄，1：邮寄  
        $model['username'] = Yii::$app->request->post("username");
        $model['datetime'] = Yii::$app->request->post("datetime");
        $model['signature'] = Yii::$app->request->post("signature");
        if (empty($model['username']) || empty($model['country']) || empty($model['datetime']) || empty($model['signature']) ) {           
            $data = array('status' => 1000,'extra' => array('msg'=>'参数不合法'));
            echo json_encode($data);exit;  
        }

        $luckkey = 'actionActiveverify'.md5($model['username']);
        $luckdata = Yii::$app->cache->get($luckkey);
        if ( $luckdata ) {
            $status = 1008;
            $result = array('status' => $status,'extra' => array('msg'=>'重复提交,请5秒后重试!'));
            die(json_encode($result));
        }        
        Yii::$app->cache->set($luckkey, 1, 5);

        $ip = $_SERVER['REMOTE_ADDR'];
        $allow_ip = array('127.0.0.1','218.17.158.154','218.17.157.76','222.231.10.71','211.63.33.58','210.101.217.115');
        if (!in_array($ip,$allow_ip)) {
            $status = 1012;
            $get_str .= "&status=".$status;
            $this->add_log($get_str, 'apiadminagent_activeverify'); 
            $result = array('status' => $status,'extra' => array('msg'=>'参数不合法,限制访问'));
            die(json_encode($result));
        }       
        $key = 'mJPLZfHa4feJYLjb7YthKRD4KqgYWEMeGh7';
        $param = $model['username'].$model['datetime'];
        $nowsignature = strtoupper(md5($param.$key));
        //echo $nowsignature;
        if ($nowsignature != $model['signature']) {
            $status = 1013;
            $get_str .= "&status=".$status;
            $this->add_log($get_str, 'apiadminagent_activeverify'); 
            $result = array('status' => $status,'extra' => array('msg'=>'签名不对!','msgen' => 'Wrong signature'));
            die(json_encode($result));
        }       

        if (empty($info)) {
             $body_code = Yii::$app->request->post("body_code");
             $hardware_id = Yii::$app->request->post("hardware_id");
             $type = Yii::$app->request->post("type");
             if ($body_code && $hardware_id && $type){
                $info = array(array('body_code' => $body_code,'hardware_id' => $hardware_id,'type' => $type));
             }             
        }else{
             $info = json_decode($info, TRUE);
        } 
        //韩国测试数据
        if ( $body_code == 'test26' ) {
            echo '{"status":200,"data":[{"body_code":"test26","hardware_id":"test26","type":"mg-1","active_id":2,"activation":"8997879"}],"extra":{"msg":""}}';
            exit;
        }   
        
        if (empty($info)) {
           $data = array('status' => 1001,'extra' => array('msg'=>'激活信息为空'));
           echo json_encode($data);exit;  
        }
        if (count($info) >5 ) {
           $data = array('status' => 1002,'extra' => array('msg'=>'激活信息不能超过5台'));
           echo json_encode($data);exit;  
        } 
        $agentInfo = Agroagent::getAgentNameForUsername($model['username']);
        if (empty($agentInfo) || $agentInfo['id'] < 1) {
            $status = 1014;
            $get_str .= "&status=".$status;
            $this->add_log($get_str, 'apiadminagent_activeverify'); 
            $result = array('status' => $status,'extra' => array('msg'=>'代理商不存在','msgen'=>'Agent does not exist'));
            die(json_encode($result));
        }
        $upper_agent_id = $agent_id = $agentInfo['id'];   
        $agent_code = $agentInfo['code'];       
        $model['agent_id'] = $agent_id;
        $model['upper_agent_id'] = $upper_agent_id;  
        $tmpActivation = array();
        $checkError = array();
        foreach ($info as $key => $value) {
          if (!isset($value['localid'])) {
              $value['localid'] = $key ;
          }           
          if (empty($value['hardware_id'])) {
                $value['error'] = '硬件ID为空';
                $value['status'] = '1008';
                $checkError[$value['localid']] = $value;
                continue;
           }
           if (empty($value['body_code'])) {
                $value['error'] = '整机序列号为空';
                $value['status'] = '1007';
                $checkError[$value['localid']] = $value;
                continue;
           }           
           $where = array();               
           $where['body_code'] = $value['body_code'];
           $where['hardware_id'] = $value['hardware_id'];
           $findBody = Agroactiveinfo::getAndEqualWhere($where, 0,1);
           if ($findBody) {
                $value['error'] = '已激活过';
                $value['status'] = '1003';
                $checkError[$value['localid']] = $value;
                continue;
               
           }
           $findBody = Agrosninfo::getAndEqualWhere($where, 0,1,'id',1,'body_code,hardware_id,activation');
           if (empty($findBody)) {
                $value['error'] = '硬件ID和整机序列号不匹配或输入有误，请重新输入';
                $value['status'] = '1004';
                $checkError[$value['localid']] = $value; 
                continue;
           }else{
                $tmpActivation[$where['body_code']] = $findBody;
           }
           $where['code'] = $agent_code;
           $findBody = Agroagentbody::getAndEqualWhere($where, 0,1);
           if (empty($findBody)) {
                $value['error'] = '抱歉，该飞机不属于您的销售范围，有疑问请联系DJI';
                $value['status'] = '1005';
                $checkError[$value['localid']] = $value;  
                continue;              
           }

        }
       if ($checkError) {
            $status = 1010;
            $get_str .= "&status=".$status;
            $this->add_log($get_str, 'apiadminagent_activeverify'); 
            $data = array('status' => $status,'error_data' => $checkError,'extra' => array('msg'=>'设备校验不通过','msg_en' => 'Equipment check is not passed'));
            echo json_encode($data);exit;  
       }
       if (empty($model['account']) || empty($model['realname']) || empty($model['phone']) || empty($model['idcard'])  ) {           
            $status = 1009;
            $get_str .= "&status=".$status;
            $this->add_log($get_str, 'apiadminagent_activeverify'); 
            $data = array('status' => $status,'extra' => array('msg'=>'参数不合法'));
            echo json_encode($data);exit;  
       }
       $DjiAgentUser = new DjiAgentUser();
       if (!$DjiAgentUser->validate_is_email($model['account'])) {
            $status = 1011;
            $get_str .= "&status=".$status;
            $this->add_log($get_str, 'apiadminagent_activeverify'); 
            $data = array('status' => $status,'extra' => array('msg'=>'用户DJI帐号格式不对'));
            echo json_encode($data);exit; 
       }

       $userObj = new DjiUser();
       $userInfo = $userObj->direct_get_user($model['account']);
       $model['uid'] = 0;
       if ($userInfo && $userInfo['status'] == '0' && $userInfo['status_msg'] == 'ok' ) {
           $model['uid'] =  $userInfo['items']['0']['user_id'];
       }else{
            $data = array('status' => 1015,'extra' => array('msg'=>'用户DJI帐号不存在,请先注册!','msgen' => 'DJI account does not exist, please register!'));
            echo json_encode($data);exit; 
       }
       $model['order_id'] = $DjiAgentUser->uuidv4(false);  
       $findOrder = Agroapplyinfo::getAndEqualWhere( array('order_id' => $model['order_id']),0,1);
       if ($findOrder) {
           $model['order_id'] = md5($DjiAgentUser->uuidv4(false).time().$model['agent_id']);  
       }
       $model['ip'] = $this->get_client_ip(); 
       $model['is_policies'] = 0;//不需要保险         
       $apply_id = Agroapplyinfo::add($model); 
       $activeList = array();       
       if ($apply_id > 0 ) {
           $model['apply_id'] = $apply_id;
           foreach ($info as $key => $value) {                               
               $model['body_code'] = $value['body_code'];
               $model['hardware_id'] = $value['hardware_id'];
               $model['type'] = $value['type'];                      
               $model['activation'] = $tmpActivation[$model['body_code']]['0']['activation'];               
               $active_id = Agroactiveinfo::add($model); 
               if ($active_id > 0) {
                   $value['active_id'] = $active_id;
                   $value['activation'] = $model['activation'];
                   $activeList[] = $value;
               }
            }
       }
       if (empty($activeList)) {
            $status = 1006;
            $get_str .= "&status=".$status;
            $this->add_log($get_str, 'apiadminagent_activeverify'); 
            $data = array('status' => $status,'extra' => array('msg'=>'系统忙,请稍后重试'));
            echo json_encode($data);exit;  
       }else{
            $comfile = __DIR__.'/../yii';
            $uid = $model['uid'];
            // 通知商城用户已经购买农业无人机
            @system("php $comfile store/index \"$uid\" > /dev/null & ");  

            $status = 200;
            $get_str .= "&status=".$status;
            $this->add_log($get_str, 'apiadminagent_activeverify'); 
            $data = array('status' => $status,'data'=>$activeList,'extra' => array('msg'=>''));
            echo json_encode($data);exit;  
       }
    }



    
    public function actionGetstreetteste()
    {
        exit;
        $data = file_get_contents("http://10.60.215.73:3000/demo/geo");
        $get_str = json_decode($data,TRUE);
        echo count($get_str);
         $go = 0;
        foreach ($get_str as $key => $value) {
            //var_dump($value);exit;
            $where = array();
            $where['area_no'] =  $key;
            foreach ($value as $key1 => $value1) {
                if ($go == 0 && $value1['id'] == '201' && $key == '530828') {
                     $go = 1;

                }else{
                     if ($go == 1 ) {
                      $where['name'] =  $value1['text'];
                      $where['street_no'] =  $value1['id'];
                      Agrostreet::add($where);
                      sleep(1);
                     }             

                }

               
            }
            

        }
        exit;

        $get_str = json_encode($_REQUEST);       
        $get_str .= "SERVER=".json_encode($_SERVER);
        $this->add_log($get_str, 'ApiagentGetstreet');

    }






    
    // 写入文件
    protected function add_log($msg, $type = 'friday')
    {
        $ip = $this->get_client_ip();
        $logfile = __DIR__.'/../runtime/logs/'.date('Ymd').'-'.$type.'.log';
        if ( ! file_exists($logfile))
        {
            file_put_contents($logfile, '');
        }
        $SERVER_ADDR = $_SERVER["SERVER_ADDR"];
        $headers = '';
       
        file_put_contents($logfile, date('Y/m/d H:i:s').":  $msg >>> $ip - $type  >> SERVER_ADDR=$SERVER_ADDR >> headers=$headers  \r\n", FILE_APPEND);
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



   
}
