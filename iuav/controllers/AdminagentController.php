<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\Agroagent;
use app\models\Agroagentbody;
use app\models\Agrosninfo;
use app\models\Agroactiveinfo;
use app\models\Agroapplyinfo;
use app\models\Agropolicies;
use app\models\Agronotice;
use app\models\User;
use GeoIp2\Database\Reader;
use app\components\DjiAgentUser;
use app\components\DjiUser;
use yii\captcha\Captcha;
use yii\captcha\CaptchaValidator;



class AdminagentController extends Controller
{
    //public $enableCsrfValidation = false;
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

    public function beforeAction($action)
    {
       if (in_array($action->actionMethod , array('actionActive','actionGetticket'))) {
            $this->enableCsrfValidation = false;
        }
        parent::beforeAction($action);
        $session = Yii::$app->session;
        $loginTime = $session->get('AGENTTIME');
        if ($loginTime) {
            $diff = time() - $loginTime;
            if ($diff > 800) {
                $DjiAgentUser = new DjiAgentUser();
                $DjiAgentUser->logout();
            } else if ($diff > 600) {
                $session->set('AGENTTIME', time());
            }
        }
        return true;
    }

    //检查用户是否已经登录
    protected function checkLogin()
    {


    }
    protected function getCookieCountry($country)
    {
        if (empty($country)) {           
           if (isset(Yii::$app->request->cookies['country'])) {
                return strtolower(Yii::$app->request->cookies['country']);
           }else{
            return 'cn';
           }
        }
        return strtolower($country);
    }


    public function actionIndex($country='')
    {
        
         if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'adminagent/index');
         }
        $session = Yii::$app->session;
        $agent_id = $session->get('AGENTUSERID');
        if ($agent_id < 1) {

            $country = $this->getCookieCountry($country);
            $url = (isset($country) && $country) ? '/'.$country.'/adminagent/login/' : '/adminagent/login/';

            return $this->redirect($url);
        }
        $request = Yii::$app->getRequest();
        $error = "";

        return $this->renderSmartyTpl('index.tpl', ['country' => $country,'error' => $error, 'title' => '代理商登录页面', 'csrf-param' => $request->csrfParam, 'csrftoken' => $request->getCsrfToken()]);

        $loginTime = $session->get('AGENTTIME');
        echo time() - $loginTime . "=======dsfds=======" . uniqid('mpfd6ZE9gkLU9ox');
        exit;
        return $this->redirect('/adminuser/list/');
        return $this->render('index');
    }

    /*
     * 跳转到售后/adminagent/aftermarket/地址
     * 
     * $key = $consumer_secret."&".$token_secret
     * $sign = base64_encode(hash_hmac("sha1", $ticket, $key, true)); 
    */
    public function actionAftermarket()
    {
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'adminagent/aftermarket');
         }
        $session = Yii::$app->session;
        $agent_id = $session->get('AGENTUSERID');
        if ($agent_id < 1) {
            return $this->redirect('/adminagent/login/');
        }
        require(__DIR__ . '/../config/.config.php');
        $appid = isset($YII_GLOBAL['AFTERMARKET']['appid']) ? $YII_GLOBAL['AFTERMARKET']['appid'] : "DJIAFTERMARKET";
        $url = isset($YII_GLOBAL['AFTERMARKET']['url']) ? $YII_GLOBAL['AFTERMARKET']['url'] : '';
        $consumer_secret = isset($YII_GLOBAL['AFTERMARKET']['consumer_secret']) ? $YII_GLOBAL['AFTERMARKET']['consumer_secret'] : "NPvwThDNv6QH8z3iWmqwKB";
        $token_secret = isset($YII_GLOBAL['AFTERMARKET']['token_secret']) ? $YII_GLOBAL['AFTERMARKET']['token_secret'] : "iw2auVtCBgVtN7rGkY6sLH";


        $DjiAgentUser = new DjiAgentUser();
        $ticket = $DjiAgentUser->get_ticket('DJIAFTERMARKET',3600);
        $sign = $DjiAgentUser->signature($ticket, $consumer_secret, $token_secret );
        $url = $url.'?ticket='.urlencode($ticket).'&sign='.urlencode($sign);

        $get_str = json_encode($_REQUEST);
        $get_str .= "url=".$url;
        $this->add_log($get_str, 'aftermarket');       
        //echo $url;exit;
        header("Location:$url");
        exit();
    }

   
    /* 
     *  sso登录后读取代理商信息 https://iuav.dji.com/adminagent/getticket  地址 只支持post,get请求
     *  @parameter datetime 时间戳
     *  @parameter ticket 票据
     *  @parameter appid  DJIAFTERMARKET
     *  @parameter sign  签名字符串 
     *
     *  return {"status":200,"data":{"agent_id":"1","username":"agr1","agentname":"3d","code":null,"upper_agent_id":"0"},"nowtime":1457335598,"dextra":{"msg":""}}
     *  upper_agent_id 为上级代理id
     *  以下签名相关
     *  consumer_secret 和  token_secret 企业QQ给
     *  $base_string = $datetime.$appid.$ticket;
     *  $key = $consumer_secret.'&'.$token_secret;
     *  $sign = base64_encode(hash_hmac("sha1", $base_string, $key, true));
    */    
    public function actionGetticket()
    {
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'adminagent/getticket');
         }
        $get_str = json_encode($_REQUEST);
        $get_str .= "SERVER=".json_encode($_SERVER);
        $this->add_log($get_str, 'getticket');

        $datetime = Yii::$app->request->get("datetime");
        $appid = Yii::$app->request->get("appid");
        $ticket = Yii::$app->request->get("ticket");
        $sign = Yii::$app->request->get("sign");
        if (empty($sign)) {
            $datetime = Yii::$app->request->post("datetime");
            $appid = Yii::$app->request->post("appid");
            $ticket = Yii::$app->request->post("ticket");
            $sign = Yii::$app->request->post("sign");
        }
        if (empty($ticket) || empty($sign)) {
            $msg = "参数为空";
            echo json_encode(array('status' => 100, 'extra' => array('msg' => $msg)));
            exit;
        }
        require(__DIR__ . '/../config/.config.php');
        $appid = isset($YII_GLOBAL['AFTERMARKET']['appid']) ? $YII_GLOBAL['AFTERMARKET']['appid'] : "DJIAFTERMARKET";
        $url = isset($YII_GLOBAL['AFTERMARKET']['url']) ? $YII_GLOBAL['AFTERMARKET']['url'] : '';
        $consumer_secret = isset($YII_GLOBAL['AFTERMARKET']['consumer_secret']) ? $YII_GLOBAL['AFTERMARKET']['consumer_secret'] : "NPvwThDNv6QH8z3iWmqwKB";
        $token_secret = isset($YII_GLOBAL['AFTERMARKET']['token_secret']) ? $YII_GLOBAL['AFTERMARKET']['token_secret'] : "iw2auVtCBgVtN7rGkY6sLH";

        // $ticket = urldecode($ticket);
        // $sign = urldecode($sign);
        $DjiAgentUser = new DjiAgentUser();
        $base_string = $datetime.$appid.$ticket;
        $nowsign = $DjiAgentUser->signature($base_string, $consumer_secret, $token_secret );
        if ($nowsign != $sign ) {
            $msg = "签名不对";
            $status = 101;
            $get_str .= "status=".$status;
            $this->add_log($get_str, 'getticket');
            //echo json_encode(array('status' => $status,'extra' => array('msg'=>$msg)));
            //exit;
        }
        $redata = array();
        $data = $DjiAgentUser->get_ticket_info($ticket);
        if ($data) {
            $data = json_decode($data, TRUE);
            //var_dump($data);exit;
            //  session_destroy();
            /*
            session_id($data['sid']);                 
            if (isset($_SESSION['AGENTUSERID'])) {
                $agent_id = $_SESSION['AGENTUSERID'];
                $upper_agent_id = $_SESSION['UPPERAGENTID'];
                $upper_agent_data = array();
                if ($upper_agent_id > 0 ) {
                    $where = array();
                    $where['id'] = $upper_agent_id;
                    $upper_agent_data = Agroagent::getAndEqualWhere($where,0,1,'id',1,'id,username,agentname,code');                    
                }
                if ($agent_id && $agent_id > 0) {
                    $redata = array('agent_id' => $agent_id, 'username' => $_SESSION['AGENTUSERNAME'], 'agentname' => $_SESSION['AGENTNAME'], 'code' => $_SESSION['AGENTCODE'], 'upper_agent_id' => $_SESSION['UPPERAGENTID'],'upper_agent_data' => $upper_agent_data);
                }
            }
            */
            if ($data) {
                $upper_agent_data = array();
                if ($data && $data['upper_agent_id'] > 0 ) {
                    $where = array();
                    $where['id'] = $upper_agent_id;
                    $upper_agent_data = Agroagent::getAndEqualWhere($where,0,1,'id',1,'id,username,agentname,code');                    
                }
                if ($data && $data['agent_id'] > 0) {
                    $data['upper_agent_data'] = $upper_agent_data;
                }
            }

            if (empty($data)) {
                $msg = "登录已经过期";
                $status = 102;
                $get_str .= "status=".$status;
                $this->add_log($get_str, 'getticket');
                echo json_encode(array('status' => $status,'extra' => array('msg'=>$msg)));
                exit;
            }

        }
        $nowtime = time();
        $msg = "";
        $status = 200;
        $get_str .= "&redata=".json_encode($redata)."&status=".$status;
        $this->add_log($get_str, 'getticket');
        echo json_encode(array('status' => 200,'data' => $data,'nowtime' => $nowtime,'dextra' => array('msg'=>$msg)));
        exit;
    }

    //代理登录页面 adminagent/login
    public function actionLogin($country='')
    {
        //var_dump($country);exit;
         if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'adminagent/login');
         }
        $ip = $_SERVER['REMOTE_ADDR'];
        $LoginFormList = Yii::$app->request->post("LoginForm");
        $captcha = Yii::$app->request->post("captcha");
        //var_dump($LoginFormList);exit;
        $UserIP = Yii::$app->request->getUserIP();

        $request = Yii::$app->getRequest();
        $error = $codeerror = '';
        $firstKey = 'v1';

        $VerifyCodeKey = $firstKey.'VerifyCodeAdminagentController_actionLogin' . md5($ip);
        $VerifyCodedata = Yii::$app->cache->get($VerifyCodeKey);       
        $CaptchaHtml = '';
        if (empty($LoginFormList['username']) || empty($LoginFormList['password']) || !preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $LoginFormList['username']) ) {
            //$VerifyCodedata = $VerifyCodedata+1;
            //Yii::$app->cache->set($VerifyCodeKey, $VerifyCodedata, 3600*12);
            if ($VerifyCodedata) {
                 $CaptchaHtml =  Captcha::widget(['name' => 'captcha',]);
            }
            return $this->renderSmartyTpl('login.tpl', ['country' => $country,'CaptchaHtml' => $CaptchaHtml,'username' => '','title' => '代理商登录页面', 'codeerror' =>$codeerror,'error' => $error, 'csrf-param' => $request->csrfParam, 'csrftoken' => $request->getCsrfToken()]);
        }

        if (strlen($LoginFormList['username']) > 50 )  {
            $error = "Incorrect username strlen is  ".strlen($LoginFormList['username']);
        }

        if ($UserIP) {
            $luckkeyIP = $firstKey.'AdminagentController_actionLogin' . md5($UserIP);
            $luckdataIP = Yii::$app->cache->get($luckkeyIP);
            if ($luckdataIP > 10) {
                $error = "Incorrect username or password. " . $luckdataIP . "times";
            }
        }
        $luckkey = $firstKey.'AdminagentController_actionLogin' . md5($LoginFormList['username']);
        $luckdata = Yii::$app->cache->get($luckkey);
        if ($luckdata > 5) {
            $error = "Incorrect username or password. " . $luckdata . "times!!!";

        }
        $luckkeypassword = $firstKey.'AdminagentController_actionLogin_password' . md5($LoginFormList['password']);
        $luckdatapassword = Yii::$app->cache->get($luckkeypassword);
        if ($luckdatapassword > 10) {
            $error = "Incorrect username or password. " . $luckdatapassword . "times";

        }
        $result = array();
        //$error = $VerifyCodedata = 0 ;
        if (empty($error) && empty($VerifyCodedata)) {
            $DjiAgentUser = new DjiAgentUser();
            $result = $DjiAgentUser->login($LoginFormList['username'],$LoginFormList['password']);            
            if ($result &&  $result['status'] == 200 ) {
                return $this->redirect('/adminagent/');
            }else{
                $error = $result['extra']['msg'];
            }
        }else{
           $VerifyCodedata = $VerifyCodedata+1;
           Yii::$app->cache->set($VerifyCodeKey, $VerifyCodedata, 3600*12);
           if ($VerifyCodedata && $VerifyCodedata < 20 ) {
                $tmpCaptcha = new CaptchaValidator();       
                if ( $tmpCaptcha->validate($captcha) == '1'){
                    $DjiAgentUser = new DjiAgentUser();
                    $result = $DjiAgentUser->login($LoginFormList['username'],$LoginFormList['password']);            
                    if ($result &&  $result['status'] == 200 ) {
                        Yii::$app->cache->set($VerifyCodeKey, 1, 3600*12);
                        return $this->redirect('/adminagent/');
                    }else{
                        $error = $result['extra']['msg'];
                    }
                }else{
                    $error = "验证码输入有误";
                }
           }
        }
        Yii::$app->cache->set($luckkey, $luckdata+1, 3600);
        Yii::$app->cache->set($luckkeypassword, $luckdatapassword+1, 3600);
        if ($luckkeyIP) {
            Yii::$app->cache->set($luckkeyIP, $luckdataIP+1, 600);
        }  
        if ($VerifyCodedata) {
                 $CaptchaHtml =  Captcha::widget(['name' => 'captcha',]);
        }
        return $this->renderSmartyTpl('login.tpl', ['CaptchaHtml' => $CaptchaHtml,'username' => $LoginFormList['username'],'data' => $result,'error' => $error,'title' => '代理商登录页面','csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);           
    
    }
    //退出地址
    public function actionLogout()
    {
         if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'adminagent/logout');
         }

        $DjiAgentUser = new DjiAgentUser();
        $DjiAgentUser->logout();
        return $this->redirect('/adminagent/login');
    }


    public function actionAccount($country='')
    {
         if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'adminagent/account');
         }
        $get_str = json_encode($_REQUEST);       
        $get_str .= "SERVER=".json_encode($_SERVER);
       

        $session = Yii::$app->session;
        $agent_id = $session->get('AGENTUSERID');
        if ($agent_id < 1) {
            return $this->redirect('/adminagent/login/');
        } 
        $get_str .= "&agent_id=".$agent_id;
        $this->add_log($get_str, 'adminagent_account');       
        $model = array();
        $upper_agent_id = $session->get('UPPERAGENTID');  
        if ($upper_agent_id && $upper_agent_id != $agent_id) {
           return $this->redirect('/adminagent/login/');
        }  
        $where = array();
        $where['upper_agent_id'] =  $agent_id;
        $data = Agroagent::getAndEqualWhere($where,0,50,'id',1,"*");
        $list = array();
        $list['agengData'] = $data;
        $request = Yii::$app->getRequest();
        $list['csrftoken']=$request->getCsrfToken();
        $list['country']=$country;
        return $this->renderSmartyTpl('account.tpl', $list);
    }

    public function actionFeedback()
    {
        return $this->renderSmartyTpl('feedback.tpl', []);
    }

    /*
    * 忘记密码 输入用户名 页面，调用/apiadminagent/getpassword/ post请求方式，参数email
    * 
    */ 
    public function actionGetpassword()
    {
         if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'adminagent/getpassword');
         }
        $get_str = json_encode($_REQUEST);
        $get_str .= "SERVER=".json_encode($_SERVER);
        $this->add_log($get_str, 'agentGetpassword');
        $list = array();
        $request = Yii::$app->getRequest();
        $tpl = array('csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() );
        //$tpl['username'] = Yii::$app->user->identity->username;

        return $this->renderSmartyTpl('get_password.tpl', $tpl);
      
    }  
    /*
    * 邮件点击找回密码页面  /apiadminagent/resetpassword/?code=343&datetime=12312321
    */   
    public function actionResetpassword()
    {
         if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'adminagent/resetpassword');
         }
        $get_str = json_encode($_REQUEST);       
        $get_str .= "SERVER=".json_encode($_SERVER);
        $this->add_log($get_str, 'agentResetpassword');
        $code = Yii::$app->request->get("code");
        $datetime =Yii::$app->request->get('datetime', '');        
        $request = Yii::$app->getRequest();        
        $tpl = array('csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() );
        //$tpl['username'] = Yii::$app->user->identity->username; 
        $tpl['code'] = $code;
        $tpl['datetime'] = $datetime; 
        $tpl['error'] = ""; 
        $DjiAgentUser = new DjiAgentUser();               
        $code = $DjiAgentUser->get_password_info($code,0);
        if (empty($code) ) {
            $tpl['error'] = "链接已失效";
            return $this->renderSmartyTpl('reset_password.tpl', $tpl);
        }
        return $this->renderSmartyTpl('reset_password.tpl', $tpl);
    }

    /* 
     *  农业无人机激活 https://iuav.dji.com/adminagent/active/地址 只支持post请求
     *  @parameter info  激活信息json格式[{"body_code":"1321","hardware_id":"werew","type":"mg-1"}]
     *  @parameter check_info 0:正式提交，1：校验激活信息
     *  @parameter user_type  用户类别：personal个人，company企业
     *  @parameter account  dji 账号
     *  @parameter company_name  企业名称
     *  @parameter company_number  企业注册号
     *  @parameter realname  真实用户名
     *  @parameter idcardtype '证件类型：01->身份证,03->护照 ,02->往来港澳通行证',
     *  @parameter idcard  证件号码
     *  @parameter phone  手机号
     *  @parameter telephone 固定电话
     *  @parameter country  国家
     *  @parameter province  省份
     *  @parameter city  城市
     *  @parameter area  区
     *  @parameter street  街道
     *  @parameter address  详细
     *  @parameter is_mall  是否邮件保险单 0:不邮寄，1：邮寄  
     *  
     *  return    {"status":200,"data":[{"body_code":"1321","hardware_id":"werew","type":"mg-1","active_id":2,"activation":"8997879"}],"extra":{"msg":""}}  
     *  "status":200 表示激活成功，activation 为激活码；
     *  "status":1000，参数不合法；"status":1001, 激活信息为空;"status":1002,激活信息不能超过5台;"status":1003,设备已经激活过;"status":1004,机身码和硬件id不匹配;
     *  "status":1005,机身码和代理不匹配;"status":1006,系统忙,请稍后重试;"status":1007,登录已经过期;"status":1008,重复提交,请5秒后重试!;
     * 
    */
    public function actionActive()
    {
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'adminagent/active');
        }
        $get_str = json_encode($_REQUEST);       
        $get_str .= "SERVER=".json_encode($_SERVER);
        $this->add_log($get_str, 'apiadminagent_active');

        $session = Yii::$app->session;
        $agent_id = $session->get('AGENTUSERID');
        if ($agent_id < 1) {
           $data = array('status' => 1007,'extra' => array('msg'=>'登录已经过期'));
           echo json_encode($data);exit;  
        }

        $luckkey = 'actionActive'.md5($agent_id);
        $luckdata = Yii::$app->cache->get($luckkey);
        if ( $luckdata ) {
            $status = 1008;
            $result = array('status' => $status,'extra' => array('msg'=>'重复提交,请5秒后重试!'));
            die(json_encode($result));
        }
        Yii::$app->cache->set($luckkey, 1, 5);
       
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
        $model['country'] = Yii::$app->request->post("country",'中国'); 
        $model['province'] = Yii::$app->request->post("province"); 
        $model['city'] = Yii::$app->request->post("city");
        $model['area'] = Yii::$app->request->post("area");
        $model['street'] = Yii::$app->request->post("street"); //前端有可能上传0 表示暂不用填写
        $model['address'] = Yii::$app->request->post("address");  //地址      
        $model['is_mall'] = Yii::$app->request->post("is_mall",0);  //保险单 0:不邮寄，1：邮寄  

        if (empty($model['street'])) {
            $model['street'] = '';
        }
        $langCountry = $this->getCookieCountry('');
        if (file_exists(__DIR__ . '/../messages/'.$langCountry.'/lang.php')) {
                 $LANGDATA = require(__DIR__ . '/../messages/'.$langCountry.'/lang.php');
        } 

        if (empty($info) ) {           
            $data = array('status' => 1000,'extra' => array('msg'=>$LANGDATA['iuav_invalid_value']));
            echo json_encode($data);exit;  
        }
            
       $info = json_decode($info, TRUE);
       if (empty($info)) {
           $data = array('status' => 1001,'extra' => array('msg'=>$LANGDATA['iuav_active_empty']));
           echo json_encode($data);exit;  
       }
       if (count($info) >5 ) {
           $data = array('status' => 1002,'extra' => array('msg'=>$LANGDATA['iuav_more_five']));
           echo json_encode($data);exit;  
       }       
       $agent_code = $session->get('AGENTCODE');
       $upper_agent_id = $session->get('UPPERAGENTID');  
       if (empty($upper_agent_id )) {
           $upper_agent_id = $agent_id;
       }
       $tmpNamePhone = Agroagent::getAgentNamePhone($upper_agent_id);

       if (empty($agent_code)) {
           //增加读取          
           $agent_code = $tmpNamePhone['code'];
       }else if ($agent_code != $tmpNamePhone['code']) {
           $data = array('status' => 1022,'extra' => array('msg'=>'登录已经过期!'));
           echo json_encode($data);exit;  
       } 
       $is_policies = 1;
       if (isset($tmpNamePhone['is_policies'])) {
          $is_policies = $tmpNamePhone['is_policies'];
       }      
       $model['agent_id'] = $agent_id;
       $model['upper_agent_id'] = $upper_agent_id;  
       $tmpActivation = array();
       $checkError = array();
       foreach ($info as $key => $value) {
           //$value['localid'] = $key ;
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
                $value['error'] = $LANGDATA['iuav_activated'];
                $value['status'] = '1003';
                $checkError[$value['localid']] = $value;
                continue;
               
           }
           $findBody = Agrosninfo::getAndEqualWhere($where, 0,1,'id',1,'body_code,hardware_id,activation');
           if (empty($findBody)) {
                $value['error'] = $LANGDATA['iuav_hardware_id_error'];
                $value['status'] = '1004';
                $checkError[$value['localid']] = $value; 
                continue;
           }else{
                $tmpActivation[$where['body_code']] = $findBody;
           }
           if ($tmpNamePhone['oldcode']) {
               $where['code'] = array($agent_code,$tmpNamePhone['oldcode']);
           }else{
               $where['code'] = $agent_code;
           }           
           $findBody = Agroagentbody::getAndEqualWhere($where, 0,1);
           if (empty($findBody)) {                 
                $value['error'] = $LANGDATA['iuav_not_auth'];
                $value['status'] = '1005';
                $checkError[$value['localid']] = $value;  
                continue;    
                           
           }

       }
       if ($checkError) {
            $data = array('status' => 1010,'error_data' => $checkError,'extra' => array('msg'=>$LANGDATA['iuav_equipment_failed']));
            echo json_encode($data);exit;  
       }
       if ($check_info == 1) {
           $data = array('status' => 200,'check_info' => $check_info,'data' => $tmpActivation,'extra' => array('msg'=>''));
           $get_error_str =  $get_str."model=".json_encode($model)."&redata=".json_encode($data);
           $this->add_log($get_error_str, 'apiadminagent_active');
           echo json_encode($data);exit;  
       }       
       if (empty($model['account']) || empty($model['realname']) || empty($model['phone']) || ($is_policies == 1 && empty($model['idcard']) )  ) {           
            $data = array('status' => 1009,'extra' => array('msg'=>$LANGDATA['iuav_invalid_value']));
            echo json_encode($data);exit;  
       }
       $DjiAgentUser = new DjiAgentUser();  
       $model['account'] = trim($model['account']);
       if (!$DjiAgentUser->validate_is_email($model['account'])) {
            $data = array('status' => 1011,'extra' => array('msg'=>$LANGDATA['iuav_incorrect_format']));
            echo json_encode($data);exit; 
       }
       $userObj = new DjiUser();
       $userInfo = $userObj->direct_get_user($model['account']);
       $model['uid'] = 0;
       if ($userInfo && $userInfo['status'] == '0' && $userInfo['status_msg'] == 'ok' ) {
           $model['uid'] =  $userInfo['items']['0']['user_id'];
       }else{
            $data = array('status' => 1012,'extra' => array('msg'=>$LANGDATA['iuav_not_account']));
            echo json_encode($data);exit; 
       }

       $model['order_id'] = $DjiAgentUser->uuidv4(false);  
       $findOrder = Agroapplyinfo::getAndEqualWhere( array('order_id' => $model['order_id']),0,1);
       if ($findOrder) {
           $model['order_id'] = md5($DjiAgentUser->uuidv4(false).time().$model['agent_id']);  
       }
       $model['ip'] = $this->get_client_ip(); 
       //默认是需要买保险的       
       if ($is_policies === 0) {
           $model['is_policies'] = $is_policies;
       }         
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
            $data = array('status' => 1006,'extra' => array('msg'=>$LANGDATA['iuav_system_error']));
            echo json_encode($data);exit;  
       }else{
            
            $comfile = __DIR__.'/../yii';
            $order_id = $model['order_id'];
            // echo "nohup php $comfile $address $coupon '$name' > /dev/null &";
            @system("php $comfile policies/index $apply_id \"$order_id\" > /dev/null & ");  

            $comfile = __DIR__.'/../commands/FirstSendEmail.php';
            $address = $model['account']; 
            $country = 'cn';  
            if (isset(Yii::$app->request->cookies['country'])) {
                $country = Yii::$app->request->cookies['country']->value;
                $country = strtolower($country);
            }
        
            @system("php $comfile \"$address\" \"$country\" > /dev/null & ");    

            $comfile = __DIR__.'/../yii';
            $uid = $model['uid'];
            // 通知商城用户已经购买农业无人机
            @system("php $comfile store/index \"$uid\" > /dev/null & ");  

            $data = array('status' => 200,'data'=>$activeList,'extra' => array('msg'=>''));
            echo json_encode($data);exit;  
       }
    }

    /* 
     *  农业无人机激活 https://iuav.dji.com/adminagent/management/地址 只支持get请求
     *  @parameter begin  开始日期2016-03-09
     *  @parameter end    结束日期2016-03-09
     *  @parameter typename  类型：'body_code','hardware_id'
     *  @parameter typevalue  搜索内容，全文匹配
     *  @parameter page  页面
     *  @parameter size  每页个数    
     *  
     *  return{"status":200,"data":[{"order_id":"db224410893c4b9a9e557086a9f04602","apply_id":"1","body_code":"1321","hardware_id":"werew","activation":"8997879","company_name":"2343243","realname":"dsfdsfd","photo":"","agent_id":"3","upper_agent_id":"3","created_at":"2016-03-09 14:18:55","agentname":"21","polnostr":"\u5904\u7406\u4e2d","pol_no":""}],"count":"1","page":1,"size":20,"page_count":1,"extra":{"msg":""}}
     *  "status":200 表示激活成功，activation 为激活码；
     *  "status":1007,登录已经过期;
     * 
    */
    public function actionManagement($country='')
    {
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'adminagent/management');
        }
        $get_str = json_encode($_REQUEST);       
        $get_str .= "SERVER=".json_encode($_SERVER);
        $this->add_log($get_str, 'apiadminagent_actionManagement');

        $session = Yii::$app->session;
        $agent_id = $session->get('AGENTUSERID');
        if ($agent_id < 1) {
            return $this->redirect('/adminagent/login/');
        }   
        
        $model = array();
        $upper_agent_id = $session->get('UPPERAGENTID');  
        //不允许子账号查看客户信息
        if ($upper_agent_id >0 && $upper_agent_id != $agent_id) {
            return $this->redirect('/adminagent/login/');
        }
        if (empty($upper_agent_id )) {
           $upper_agent_id = $agent_id;
        }
        if (empty($upper_agent_id )) {
           //$upper_agent_id = 3;
        }
        
        $model['agent_id'] = $agent_id;
        $model['upper_agent_id'] = $upper_agent_id;   
        $model['begin'] = strip_tags(Yii::$app->request->get("begin"));  //开始时间
        $model['end'] = strip_tags(Yii::$app->request->get("end"));   //结束时间
        $model['typename'] = strip_tags(Yii::$app->request->get("typename"));  //企业名称
        $model['typevalue'] = strip_tags(Yii::$app->request->get("typevalue"));
        //echo $model['typevalue'];exit;
        if (in_array($model['typename'], array('body_code','hardware_id','account','phone'))) {
            $model[$model['typename']] = $model['typevalue'];  
        }
        $page = Yii::$app->request->get('page', 1);
        $size = Yii::$app->request->get('size', 20);
        $page += 0;
        $size += 0;
        $start = ($page - 1) * $size;
        if ( $start < 0 ) $start = 0; 
        $luckkey = 'v32actionManagement'.md5(implode(',',$model)). $page.$size;
        $luckdata = Yii::$app->cache->get($luckkey);
        if ( $luckdata ) {
           return $this->renderSmartyTpl('management.tpl', $luckdata);
    
           // die(json_encode($luckdata));
        }
        $base_url = "/adminagent/management/?begin=".$model['begin'].'&end='.$model['end'].'&typename='.$model['typename'].'&typevalue='.$model['typevalue'];
        $data = array();  
        $page_count = 0;     
        $fields = 'count(*) AS activeCount';
        $count = Agroactiveinfo::getActiveWhere($model,$fields);   
              // var_dump($count);exit;     
        if ($count && $count['0']['activeCount'] == '0') {
             $status = 200;
             $result = array('status' => $status,'typevalue' => $model['typevalue'],'base_url' => $base_url,'data' => $data,'count' => $count['0']['activeCount'],'page' => $page,'size' => $size,'page_count' => $page_count,'extra' => array('msg'=>''));
             Yii::$app->cache->set($luckkey, $result, 5);
             //var_dump($result);exit;
             return $this->renderSmartyTpl('management.tpl', $result);
            
        }
        $page_count = ceil($count['0']['activeCount'] / $size);        
        $fields = 'agro_active_info.order_id,agro_active_info.apply_id,agro_active_info.body_code,agro_active_info.hardware_id,agro_active_info.activation,agro_apply_info.company_name,agro_apply_info.realname,agro_apply_info.phone';
        $fields .=',agro_active_info.agent_id,agro_active_info.upper_agent_id,agro_active_info.created_at';
        
        $data = Agroactiveinfo::getActiveWhere($model,$fields,$start,$size);

        $newData = array();      
        foreach ($data as $key => $value) {
            $value['agentname'] = Agroagent::getAgentname($value['agent_id']);
            $tmpPol= Agropolicies::getPolNo($value['apply_id'],$value['order_id']);
            $value['polnostr'] = $tmpPol['polnostr'];
            $value['pol_no'] = $tmpPol['pol_no'];
            $newData[] =$value;
            
        }
        $status = 200;
        $result = array('country' => $country,'status' => $status,'typevalue' => $model['typevalue'],'base_url' => $base_url,'data' => $newData,'count' => $count['0']['activeCount'],'page' => $page,'size' => $size,'page_count' => $page_count,'extra' => array('msg'=>''));
        Yii::$app->cache->set($luckkey, $result, 5);      
        //var_dump($result);exit;
        return $this->renderSmartyTpl('management.tpl', $result);         
       
    }
    /*
    * 创建子账号 /adminagent/addagent 支持post
    *  @parameter agentname 
    *  @parameter realname  真实用户名
    *  @parameter phone  手机号
    *  @parameter email  邮箱
    *  @parameter country  国家
    *  @parameter province  省份
    *  @parameter city  城市
    *  @parameter area  区
    *  @parameter street  街道
    *  @parameter address  详细
    *  {"status":200,"id":8,"data":{"agentname":"dsfdsfds","realname":"34343","phone":"12321321","email":"er1e11wrew@dji.com","country":"","province":null,"city":null,"area":null,"street":null,"address":"dsfdsfdsfds","username":"er1e11wrew@dji.com","upper_agent_id":null,"id":8,"status":"pending"},"extra":{"msg":""}}
    * 
    */
    public function actionAddagentchild()
    {
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'adminagent/addagent');
        }
        $get_str = json_encode($_REQUEST);       
        $get_str .= "SERVER=".json_encode($_SERVER);
        $this->add_log($get_str, 'adminagen_actionAddagent');

        $session = Yii::$app->session;
        $agent_id = $session->get('AGENTUSERID');
        if ($agent_id < 1) {
           $data = array('status' => 1007,'extra' => array('msg'=>'登录已经过期'));
           echo json_encode($data);exit;  
        }
        $upper_agent_id = $session->get('UPPERAGENTID');  
        if ($upper_agent_id && $upper_agent_id != $agent_id  ) {
           $data = array('status' => 1011,'extra' => array('msg'=>'您无法创建子账号'));
           echo json_encode($data);exit;  
        }
        $luckkey = 'adminagen_actionAddagent'.md5($agent_id);
        $luckdata = Yii::$app->cache->get($luckkey);
        if ( $luckdata ) {
            $status = 1008;
            $result = array('status' => $status,'extra' => array('msg'=>'重复提交,请5秒后重试!'));
            die(json_encode($result));
        }
        Yii::$app->cache->set($luckkey, 1, 5);       
        $model = array();
        $model['agentname'] = Yii::$app->request->post("agentname");  //子账号名称
        $model['realname'] = Yii::$app->request->post("realname");  //姓名
        $model['phone'] = Yii::$app->request->post("phone");  //手机
        $model['email'] = Yii::$app->request->post("email");  //邮箱
        $model['country'] = Yii::$app->request->post("country"); 
        $model['province'] = Yii::$app->request->post("province"); 
        $model['city'] = Yii::$app->request->post("city");
        $model['area'] = Yii::$app->request->post("area");
        $model['street'] = Yii::$app->request->post("street");
        $model['address'] = Yii::$app->request->post("address");  //地址  
        if (empty($model['agentname']) || empty($model['realname']) || empty($model['phone']) || empty($model['email']) || empty($model['address']) ) {           
            $data = array('status' => 1000,'extra' => array('msg'=>'参数不合法'));
            echo json_encode($data);exit;  
        }
        if (!DjiAgentUser::validate_is_email( $model['email'] ) ) {
                $data = array('status' => 1004,'extra' => array('msg'=>'邮箱格式不对'));
                echo json_encode($data);exit;  
        }    

        $model['username']  = $model['email'];
        $model['upper_agent_id']  = $agent_id;
        $findName = Agroagent::findByUsername($model['username']);
        if (empty($findName)) {  
            $redata =  $model;        
            $model['authKey'] = time();   
            $model['accessToken'] = $model['authKey']; 
            $model['password'] = uniqid('mpfd6ZE9gkLU9ox').$model['email'];                
            $password = md5($model['authKey'].$model['password']);
            $model['password'] =Yii::$app->getSecurity()->generatePasswordHash($password);
            $model['ip'] = $this->get_client_ip();
            $model['staff'] =  $model['operator'] = '';
            $addid = Agroagent::add($model);
            if ($addid > 0 ) {
                 $redata['id'] =  $addid;
                 $redata['status'] =  '审核中';
                 $data = array('status' => 200,'id' => $addid ,'data'=>$redata,'extra' => array('msg'=>''));
                 echo json_encode($data);exit; 
            
            }else{
                 $data = array('status' => 500,'extra' => array('msg'=>'系统忙,请稍后重试'));
                 echo json_encode($data);exit;  
            }
           
        }else{
            $data = array('status' => 1001,'extra' => array('msg'=>'用户已经存在'));
            echo json_encode($data);exit;  
        }      

    }

     /* 
     *  农业无人机公告 https://iuav.dji.com/adminagent/notice/地址 只支持get请求
     *  @parameter page  页面
     *  @parameter size  每页个数    
     */
    public function actionNotice()
    {
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'adminagent/notice');
        }
        $get_str = json_encode($_REQUEST);       
        $get_str .= "SERVER=".json_encode($_SERVER);
        //$this->add_log($get_str, 'apiadminagent_actionManagement');

        $session = Yii::$app->session;
        $agent_id = $session->get('AGENTUSERID');
        if ($agent_id < 1) {
            return $this->redirect('/adminagent/login/');
        }       
        $model = array();        
        $model['type'] = 'agent';
        $model['deleted'] = '0';   
        $page = Yii::$app->request->get('page', 1);
        $size = Yii::$app->request->get('size', 20);
        $page += 0;
        $size += 0;
        $start = ($page - 1) * $size;
        if ( $start < 0 ) $start = 0; 
        $luckkey = 'v32actionNotice'.md5(implode(',',$model)). $page.$size;
        $luckdata = Yii::$app->cache->get($luckkey);
        if ( $luckdata ) {
           return $this->renderSmartyTpl('notice.tpl', $luckdata);
        }
        $base_url = "/adminagent/notice/?a=1";
        $data = array();  
        $page_count = 0; 
        $count = Agronotice::getAndEqualWhereCount($model);   
              // var_dump($count);exit;     
        if ($count && $count == '0') {
             $status = 200;
             $result = array('status' => $status,'type' => $model['type'],'base_url' => $base_url,'data' => $data,'count' => $count,'page' => $page,'size' => $size,'page_count' => $page_count,'extra' => array('msg'=>''));
             Yii::$app->cache->set($luckkey, $result, 5);
             //var_dump($result);exit;
             return $this->renderSmartyTpl('notice.tpl', $result);
            
        }
        $page_count = ceil($count  / $size);        
        $data = Agronotice::getAndEqualWhere($model,$start,$size);
   
       
        $status = 200;
        $result = array('status' => $status,'type' => $model['type'],'base_url' => $base_url,'data' => $data,'count' => $count,'page' => $page,'size' => $size,'page_count' => $page_count,'extra' => array('msg'=>''));
        Yii::$app->cache->set($luckkey, $result, 5);      
        //var_dump($result);exit;
        return $this->renderSmartyTpl('notice.tpl', $result);         
       
    }
   

    protected function renderSmartyTpl($tpl,$list)
    {
        $list['CDNCONFIGURL'] = '';
        if (isset(Yii::$app->params['CDNCONFIG']) ) {
            $list['CDNCONFIGURL'] = Yii::$app->params['CDNCONFIG']['url'];
        } 
        $list['LANGDATA'] = array();
        $domain = YII_DEBUG ? '.dbeta.me' : '.dji.com';
        if (isset( $list['country']) && $list['country']  ) {
            //设置 cookie 
            $cookies = Yii::$app->response->cookies;
            // 在要发送的响应中添加一个新的cookie
            $cookies->add(new \yii\web\Cookie([
                'name'      => 'country',
                'value'     => strtoupper($list['country']),
                'expire'    => time() + 365 * 24 * 3600,
                'path'      => '/',
                'domain'    => $domain,
                'httpOnly'  => false,
            ]));
            $list['country'] = strtolower($list['country']);
        }else if (Yii::$app->request->cookies['country']) {
            $list['country'] = Yii::$app->request->cookies['country']->value;
            $list['country'] = strtolower($list['country']);
        }

        if( !in_array($list['country'], array('cn','us','kr','jp')) )
        {
            $list['country'] = 'cn';
             //设置 cookie 
            $cookies = Yii::$app->response->cookies;
            // 在要发送的响应中添加一个新的cookie
            $cookies->add(new \yii\web\Cookie([
                'name'      => 'country',
                'value'     => strtoupper($list['country']),
                'expire'    => time() + 365 * 24 * 3600,
                'path'      => '/',
                'domain'    => $domain,
                'httpOnly'  => false,
            ]));
        }
        if (file_exists(__DIR__ . '/../messages/'.$list['country'].'/lang.php')) {
                 $list['LANGDATA'] = require(__DIR__ . '/../messages/'.$list['country'].'/lang.php');
        } 
        //var_dump( $list['LANGDATA']);exit;

        $session = Yii::$app->session;
        $AGENTNAME = $session->get('AGENTNAME');
        $list['AGENTNAME'] = $AGENTNAME;
        $list['tpl_yii_env'] = YII_ENV;
        $UPPERAGENTID = $session->get('UPPERAGENTID');
        $list['UPPERAGENTID'] = $UPPERAGENTID;
        
        return $this->renderPartial($tpl,$list);
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
     // 写入文件
    protected function add_log($msg, $type = 'site_login')
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
    

}
