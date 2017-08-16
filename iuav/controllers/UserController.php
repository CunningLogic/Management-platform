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
use app\models\User;
use app\models\Agroevaluate;
use app\models\Agroreport;
use app\models\Agronotice;
use app\models\Agroteam;
use app\models\Agroflyer;
use app\models\Agrotask;
use app\models\Agroactiveflyer;
use GeoIp2\Database\Reader;
use app\components\DjiAgentUser;
use app\components\DjiUser;


class UserController extends Controller
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
       if (in_array($action->actionMethod , array('actionFeedback','actionEvaluate','actionAddteam','actionAddflyer'))) {
            $this->enableCsrfValidation = false;
        }
        parent::beforeAction($action);

        $session = Yii::$app->session;
        $loginTime = $session->get('IUAVUSERTIME');
        if ($loginTime) {
            $diff = time() - $loginTime;
            if ($diff > 800) {
                $DjiAgentUser = new DjiUser();
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

    //https://iuav.dji.com/user/
    public function actionIndex()
    {
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'user/index');
        }
        if (empty(Yii::$app->request->cookies['_meta_key']) )
        {
            return $this->redirect('/user/login/');
        }
        $ip = $_SERVER['REMOTE_ADDR'];
        $UserIP = Yii::$app->request->getUserIP();
        $error = '';
        if ($UserIP) {
            $luckkeyIP = 'UserController_actionLogin' . md5($UserIP);
            $luckdataIP = Yii::$app->cache->get($luckkeyIP);
            if ($luckdataIP > 36) {
                $error = "Incorrect username or password. " . $luckdataIP . "times";
            }
        } 
        $meta_key = Yii::$app->request->cookies['_meta_key']->value;
        //var_dump($meta_key);exit;
        $data = $newData = array();
        if (empty($error)) {
            $djiUser = new DjiUser();
            $userData = $djiUser->get_account_info_by_key($meta_key);
            //var_dump($userData);exit;
            if ($userData && $userData['status'] == '0' && $userData['status_msg'] == 'ok') {
              if ($userData['items']['0']['account_info']['user_id']) {
                 $model = array();
                 $model['uid'] =$userData['items']['0']['account_info']['user_id'];
                 $start = 0;
                 $size = 30;
                 $fields = 'agro_active_info.order_id,agro_active_info.apply_id,agro_active_info.body_code,agro_active_info.hardware_id,agro_active_info.activation,agro_apply_info.company_name,agro_apply_info.realname,agro_apply_info.phone';
                 $fields .=',agro_active_info.agent_id,agro_active_info.upper_agent_id,agro_active_info.created_at';
                 $data = Agroactiveinfo::getActiveWhere($model,$fields,$start,$size);                 
                 foreach ($data as $key => $value) {
                      $tmpNamePhone = Agroagent::getAgentNamePhone($value['agent_id']);
                      $value['agentname'] = $tmpNamePhone['agentname'];
                      $value['agentphone'] = $tmpNamePhone['phone'];
                      $tmpPol= Agropolicies::getPolNo($value['apply_id'],$value['order_id']);
                      $value['polnostr'] = $tmpPol['polnostr'];
                      $value['pol_no'] = $tmpPol['pol_no'];
                      $newData[] =$value;                      
                  }
              }        
            }else{ 
               Yii::$app->cache->set($luckkeyIP, $luckdataIP+1, 600);
               return $this->redirect('/user/logout/');
                //echo "dafasd";exit;        
            }    
            
        }else{ 
                   
             return $this->redirect('/user/logout/');
        }
        //var_dump($newData);exit;$list['AGENTNAME'] = '';
        $request = Yii::$app->getRequest();
        $error = "";
        $status = 200;
        $result = array('status' => $status,'data' => $newData,'AGENTNAME' => $userData['items']['0']['account_info']['nick_name'],'extra' => array('msg'=>''));
        //Yii::$app->cache->set($luckkey, $result, 5);      
        return $this->renderSmartyTpl('index.tpl', $result);       
    } 


    /*
     * 从售后系统读取数据 /user/maintaince/地址
     * 
     * $key = $consumer_secret."&".$token_secret
     * $sign = base64_encode(hash_hmac("sha1", $ticket, $key, true)); 
     *{"status": "0","message": "","data": [
     *   {  "CASENO": "SAS-14576-684427", --案例号
     *       "CASENAME": "SAS-21-本地维修",  --案例名称
     *       "CASESTATUS": "0",  --案例状态 0-已受理  3-已报价  99-确认收款 6-已维修 9-已发货
     *       "REMARK": "test", --案例说明
     *       "SENDERDATE": "2016/3/9 0:00:00", --收件日期
     *       "EXPRESSNO": "test",  --快递号
     *       "CREATETIME": "2016/3/11 11:54:01", --案例创建日期
     *       "NAMECN":"农机MG-1", --产品中文名
     *       "NAMEEN":"", --产品英文名
     *       "CUSTOMEREMAIL": "505801594@qq.com",--客户邮箱
     *       "CUSTOMERNAME": "test", --客户姓名
     *       "CUSTOMERADDR": "test", --客户地址
     *       "AGENTNAME": "21" --代理商名称
     *   }
     *]}
     *
    */
    public function actionMaintaince()
    {
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'user/maintaince');
        }
        // header("Content-Type: text/html; charset=utf-8");
        if (empty(Yii::$app->request->cookies['_meta_key']) )
        {
            return $this->redirect('/user/login/');
        }
        require(__DIR__ . '/../config/.config.php');
        $appid = isset($YII_GLOBAL['AFTERMARKET']['appid']) ? $YII_GLOBAL['AFTERMARKET']['appid'] : "DJIAFTERMARKET";
        $case_url = isset($YII_GLOBAL['AFTERMARKET']['case_url']) ? $YII_GLOBAL['AFTERMARKET']['case_url'] : '';
        $consumer_secret = isset($YII_GLOBAL['AFTERMARKET']['consumer_secret']) ? $YII_GLOBAL['AFTERMARKET']['consumer_secret'] : "NPvwThDNv6QH8z3iWmqwKB";
        $token_secret = isset($YII_GLOBAL['AFTERMARKET']['token_secret']) ? $YII_GLOBAL['AFTERMARKET']['token_secret'] : "iw2auVtCBgVtN7rGkY6sLH";
        $meta_key = Yii::$app->request->cookies['_meta_key'];
        $CASESTATUSList = array('0' =>'已受理','3' => '已报价','99'=>'确认收款','6'=>'已维修','9' =>'已发货');
        $djiUser = new DjiUser();
        $data = array('data' => array() );
        $newData = array();
        $userData = $djiUser->get_account_info_by_key($meta_key);
        if ($userData && $userData['status'] == '0' && $userData['status_msg'] == 'ok') {
            if ($userData['items']['0']['account_info']['email']) {
              
              $email = $userData['items']['0']['account_info']['email'];
              $DjiAgentUser = new DjiAgentUser();
              $data = $DjiAgentUser->getCaseInfoByEmail($case_url,$email,$consumer_secret, $token_secret);   
              //var_dump($data);exit;
              if ($data && $data['status'] == 0 && $data['data']) {

                foreach ($data['data'] as $key => $value) {
                  $evaluate = Agroevaluate::getCaseNo($value['CASENO']);
                  if ($evaluate) {
                     $value['evaluate'] = json_encode($evaluate);
                  }else{
                    $value['evaluate'] = '';
                  }
                  $value['CASESTATUS'] = isset( $CASESTATUSList[$value['CASESTATUS']]) ? $CASESTATUSList[$value['CASESTATUS']] : $value['CASESTATUS'];                  
                  $newData[] =$value;
                }
                
              }
              $data['AGENTNAME'] = $userData['items']['0']['account_info']['nick_name'];

            }
        }
        $data['data'] = $newData;        
        return $this->renderSmartyTpl('maintaince.tpl', $data);
    }

    /*
    * 评价售后  /user/evaluate
    *  @parameter caseno  CASENO
    *  @parameter totality  总体满意度 0~5
    *  @parameter speed  维修速度 0~5
    *  @parameter quality  质量 0~5
    *  @parameter attitude  态度 0~5
    *  @parameter message  其他留言
    *  
    */
    public function actionEvaluate()
    {
      if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'user/evaluate');
      }
      $model = array();   
      $model['caseno'] = Yii::$app->request->post("caseno");  //案例号
      $model['totality'] = Yii::$app->request->post("totality",0);  //总体满意度
      $model['speed'] = Yii::$app->request->post("speed",0);  //维修速度
      $model['quality'] = Yii::$app->request->post("quality",0);  //质量
      $model['attitude'] = Yii::$app->request->post("attitude",0);  //态度
      $model['message'] = Yii::$app->request->post("message",''); //其他留言
      if (empty($model['caseno']) || (empty($model['totality']) && empty($model['speed']) && empty($model['quality']) && empty($model['attitude']) && empty($model['message']) ) ) {
         $data = array('status' => 1000,'extra' => array('msg'=>'参数不合法'));
         echo json_encode($data);exit;
      }
      if (empty(Yii::$app->request->cookies['_meta_key']) )
      {
          $data = array('status' => 1001,'extra' => array('msg'=>'登录已经过期,请重新登录'));
          echo json_encode($data);exit;
      }
      $luckkey = 'UseractionEvaluate'.md5($model['caseno']);
      $luckdata = Yii::$app->cache->get($luckkey);
      if ( $luckdata ) {            
         $data = array('status' => 1004,'extra' => array('msg'=>'5秒内重复提交,请稍后重试'));
         echo json_encode($data);exit;
      }
      Yii::$app->cache->set($luckkey, 1, 5);

      $meta_key = Yii::$app->request->cookies['_meta_key'];
      $djiUser = new DjiUser();
      $data = array('data' => array() );
      $list = array();
      $userData = $djiUser->get_account_info_by_key($meta_key);
      if ($userData && $userData['status'] == '0' && $userData['status_msg'] == 'ok') {
        $redata = $model;
        $model['account'] = $userData['items']['0']['account_info']['email'];
        $model['user_id'] = $userData['items']['0']['account_info']['user_id'];
        $model['register_phone'] = $userData['items']['0']['account_info']['register_phone'];
        $model['ip'] = $this->get_client_ip();
        $result = Agroevaluate::add($model);
        if ($result && $result > 0) {
           $list['status'] = 200;     
           $list['addid'] = $result;  
           $list['data'] = $redata;               
           echo json_encode($list);exit;           
        }

      }
      $data = array('status' => 1002,'extra' => array('msg'=>'系统忙'));
      echo json_encode($data);exit;

    }

    //用户登录页面 user/login
    public function actionLogin()
    {
        if (extension_loaded ('newrelic')) {
            newrelic_name_transaction ( 'user/login');
        }

        $country = Yii::$app->request->get('country', 'cn'); 

        $djiUser = new DjiUser();         
        $url = $djiUser->getLoginUrl($djiUser->getBackUrl(),$djiUser->getLocale($country));
        Header("Location: $url "); 
        exit;
    }
     /*
    * 用户中心系统登录回调地址/user/loginback/
    */
    public function actionLoginback()
    {
         $ticket   = Yii::$app->request->get('ticket');
         $actionType   = Yii::$app->request->get('actionType');      
         $backUrl   = Yii::$app->request->get('backUrl');
         //$backUrl = urlencode($backUrl);
         $djiUser = new DjiUser();
         $result = $djiUser->get_meta_key_by_ticket($ticket,$actionType);  
         if (isset(Yii::$app->params['GWServer']) ) {
              $cookieDomain = Yii::$app->params['GWServer']['cookieDomain'];           
         }  
                
         if($result['status'] == 0 && isset($result['items'][0]['cookie_key']) && !empty($result['items'][0]['cookie_key']))
         {
            if ($actionType != 'logout') {                          
              $cookies = Yii::$app->response->cookies; 
              // 在要发送的响应中添加一个新的cookie
              $cookies->add(new \yii\web\Cookie([
                  'name'      => $result['items'][0]['cookie_name'],
                  'value'     => $result['items'][0]['cookie_key'],
                  'expire'    => time() + 86400,
                  'path'      => '/',
                  'domain'    => $cookieDomain,
                  'httpOnly'  => false,
              ]));  
            }                                                      
         } 
         $urlData = parse_url($backUrl);  
 
         if ($cookieDomain && substr($urlData['host'], -strlen($cookieDomain) ) != $cookieDomain ) {
             $backUrl = "https://www.dji.com/";            
         }      
         return $this->redirect($backUrl);
    }
    protected function isMobile()
    {
       $djiUser = new DjiUser();
       return $djiUser->isMobile();
    }

    protected function getLoginUrl($country)
    {
        $from = '>Uy^K)R8Rd$5!@6T^VQH}EVEo8ZD>b`1';
        if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']) {
           $data = base64_encode($_SERVER['HTTP_REFERER']);
        }else{
           $data = base64_encode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        }      
        //echo    $data;exit;
        $sign = md5($from . $data);

        $host = YII_DEBUG ? 'https://www.dbeta.me' : ($this->isMobile() ? 'https://m.dji.com' : 'https://www.dji.com');
        if (in_array($country,array('cn'))) {
            $url = $host . ($this->isMobile() ? '/cn/sessions/new' : '/cn/user/login');
        }elseif (in_array($country,array('tw','mo','hk'))) {
            $url = $host . ($this->isMobile() ? '/cn/sessions/new' : '/zh-tw/user/login');
        }else{
            $url = $host . ($this->isMobile() ? '/sessions/new' : '/user/login');
        }        
        return $url . '?from=dji-sticker&data=' . $data . '&sign=' .$sign;
    }

    protected function getLogoutUrl($country)
    {
        $from = '>Uy^K)R8Rd$5!@6T^VQH}EVEo8ZD>b`1';
        $data = base64_encode('http://'.$_SERVER['HTTP_HOST'].'/user/login/');  
        //echo 'http://'.$_SERVER['HTTP_HOST'].'/user/login/';exit;
       // echo    $data;exit;
        $sign = md5($from . $data);

        $host = YII_DEBUG ? 'https://www.dbeta.me' : ($this->isMobile() ? 'https://m.dji.com' : 'https://www.dji.com');
        if (in_array($country,array('cn'))) {
            $url = $host . ($this->isMobile() ? '/cn/sessions/new' : '/cn/user/logout');
        }elseif (in_array($country,array('tw','mo','hk'))) {
            $url = $host . ($this->isMobile() ? '/cn/sessions/new' : '/zh-tw/user/logout');
        }else{
            $url = $host . ($this->isMobile() ? '/sessions/new' : '/user/logout');
        } 
        return $url . '?from=dji-sticker&data=' . $data . '&sign=' .$sign;
    }


    //退出地址
    public function actionLogout()
    {                                                             
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'user/logout');
        }       
        $cookies = Yii::$app->response->cookies; 
        $cookies->remove('_meta_key'); //清除cookie
        $country = Yii::$app->request->get('country', 'cn');                         
        $djiUser = new DjiUser();  
        $BackUrl = 'https://'.$_SERVER['HTTP_HOST'].'/user/logoutback/?country='.$country;
        $url = $djiUser->getLogoutUrl($BackUrl);
        $this->redirect($url);
    }

    public function actionLogoutback()
    {          
        if (isset(Yii::$app->params['GWServer']) ) {
           $cookieDomain = Yii::$app->params['GWServer']['cookieDomain'];           
        }
        setcookie('_meta_key', null,  time() - 3600,'/',$cookieDomain,false);

        $country = Yii::$app->request->get('country', 'cn');
        $djiUser = new DjiUser();
        return $this->redirect('/user/login/?country='.$country);
    }
    /*
    *  用户意见反馈 接口地址 https://iuav.dji.com/user/feedback/ 只支持post请求
    *  @parameter type  类型
    *  @parameter title  标题
    *  @parameter message  留言内容
    *  @parameter isajax  为1表示ajax请求
    *
    */
    public function actionFeedback()
    {
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'user/feedback');
        }
        if (empty(Yii::$app->request->cookies['_meta_key']) )
        {
            return $this->redirect('/user/login/');
        }
        $model = $list = array();
        $meta_key = Yii::$app->request->cookies['_meta_key'];
        $djiUser = new DjiUser();
        $userData = $djiUser->get_account_info_by_key($meta_key);
        if ($userData['status'] == 0 && $userData['status_msg'] == 'ok') {
            $list['AGENTNAME'] = $userData['items']['0']['account_info']['nick_name'];
        }


       
        $model['type'] = Yii::$app->request->post("type");  //类型
        $model['title'] = Yii::$app->request->post("title");  //标题
        $model['message'] = Yii::$app->request->post("message"); //留言内容
        $model['isajax'] = Yii::$app->request->post("isajax"); 
        if ($model['type'] && $model['message'] && $model['title']) {   

          
           $luckkey = 'UseractionFeedback'.md5($meta_key);
           $luckdata = Yii::$app->cache->get($luckkey);
           if ( $luckdata ) {            
               $data = array('status' => 1004,'extra' => array('msg'=>'10秒内重复提交,请稍后重试'));
               echo json_encode($data);exit;
           }
           Yii::$app->cache->set($luckkey, 1, 10);          
           if ($userData['status'] == 0 && $userData['status_msg'] == 'ok') {
             $list['AGENTNAME'] = $userData['items']['0']['account_info']['nick_name'];
             $model['account'] = $userData['items']['0']['account_info']['email'];
             $model['user_id'] = $userData['items']['0']['account_info']['user_id'];
             $model['register_phone'] = $userData['items']['0']['account_info']['register_phone'];
             $model['ip'] = $this->get_client_ip();
             $result = Agroreport::add($model);
             if ($result && $result > 0) {
                 $list['addid'] = $result;
                 $list['status'] = 200;
                 if ($model['isajax'] == '1') {
                     echo json_encode($list);exit;
                 }
             }
           }else{
                $list['status'] = 1001;
                $list['extra'] = array('msg'=>'系统忙,请稍后重试');
                if ($model['isajax'] == '1') {
                     echo json_encode($list);exit;
                }
           }
           
        }
        return $this->renderSmartyTpl('feedback.tpl', $list);
    }

     /* 
     *  农业无人机公告 https://iuav.dji.com/user/notice/地址 只支持get请求
     *  @parameter page  页面
     *  @parameter size  每页个数    
     */
    public function actionNotice()
    {
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'user/notice');
        }
        $get_str = json_encode($_REQUEST);       
        $get_str .= "SERVER=".json_encode($_SERVER);
        //$this->add_log($get_str, 'apiadminagent_actionManagement');

        $session = Yii::$app->session;
        $agent_id = $session->get('AGENTUSERID');
        if ($agent_id < 1) {
            return $this->redirect('/user/login/');
        }       
        $model = array();        
        $model['type'] = 'client';
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
        $base_url = "/user/notice/?a=1";
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
        if (!isset($list['AGENTNAME'])) {
          $list['AGENTNAME'] = ''; 
        }           
        $list['UPPERAGENTID'] = '';
        $list['tpl_yii_env'] = YII_ENV;
        
        return $this->renderPartial($tpl,$list);
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

    protected function jsonResponse($data)
    {
        header('Content-type: application/json ; charset=UTF-8');
        echo json_encode($data);exit;
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
