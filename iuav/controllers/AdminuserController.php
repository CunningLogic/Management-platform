<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;

use app\models\User;
use app\models\Role;
use app\models\Rolepurview;
use app\models\Purview;
use app\models\OperationLog;
use app\models\Email;
use app\models\Agroagent;
use app\models\Agronotice;
use app\models\Agroagentmis;
use app\components\DjiAgentUser;
use app\models\Agroactiveinfo;
use app\models\Agropolicies;
use app\models\Agroreport;
use app\models\Agroreportreply;
use app\models\Agroagentbody;
use app\models\Agroapplyinfo;
use app\components\AdminUser;
use app\components\DjiUser;

class AdminuserController extends Controller
{
    //public $enableCsrfValidation = false;

    public function init() {
        parent::init();
        //用户id
        //$userId = Yii::$app->user->getId();
      //  echo $userId."------";
      // var_dump(Yii::$app);
       // exit;
    }
    public function beforeAction($action)
    {
         parent::beforeAction($action);
         $userId = Yii::$app->user->getId();
         $adminUser = new AdminUser();
         //判断权限
         $adminUser->checklogin(__CLASS__.'::'.$action->actionMethod);
         return true;        
    }
        
    public function behaviors()
    {
        return [
            'access' => [
                'class'  => AccessControl::className(),
                'rules'  => [
                    [
                        'actions' => ['adfsafdsa21321'], 
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['index','add','edit','del','view','list','addrole','listrole','addpurview','listpurview',
                                     'password','addrolepurview','adduserrole','addagent','listagentmis','listagent','listapply','findapply','addagentpending','listagentpending','addnotice','addapply','listnotice','nopolicies','listpolicies','listreport','addreport','totalapply'], 
                        'allow' => true,
                        'roles' => ['@'],
                        'denyCallback' => function ($rule, $action) {
                           $this->redirect('/admin/');
                        }
                    ],
                ],
            ],
            'verbs' => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
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
    
    public function jsonResponse($data)
    {
        header('Content-type: text/json');
        echo json_encode($data);exit;
    }
    
    public function getLimit()
    {
        $page = Yii::$app->request->get('page', 1);
        $size = Yii::$app->request->get('size', 20);
        $start = ($page - 1) * $size;
        if ( $start < 0 ) $start = 0;
        
        return [$start, $size];
    }

    public function actionPassword()
    {
        $get_str = json_encode($_REQUEST);       
        $get_str .= "SERVER=".json_encode($_SERVER);
        $this->add_log($get_str, 'adminuser_add');
        $LoginFormList = Yii::$app->request->post("LoginForm");
        $id =Yii::$app->request->post('id', '');
        $list = array();
        $request = Yii::$app->getRequest(); 
       
        $tpl = array('csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() );
        $tpl['error_oldpassword'] = ''; 
        $tpl['error_newpassword'] = ''; 
        $tpl['error_retpassword'] = ''; 
        $tpl['username'] = Yii::$app->user->identity->username; 


        if (empty($LoginFormList['oldpassword']) ) {           
           return $this->renderSmartyTpl('password.tpl', $tpl);          
        }else{
           $id =Yii::$app->request->post('id', '');  
           $LoginFormList['username'] = Yii::$app->user->identity->username;
           $LoginFormList['id'] = Yii::$app->user->identity->id;
           Yii::$app->user->identity->username;
           $authKey = Yii::$app->user->identity->authKey;          
           $password = md5($authKey.$LoginFormList['oldpassword']);
           $validate = Yii::$app->getSecurity()->validatePassword($password, Yii::$app->user->identity->password);
           if (empty($LoginFormList['oldpassword']) || empty($LoginFormList['newpassword']) || empty($LoginFormList['retpassword']) || $LoginFormList['retpassword'] != $LoginFormList['newpassword'] ) {
                $tpl['error_oldpassword'] = '参数不合法';  
           }else{
               if ($validate) {
                    $password = md5($authKey.$LoginFormList['newpassword']);
                    $LoginFormList['password'] =Yii::$app->getSecurity()->generatePasswordHash($password);
                    $result = user::updatePasswordInfo($LoginFormList);
                    if ($result> 0) {
                         $this->redirect('/admin/logout');
                    }                    

               }else{
                   $tpl['error_oldpassword'] = '密码输入有误'; 
               }
           }
           
        }
        return $this->renderSmartyTpl('password.tpl', $tpl);
        exit;   
      
    }  
    public function actionList()
    {
        $LoginFormList = Yii::$app->request->post("LoginForm");
        $request = Yii::$app->getRequest();  
        $list = array(); 
        $list = user::get(0,50);
        if (empty($list)) {
           
        }        
        return $this->renderSmartyTpl('list.tpl', ['LIST' => $list,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);
        exit;     
      
    }    
    public function actionAdd()
    {
        $get_str = json_encode($_REQUEST);       
        $get_str .= "SERVER=".json_encode($_SERVER);
        $this->add_log($get_str, 'adminuser_add');
        $LoginFormList = Yii::$app->request->post("LoginForm");
        $id =Yii::$app->request->post('id', '');
        $list = array();
        $request = Yii::$app->getRequest();  

        if (empty($LoginFormList['username']) || (empty($LoginFormList['password']) && empty($id)) ) {
            $id =Yii::$app->request->get('id', '');
            if ($id > 0 ) {
                $list = user::get(0,1,array('id'=>$id));
               
            }           
           return $this->renderSmartyTpl('add.tpl', ['LIST' => $list,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);          
        }else{
           $id =Yii::$app->request->post('id', '');
          
           if ($id > 0 ) {
                if ($LoginFormList['password']) {
                    $LoginFormList['authKey'] = time();                    
                    $password = md5($LoginFormList['authKey'].$LoginFormList['password']);
                    $LoginFormList['password'] =Yii::$app->getSecurity()->generatePasswordHash($password);
                    
                }
                $LoginFormList['id'] = $id;               
                $result = user::updateInfo($LoginFormList);
                if ($result> 0) {
                       $this->redirect('/adminuser/list/');
                }
           }else{
               $find = user::findByUsername($LoginFormList['username']);
               if (empty($find)) {
                   $LoginFormList['email'] = $LoginFormList['username'];
                   $LoginFormList['phone'] = '';
                   $LoginFormList['authKey'] = time();
                   $LoginFormList['accessToken'] = $LoginFormList['authKey'];
                   $password = md5($LoginFormList['authKey'].$LoginFormList['password']);
                   require '../lib/PHPGangsta/GoogleAuthenticator.php';
                   $ga = new \PHPGangsta_GoogleAuthenticator();
                   $LoginFormList['google_auth'] = $ga->createSecret();

                   $LoginFormList['password'] =Yii::$app->getSecurity()->generatePasswordHash($password);
                   $result = user::add($LoginFormList);
                   if ($result> 0) {
                       $this->redirect('/adminuser/list/');
                   }
               }
           }          

        }
        return $this->renderSmartyTpl('add.tpl', ['LIST' => $list,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);
        exit;     
      
    }
     public function actionListrole()
    {
        $LoginFormList = Yii::$app->request->post("LoginForm");
        $request = Yii::$app->getRequest();  
        $list = array(); 
        $list = Role::get(0,30);
        if (empty($list)) {
           
        }        
        return $this->renderSmartyTpl('listrole.tpl', ['LIST' => $list,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);
        exit;     
      
    }    


    public function actionAddrole()
    {
        $get_str = json_encode($_REQUEST);       
        $get_str .= "SERVER=".json_encode($_SERVER);
        $this->add_log($get_str, 'adminuser_add');
        $LoginFormList = Yii::$app->request->post("LoginForm");
        $id =Yii::$app->request->post('id', '');
        $list = array();
        $request = Yii::$app->getRequest();  

        if (empty($LoginFormList['name']) ) {
            $id =Yii::$app->request->get('id', '');
            if ($id > 0 ) {
                $list = Role::get(0,1,array('id'=>$id));
               
            }           
           return $this->renderSmartyTpl('addrole.tpl', ['LIST' => $list,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);          
        }else{
           $id =Yii::$app->request->post('id', '');
          
           if ($id > 0 ) {
                $LoginFormList['id'] = $id;               
                $result = Role::updateInfo($LoginFormList);
                if ($result> 0) {
                       $this->redirect('/adminuser/listrole/');
                }
           }else{
               $find = Role::findByUsername($LoginFormList['name']);
               if (empty($find)) {
                   $result = Role::add($LoginFormList);
                   if ($result> 0) {
                       $this->redirect('/adminuser/listrole/');
                   }
               }
           }          

        }
        return $this->renderSmartyTpl('addrole.tpl', ['LIST' => $list,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);
        exit;     
      
    }

    public function actionListpurview()
    {
        $LoginFormList = Yii::$app->request->post("LoginForm");
        $request = Yii::$app->getRequest();  
        $list = array(); 
        $list = Purview::get(0,50);
        if (empty($list)) {
           
        }        
        return $this->renderSmartyTpl('listpurview.tpl', ['LIST' => $list,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);
        exit;     
      
    }    


    public function actionAddpurview()
    {
        
        $get_str = json_encode($_REQUEST);       
        $get_str .= "SERVER=".json_encode($_SERVER);
        $this->add_log($get_str, 'adminuser_add');
        $LoginFormList = Yii::$app->request->post("LoginForm");
        $id =Yii::$app->request->post('id', '');
        $list = array();
        $request = Yii::$app->getRequest();
        $listPurview = Purview::get(0,50);  

        if (empty($LoginFormList['method']) ) {
            $id =Yii::$app->request->get('id', '');
            if ($id > 0 ) {
                $list = Purview::get(0,1,array('id'=>$id));
               
            }           
           return $this->renderSmartyTpl('addpurview.tpl', ['LIST' => $list,'listPurview' => $listPurview,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);          
        }else{
           $id =Yii::$app->request->post('id', '');
          
           if ($id > 0 ) {
                $LoginFormList['id'] = $id;               
                $result = Purview::updateInfo($LoginFormList);
                if ($result> 0) {
                       $this->redirect('/adminuser/listpurview/');
                }
           }else{
               $find = Purview::findByUsername($LoginFormList['method']);
               //var_dump($find);exit;
               if (empty($find)) {
                   $result = Purview::add($LoginFormList);
                   if ($result> 0) {
                       $this->redirect('/adminuser/listpurview/');
                   }
               }
           }          

        }
        return $this->renderSmartyTpl('addpurview.tpl', ['LIST' => $list,'listPurview' => $listPurview,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);
        exit;     
      
    }
    //增加权限
    public function actionAddrolepurview()
    {
        $get_str = json_encode($_REQUEST);       
        $get_str .= "SERVER=".json_encode($_SERVER);
        $this->add_log($get_str, 'adminuser_add');
        $LoginFormList = Yii::$app->request->post("LoginForm");
        $id =Yii::$app->request->post('id', '');
        $roleid = Yii::$app->request->post('roleid', '');
        $purviewids = Yii::$app->request->post('purviewids', '');


        $list = $listrole = $purviewidlist = array();
        $request = Yii::$app->getRequest();  

        if (empty($LoginFormList) ) {
            $id =Yii::$app->request->get('id', '');
            $roleid =Yii::$app->request->get('roleid', '');
            if ($id > 0 ) {
               // $list = Rolepurview::get(0,1,array('id'=>$id));
               
            }
        }else{
           if ($roleid > 0 && $purviewids && $LoginFormList['action'] ) {

                $LoginFormList['id'] = $id;               
                $result = Rolepurview::updateInfo($roleid,$purviewids,$LoginFormList['action']);
                if ($result> 0) {
                   $this->redirect('/adminuser/addrolepurview/?roleid='.$roleid);
                }
           }
        }
        if ($roleid > 0 ) {
                $listrole = Rolepurview::get(0,50,array('role_id'=>$roleid,'deleted' => 0));   
                foreach ($listrole as $key => $value) {
                      $purviewidlist[] = $value['purview_id'];
                }            
        } 
        $listpurview = purview::get(0,50); 
        if ($listpurview) {
            foreach ($listpurview as $key => $value) {
                if (in_array($value['id'], $purviewidlist)) {
                   $listpurview[$key]['ishave'] = 1;
                }else{
                   $listpurview[$key]['ishave'] = 0;
                }
               
            } 
        }       
        //var_dump($listrole,$listpurview);exit;
        return $this->renderSmartyTpl('addrolepurview.tpl', ['listpurview' => $listpurview,'listrole' => $listrole,'roleid' => $roleid,'LIST' => $list,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);          

      
    }

    //对应的角色增加用户
    public function actionAdduserrole()
    {
        $get_str = json_encode($_REQUEST);       
        $get_str .= "SERVER=".json_encode($_SERVER);
        $this->add_log($get_str, 'adminuser_add');
        $LoginFormList = Yii::$app->request->post("LoginForm");
        $id =Yii::$app->request->post('id', '');       

        $list = $listrole = $listuser = array();
        $request = Yii::$app->getRequest();  

        if (empty($LoginFormList) ) {
            $id =Yii::$app->request->get('id', '');
            $roleid =Yii::$app->request->get('roleid', '');
            if ($id > 0 ) {
               // $list = Rolepurview::get(0,1,array('id'=>$id));               
            }
        }else{
          //var_dump(expression)
           if ($LoginFormList ) {
                $LoginFormList['id'] = $LoginFormList['user_id'];                       
                $result = User::updateRoleidInfo($LoginFormList);
                if ($result> 0) {
                   $this->redirect('/adminuser/list/');
                }
           }
        }
        $listrole = Role::get(0,50,array('deleted' => 0));                      
         
        if ($id > 0 ) {
           $listuser = User::get(0,1,array('id'=>$id,'deleted' => 0)); 
        }else{
           $listuser = User::get(0,50,array('deleted' => 0));

        } 
       
        //var_dump($listrole,$listuser);exit;
        return $this->renderSmartyTpl('adduserrole.tpl', ['listuser' => $listuser,'listrole' => $listrole,'roleid' => $roleid,'LIST' => $list,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);          

      
    }

    //申请审批代理列表
    public function actionListagentpending()
    {
        //$upper_agent_id = Yii::$app->request->get("upper_agent_id",0);
        $request = Yii::$app->getRequest();  
        $list = array(); 
        $page = Yii::$app->request->get('page', 1);
        $size = Yii::$app->request->get('size', 30); 
        $status = Yii::$app->request->get('status', 'pending');         
        $start = ($page - 1) * $size;
        if ( $start < 0 ) $start = 0;   

        $where = array('deleted'=> 0,'status' => $status);
        $list = Agroagent::getAndEqualWhere($where, $start, $size);
        
        $count = 0;
        if ($list){
           foreach ($list as $key => $value) {
             $tmpNamePhone = Agroagent::getAgentNamePhone($value['upper_agent_id']);
             $list[$key]['upperagentname'] = $tmpNamePhone['agentname'];
             $list[$key]['uppercode'] = $tmpNamePhone['code'];             
           }
           $count = Agroagent::getAndEqualWhereCount($where);          
        }else if ($page > 1) {
           $this->redirect("/adminuser/listagentpending/?status=".$status."&page=1&size=".$size); 
        }

        $page_count = ceil($count / $size);
        $base_url = "/adminuser/listagentpending/?status=".$status;
        $callback_url = base64_encode("/adminuser/listagentpending/?status=".$status."&page=".$page."&size=".$size);
        return $this->renderSmartyTpl('listagentpending.tpl', ['LIST' => $list,'base_url' => $base_url,'callback_url' => $callback_url,'page' => $page,'page_count' => $page_count,'size' => $size,'count' => $count,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);
        
      
    }  
    //审批或者拒绝代理用户的申请
    public function actionAddagentpending()
    {
        $get_str = json_encode($_REQUEST);       
        $get_str .= "SERVER=".json_encode($_SERVER);
        $this->add_log($get_str, 'adminuser_addagentpending');
        $id = Yii::$app->request->get("id",0);
        $action = Yii::$app->request->get("action",'');
        $callurl = Yii::$app->request->get("callurl",'');
        if ($callurl) {
          $callurl = base64_decode($callurl);
        }else{
           $callurl = '/adminuser/listagentpending/';

        }        
        if ($id > 0 && in_array($action, array('agree','disagree'))) {
          
          $where = array(); 
          $where['id'] = $id;   
          $where['status'] = $action; 
          $where['operator'] = Yii::$app->user->identity->username; 
          $where['ip'] = $this->get_client_ip(); 
          $result = Agroagent::updatePending($where);  
          if ($result > 0 && $action == 'agree') {
                $where = array(); 
                $where['id'] = $id;   
                $agentData = Agroagent::getAndEqualWhere($where,0,1);
                $email = $agentData['0']['username'];
                $datetime = time();
                $timeout = 3600*3;
                $DjiAgentUser = new DjiAgentUser();               
                $code = $DjiAgentUser->get_password($email,$datetime,$timeout);
                require(__DIR__ . '/../config/.config.php');                
                $url = $YII_GLOBAL['AGENTGETPASSWORD']['url']."adminagent/resetpassword/?code=".$code.'&datetime='.$datetime;
                $address = $email;
                $name = $email;
                $currency = 'cn';
                $comfile = __DIR__.'/../commands/PasswordSendEmail.php';
                // echo "nohup php $comfile $address $coupon '$name' > /dev/null &";
                @system("php $comfile $address \"$url\"  \"$name\" \"$currency\"  > /dev/null & ");  

          }

        } 
        $this->redirect($callurl);      
    }  

    //mis系统代理列表
    public function actionListagentmis()
    {
        $isGuest = Yii::$app->user->isGuest;
        
        $upper_agent_id = Yii::$app->request->get("upper_agent_id",0);
        $page = Yii::$app->request->get('page', 1);
        $size = Yii::$app->request->get('size', 30);  
        $code = Yii::$app->request->post("code");
        if (empty($code)) {
            $code = Yii::$app->request->get("code");
        }
        $misuid = Yii::$app->request->post("misuid");
        if (empty($misuid)) {
            $misuid = Yii::$app->request->get("misuid");
        }
        $email = Yii::$app->request->post("email");
        if (empty($email)) {
            $email = Yii::$app->request->get("email");
        }

        $start = ($page - 1) * $size;
        if ( $start < 0 ) $start = 0;   
        $request = Yii::$app->getRequest();  
        $list = array();   
        $where = array('deleted'=> 0);
        if (isset($code) && $code) {
            $where['code'] = trim($code); 
            $code =  htmlspecialchars($code, ENT_QUOTES);
        }
        if (isset($misuid) && $misuid) {
            if ($misuid === 'null') {
              $where['misuid'] = null; 
            }else{
              $where['misuid'] = trim($misuid); 
            }
            $misuid =  htmlspecialchars($misuid, ENT_QUOTES);
        }
        if (isset($email) && $email) {
            if ($email === 'null') {
              $where['email'] = null; 
            }else{
              $where['email'] = trim($email); 
            }
            
            $email =  htmlspecialchars($email, ENT_QUOTES);
        }


        $list = Agroagentmis::getAndEqualWhere($where, $start, $size);
        $count = 0;
        if (empty($list)) {
           
        }else{
           $count = Agroagentmis::getAndEqualWhereCount($where);
          
        }
        $page_count = ceil($count / $size);
        $base_url = "/adminuser/listagentmis/?upper_agent_id=".$upper_agent_id.'&code='.$code;
        return $this->renderSmartyTpl('listagentmis.tpl', ['email' => $email,'misuid' => $misuid,'code' => $code,'isGuest' => $isGuest,'LIST' => $list,'base_url' => $base_url,'page' => $page,'page_count' => $page_count,'size' => $size,'count' => $count,'upper_agent_id' => $upper_agent_id,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);
        exit;     
      
    }    


    //代理列表
    public function actionListagent()
    {
        $isGuest = Yii::$app->user->isGuest;        
        $code = $this->getPostValue("code");
        $username = $this->getPostValue("username");
        $upper_agent_id = Yii::$app->request->get("upper_agent_id",0);
        $upper_agent_id = intval($upper_agent_id);
        $page = Yii::$app->request->get('page', 1);
        $size = Yii::$app->request->get('size', 30);      
        $start = ($page - 1) * $size;
        if ( $start < 0 ) $start = 0;   
        $request = Yii::$app->getRequest();  
        $list = array();   
        $where = array('deleted'=> 0,'upper_agent_id' => $upper_agent_id);
        
        if (isset($code) && $code) {
            $where['code'] = trim($code); 
            $code =  htmlspecialchars($code, ENT_QUOTES);
        }
        if (isset($username) && $username) {
            $where['username'] = trim($username); 
            $username =  htmlspecialchars($username, ENT_QUOTES);
        }

        $list = Agroagent::getAndEqualWhere($where, $start, $size);
        $count = 0;
        if (empty($list)) {
           
        }else{
           $count = Agroagent::getAndEqualWhereCount($where);
          
        }
        $page_count = ceil($count / $size);
        $base_url = "/adminuser/listagent/?upper_agent_id=".$upper_agent_id."&code=".$code;
        return $this->renderSmartyTpl('listagent.tpl', ['username' => $username,'code' => $code,'isGuest' => $isGuest,'LIST' => $list,'base_url' => $base_url,'page' => $page,'page_count' => $page_count,'size' => $size,'count' => $count,'upper_agent_id' => $upper_agent_id,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);
        exit;     
      
    }    
    //增加代理用户
    public function actionAddagent()
    {
        $get_str = json_encode($_REQUEST);       
        $get_str .= "SERVER=".json_encode($_SERVER);
        $this->add_log($get_str, 'adminuser_addagent');
        $upper_agent_id = Yii::$app->request->get("upper_agent_id",0);
        $LoginFormList = Yii::$app->request->post("LoginForm");
        $id =Yii::$app->request->post('id', '');
        $from = Yii::$app->request->get("from",'');
        $list = array();
        $request = Yii::$app->getRequest(); 
        if (empty($LoginFormList['email']) || (empty($LoginFormList['password']) && empty($id))  ) {
            $id =Yii::$app->request->get('id', '');
            
            if ($id > 0 ) {
                 $start = 0;
                $limit = 1;
                $where = array('id'=>$id);
                if ($from == 'mis') {
                   $list = Agroagentmis::getAndEqualWhere($where, $start, $limit); 
                   $upper_agent_id = '';
                }else{
                   $list = Agroagent::getAndEqualWhere($where, $start, $limit); 
                    $upper_agent_id =  $list[0]['upper_agent_id'];   
                }                  
                      
               
            }           
           return $this->renderSmartyTpl('addagent.tpl', ['from' => $from,'LIST' => $list,'upper_agent_id' => $upper_agent_id,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);          
        }else{
           $id =Yii::$app->request->post('id', '');
           $LoginFormList['operator'] = Yii::$app->user->identity->username; 
           $LoginFormList['ip'] = $this->get_client_ip(); 
           $upper_agent_id = $LoginFormList['upper_agent_id'];  
           $LoginFormList['username'] = $LoginFormList['email'];        
           if ($id > 0 ) {
                if ($LoginFormList['password']) {
                    $LoginFormList['authKey'] = time();   
                    $LoginFormList['accessToken'] = $LoginFormList['authKey'];                 
                    $password = md5($LoginFormList['authKey'].$LoginFormList['password']);
                    $LoginFormList['password'] =Yii::$app->getSecurity()->generatePasswordHash($password);
                    
                }
                $LoginFormList['id'] = $id;               
                $result = Agroagent::updateInfo($LoginFormList);
                if ($result> 0) {
                       $this->redirect('/adminuser/listagent/?upper_agent_id='.$upper_agent_id);
                }
           }else{
               $find = Agroagent::findByUsername($LoginFormList['username']);

               if (empty($find)) {
                   //$LoginFormList['email'] = $LoginFormList['username'];                  
                   $LoginFormList['authKey'] = time();
                   $LoginFormList['accessToken'] = $LoginFormList['authKey'];
                   $password = md5($LoginFormList['authKey'].$LoginFormList['password']);

                   $LoginFormList['password'] =Yii::$app->getSecurity()->generatePasswordHash($password);
                   $result = Agroagent::add($LoginFormList);
                   if ($result> 0) {
                       $this->redirect('/adminuser/listagent/?upper_agent_id='.$upper_agent_id);
                   }
               }
           }          

        }
        return $this->renderSmartyTpl('addagent.tpl', ['from' => $from,'LIST' => $list,'upper_agent_id' => $upper_agent_id,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);
        exit;     
      
    }
    
    /**
     * 检查异常的激活记录没有发送给保险公司
     */
    public function actionNopolicies()
    {        
        $fields = 'agro_apply_info.id,agro_apply_info.order_id,agro_apply_info.realname,agro_apply_info.idcard,agro_apply_info.phone,agro_apply_info.updated_at';
        $model = array('agro_policies_id' => 'isnull','is_policies' => 1);         
        $policieslist = Agroapplyinfo::getPoliciesWhere($model,$fields); 
        if (is_array($policieslist) && $policieslist) {
           
        }
        $request = Yii::$app->getRequest();  
        //var_dump($policieslist );exit;
        return $this->renderSmartyTpl('nopolicies.tpl', ['LIST' => $policieslist,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]); 

    }  
   
    //保险列表
    public function actionListpolicies()
    {
        $page = Yii::$app->request->get('page', 1);
        $size = Yii::$app->request->get('size', 30);
        $apply_id = $this->getPostValue("apply_id");
        $query_id = $this->getPostValue("query_id");
        $order_id = $this->getPostValue("order_id");
        $pol_no = $this->getPostValue("pol_no");
        $begin = $this->getPostValue("begin");
        $end = $this->getPostValue("end");
        $start = ($page - 1) * $size;
        if ( $start < 0 ) $start = 0;   
        $request = Yii::$app->getRequest();  
        $list = array();   
        $where = array('deleted'=> 0);
        if (isset($apply_id) && $apply_id) {
            $where['apply_id'] = trim($apply_id); 
        }
        if (isset($order_id) && $order_id) {
            $where['order_id'] = trim($order_id); 
        }
        if (isset($query_id) && $query_id) {
            $where['query_id'] = trim($query_id); 
        }
        if (isset($pol_no) && $pol_no) {
            if ($pol_no == 'null') {
              $where['pol_no'] = '';
            }else{
              $where['pol_no'] = trim($pol_no); 
            }            
        }
        if (isset($begin) && $begin) {
            $where['begin'] = trim($begin); 
        }
        if (isset($end) && $end) {
            $where['end'] = trim($end); 
        }
        $fields = "*";
        $list = Agropolicies::getSearchWhere($where, $fields,$start, $size);       
        $count = 0;
        if (empty($list)) {
           
        }else{
           $fields = "count(*) as allcount";
           $count = Agropolicies::getSearchWhere($where,$fields); 
           if (is_array($count)) {
               $count = $count['0']['allcount'];
           }         
        }
        $page_count = ceil($count / $size);        
        $base_url = "/adminuser/listpolicies/?".http_build_query($where);
        $tplArray = ['apply_id' => $apply_id,'query_id' => $query_id,'order_id' => $order_id,'pol_no' => $pol_no,'LIST' => $list,'base_url' => $base_url,'page' => $page,'page_count' => $page_count,'size' => $size,'count' => $count,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ];
        $tplArray['begin'] = $begin;
        $tplArray['end'] = $end;
        return $this->renderSmartyTpl('listpolicies.tpl', $tplArray);          
    }

    public function actionTotalapply()
    {     
      $beginpost = Yii::$app->request->post('begin');
      $endpost = Yii::$app->request->post('end');
      $month = Yii::$app->request->post("month");     
      if (empty($month) && empty($beginpost) && empty($endpost) ) {
        $month = Yii::$app->request->get("month",date("Y-m",time()));
      }else if ($beginpost && $endpost) {
        $month = null;
      }    
      $where = array();
      if ($month) {
        $begin = $month."-01";
        $end = $month."-31";
      }else{
        $begin = $beginpost;
        $end = $endpost;
      } 
      $today = date('Y-m-d',time());
      $todayCount = Agroactiveinfo::getWhereTodayCount($where,$today,$today);
      $activeData = Agroactiveinfo::getWhereGroupCount($where,$begin,$end);
      $codeActive = $stock = array();
      if (is_array($activeData)) {
        foreach ($activeData as $key => $value) {
           $tmpNamePhone = Agroagent::getAgentNamePhone($value['upper_agent_id']);
           $activeData[$key]['agentname'] = $tmpNamePhone['agentname'];
           $activeData[$key]['code'] = $tmpNamePhone['code'];
           $codeActive[$tmpNamePhone['code']] = $value['total_mon'];
        }
      }
       
      $bodyData = Agroagentbody::getWhereGroupCount($where,$begin,$end);
      if (is_array($bodyData)) {
        foreach ($bodyData as $key => $value) {
             if (isset($codeActive[$value['code']])) {
                $bodyData[$key]['stocknum'] = $value['total_mon'] - $codeActive[$value['code']];
                $bodyData[$key]['activenum'] = $codeActive[$value['code']];
             }else{
                $bodyData[$key]['activenum'] = 0;
                $bodyData[$key]['stocknum'] = $value['total_mon'];
             }
             $realnameArray = Agroagentmis::getRealNameWithCode($value['code']);
             $bodyData[$key]['staff'] = $realnameArray['staff'];
        }
      }
      $request = Yii::$app->getRequest();  
      return $this->renderSmartyTpl('totalapply.tpl', ['month' => $month,'begin' => $beginpost,'end' => $endpost,'bodyData' => $bodyData,'activeData' => $activeData,'todayCount' => $todayCount,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);
      exit;  

    }   

    //激活码和激活用户列表
    public function actionListapply()
    {       
        $page = Yii::$app->request->get('page', 1);
        $size = Yii::$app->request->get('size', 30); 
        $body_code = $this->getPostValue("body_code");
        $country = $this->getPostValue("country");
        $province = $this->getPostValue("province");
        $city = $this->getPostValue("city");
        $agentname = $this->getPostValue("agentname");
        $account = $this->getPostValue("account");
        $begin = $this->getPostValue("begin");
        $end = $this->getPostValue("end");
        $code =$this->getPostValue("code");
        $agent_id = $this->getPostValue("agent_id");
      
        $start = ($page - 1) * $size;
        if ( $start < 0 ) $start = 0;   
        $request = Yii::$app->getRequest();  
        $list = array();   
        $where = array();
        if (isset($body_code) && $body_code) {
            $where['body_code'] = trim($body_code); 
        }
        if (isset($country) && $country) {
            $where['country'] = trim($country); 
        }        
        if (isset($province) && $province) {
            $where['province'] = trim($province); 
        }
        if (isset($city) && $city) {
            $where['city'] = trim($city); 
        }
        if (isset($account) && $account) {
            $where['account'] = trim($account); 
        }
        if (isset($begin) && $begin) {
            $where['begin'] = trim($begin); 
        }
        if (isset($end) && $end) {
            $where['end'] = trim($end); 
        }
        if (isset($code) && $code) {
            $code_array = Agroagent::getAgentNameForCode($code);
            $where['upper_agent_id'] = $code_array['id']; 
        }
        if (isset($agent_id) && $agent_id) {          
            $where['upper_agent_id'] = $agent_id; 
        }

        $fields = 'count(*) AS activeCount';
        $count = Agroactiveinfo::getActiveWhere($where,$fields); 
        $activeCount = 0;  
        $newData = array();        
        if ($count && $count['0']['activeCount'] > 0 ) {
          $activeCount = $count['0']['activeCount'];      
          $fields = 'agro_active_info.id,agro_active_info.order_id,agro_active_info.apply_id,agro_active_info.body_code,agro_active_info.hardware_id,agro_active_info.activation,agro_apply_info.company_name,agro_apply_info.account,agro_apply_info.realname,agro_apply_info.phone';
          $fields .=',agro_active_info.agent_id,agro_active_info.upper_agent_id,agro_active_info.created_at,agro_apply_info.country,agro_apply_info.province,agro_apply_info.city,agro_apply_info.area,agro_apply_info.street,agro_apply_info.address';
          
          $data = Agroactiveinfo::getActiveWhere($where,$fields,$start,$size);

              
          foreach ($data as $key => $value) {
              $agentnameArr = Agroagent::getAgentNamePhone($value['agent_id']);
              $upperagentnameArr = Agroagent::getAgentNamePhone($value['upper_agent_id']);
              $value['agentname'] = $agentnameArr['agentname'];
              $value['upperagentname'] = $upperagentnameArr['agentname'];
              $value['is_agent_apply'] = '';
              if ($agentnameArr['username'] == $value['account'] || $upperagentnameArr['username'] == $value['account']) {
                 $value['is_agent_apply'] = '是';
              }

              $tmpPol= Agropolicies::getPolNo($value['apply_id'],$value['order_id']);
              $value['polnostr'] = $tmpPol['polnostr'];
              $value['pol_no'] = $tmpPol['pol_no'];
              $value['created_date'] = substr($value['created_at'], 0,10);
              $value['created_time'] = substr($value['created_at'], 11);
              $newData[] =$value;
              
          }
        }
        $where = array('deleted'=> 0,'upper_agent_id' => '0');
        $listAgent = Agroagent::getAndEqualWhere($where, 0, 100);

        //var_dump($listAgent);exit;
        $page_count = ceil($activeCount / $size);
        $base_url = "/adminuser/listapply/?body_code=".$body_code.'&province='.$province.'&city='.$city.'&account='.$account.'&begin='.$begin.'&end='.$end.'&code='.$code.'&agent_id='.$agent_id;
        return $this->renderSmartyTpl('listapply.tpl', ['agent_id' => $agent_id,'listAgent' => $listAgent,'body_code' => $body_code,'agentname' => $agentname,'province' => $province,'country' => $country,'city' => $city,'account' => $account,'begin' => $begin,'end' => $end,'code' => $code,'LIST' => $newData,'base_url' => $base_url,'page' => $page,'page_count' => $page_count,'size' => $size,'count' => $activeCount,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);
           
    }  

    //专门给售后使用的查询已经激活信息的权限
    public function actionFindapply()
    {       
       
        $body_code = Yii::$app->request->post("body_code");
        $hardware_id = Yii::$app->request->post("hardware_id");       
        if (empty($body_code)) {
            $body_code = Yii::$app->request->get("body_code");
        }
        if (empty($hardware_id)) {
            $hardware_id = Yii::$app->request->get("hardware_id");
        }       

        $start = 0;
        $size = 10;         
        $request = Yii::$app->getRequest();  
        $list = array();   
        $where = array();
        if (isset($body_code) && $body_code) {
            $where['body_code'] = trim($body_code); 
        }
        if (isset($hardware_id) && $hardware_id) {
            $where['hardware_id'] = trim($hardware_id); 
        }
        $count = array();
        if ($where) {
           $fields = 'count(*) AS activeCount';
           $count = Agroactiveinfo::getActiveWhere($where,$fields); 
        }        
        $activeCount = 0;  
        $newData = array();        
        if ($count && $count['0']['activeCount'] > 0 ) {
          $activeCount = $count['0']['activeCount'];      
          $fields = 'agro_active_info.id,agro_active_info.order_id,agro_active_info.apply_id,agro_active_info.body_code,agro_active_info.hardware_id,agro_active_info.activation,agro_apply_info.company_name,agro_apply_info.account,agro_apply_info.realname,agro_apply_info.phone';
          $fields .=',agro_active_info.agent_id,agro_active_info.upper_agent_id,agro_active_info.created_at,agro_apply_info.country,agro_apply_info.province,agro_apply_info.city,agro_apply_info.area,agro_apply_info.street,agro_apply_info.address';
          
          $data = Agroactiveinfo::getActiveWhere($where,$fields,$start,$size);              
          foreach ($data as $key => $value) {
              $value['agentname'] = Agroagent::getAgentname($value['agent_id']);
              $value['upperagentname'] = Agroagent::getAgentname($value['upper_agent_id']);

              $tmpPol= Agropolicies::getPolNo($value['apply_id'],$value['order_id']);
              $value['polnostr'] = $tmpPol['polnostr'];
              $value['pol_no'] = $tmpPol['pol_no'];
              $newData[] =$value;
              
          }
        }
        $page = 1;
        //var_dump($listAgent);exit;
        $page_count = ceil($activeCount / $size);
        $base_url = "/adminuser/findapply/?body_code=".$body_code.'&hardware_id='.$hardware_id;
        return $this->renderSmartyTpl('findapply.tpl', ['hardware_id' => $hardware_id,'body_code' => $body_code,'LIST' => $newData,'base_url' => $base_url,'page' => $page,'page_count' => $page_count,'size' => $size,'count' => $activeCount,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);
           
    }  

    //意见反馈列表
    public function actionListreport()
    {       
        $page = Yii::$app->request->get('page', 1);
        $size = Yii::$app->request->get('size', 30);      
        $start = ($page - 1) * $size;
        if ( $start < 0 ) $start = 0;   
        $request = Yii::$app->getRequest();  
        $list = $where = array(); 
        $list = Agroreport::getAndEqualWhere($where, $start, $size);
        $count = 0;
        if (empty($list)) {
           
        }else{
           $count = Agroreport::getAndEqualWhereCount($where);
          
        }
        //var_dump($list);exit;
        $page_count = ceil($count / $size);
        $base_url = "/adminuser/listreport/?a=1";
        return $this->renderSmartyTpl('listreport.tpl', ['LIST' => $list,'base_url' => $base_url,'page' => $page,'page_count' => $page_count,'size' => $size,'count' => $count,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);
        exit;     
      
    }  

    public function actionAddreport()
    {
       $LoginFormList = Yii::$app->request->post("LoginForm");
        $id =Yii::$app->request->post('id', '');
        $list = $remarklist = array();
        $request = Yii::$app->getRequest();  

        if (empty($LoginFormList['remark']) ) {
            $id =Yii::$app->request->get('id', '');
            if ($id > 0 ) {
                $start = 0;
                $limit = 1;
                $where = array('id'=>$id);
                $list = Agroreport::getAndEqualWhere($where, $start, $limit);
                $remarkwhere = array('report_id'=>$id);
                $limit = 10;
                $remarklist = Agroreportreply::getAndEqualWhere($remarkwhere, $start, $limit);                            
               
            }           
           return $this->renderSmartyTpl('addreport.tpl', ['REMARKLIST' => $remarklist,'LIST' => $list,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);          
        }else{
           $id =Yii::$app->request->post('id', '');
           $LoginFormList['operator'] = Yii::$app->user->identity->username; 
           $LoginFormList['ip'] = $this->get_client_ip();                    
           if ($id > 0 ) {
                $LoginFormList['id'] = $id;               
                $result = Agroreport::updateInfo($LoginFormList);
               
                $result = Agroreportreply::addRemark($LoginFormList);
                if ($result> 0) {
                     $this->redirect('/adminuser/listreport/');
                }

           }        

        }
        return $this->renderSmartyTpl('addreport.tpl', ['REMARKLIST' => $remarklist,'LIST' => $list,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);
        exit; 

    } 


    //通知列表
    public function actionListnotice()
    {       
        $page = Yii::$app->request->get('page', 1);
        $size = Yii::$app->request->get('size', 30);      
        $start = ($page - 1) * $size;
        if ( $start < 0 ) $start = 0;   
        $request = Yii::$app->getRequest();  
        $list = array();   
        $where = array('deleted'=> 0);
        $list = Agronotice::getAndEqualWhere($where, $start, $size);
        $count = 0;
        if (empty($list)) {
           
        }else{
           $count = Agronotice::getAndEqualWhereCount($where);
          
        }
        $page_count = ceil($count / $size);
        $base_url = "/adminuser/listnotice/?a=1";
        return $this->renderSmartyTpl('listnotice.tpl', ['LIST' => $list,'base_url' => $base_url,'page' => $page,'page_count' => $page_count,'size' => $size,'count' => $count,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);
        exit;     
      
    }    
    //增加通知
    public function actionAddnotice()
    {
        $get_str = json_encode($_REQUEST);       
        $get_str .= "SERVER=".json_encode($_SERVER);
        $this->add_log($get_str, 'adminuser_addagent');    

        $LoginFormList = Yii::$app->request->post("LoginForm");
        $id =Yii::$app->request->post('id', '');
        $list = array();
        $request = Yii::$app->getRequest();  

        if (empty($LoginFormList['title']) ) {
            $id =Yii::$app->request->get('id', '');
            if ($id > 0 ) {
                 $start = 0;
                $limit = 1;
                $where = array('id'=>$id);
                $list = Agronotice::getAndEqualWhere($where, $start, $limit);                          
               
            }           
           return $this->renderSmartyTpl('addnotice.tpl', ['LIST' => $list,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);          
        }else{
           $id =Yii::$app->request->post('id', '');
           $LoginFormList['operator'] = Yii::$app->user->identity->username; 
           $LoginFormList['ip'] = $this->get_client_ip(); 
                   
           if ($id > 0 ) {
                $LoginFormList['id'] = $id;               
                $result = Agronotice::updateInfo($LoginFormList);
                if ($result> 0) {
                       $this->redirect('/adminuser/listnotice/');
                }
           }else{
                 $result = Agronotice::add($LoginFormList);
                 if ($result> 0) {
                     $this->redirect('/adminuser/listnotice/');
                 }
           }          

        }
        return $this->renderSmartyTpl('addnotice.tpl', ['LIST' => $list,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);
        exit;     
      
    }

      //修改激活信息
    public function actionAddapply()
    {
        $get_str = json_encode($_REQUEST);       
        $get_str .= "SERVER=".json_encode($_SERVER);
        $this->add_log($get_str, 'adminuser_addapply');    

        $LoginFormList = Yii::$app->request->post("LoginForm");
        $id =Yii::$app->request->post('id', '');
        $list = array();
        $request = Yii::$app->getRequest();  

        if (empty($LoginFormList['realname']) ) {
            $id =Yii::$app->request->get('id', '');
            if ($id > 0 ) {
                $start = 0;
                $limit = 1;
                $where = array('id'=>$id);
                $list = Agroapplyinfo::getAndEqualWhere($where, $start, $limit); 
                return $this->renderSmartyTpl('addapply.tpl', ['LIST' => $list,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);                          
               
            }else{

              $this->redirect('/adminuser/listapply/');
              return true;
            }           
                   
        }else{
           $id =Yii::$app->request->post('id', '');
           $LoginFormList['operator'] = Yii::$app->user->identity->username; 
           $LoginFormList['ip'] = $this->get_client_ip(); 
                   
           if ($id > 0 ) {
                $userObj = new DjiUser();
                $userInfo = $userObj->direct_get_user($LoginFormList['account']);
               
                if ($userInfo && $userInfo['status'] == '0' && $userInfo['status_msg'] == 'ok' ) {
                   $LoginFormList['uid'] =  $userInfo['items']['0']['user_id'];
                }else{
                    $data = array('status' => 1015,'extra' => array('msg'=>'用户DJI帐号不存在,请先注册!','msgen' => 'DJI account does not exist, please register!'));
                    echo json_encode($data);exit; 
                }

                $LoginFormList['id'] = $id;               
                $result = Agroapplyinfo::updateInfo($LoginFormList);
                if ($result> 0) {
                       $this->redirect('/adminuser/listapply/');
                }
           }else{
               $this->redirect('/adminuser/listapply/');
           }
           return $this->renderSmartyTpl('addapply.tpl', ['LIST' => $list,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);          

        }
      
    }


    protected function renderSmartyTpl($tpl,$list)
    {
        $adminUser = new AdminUser();
        $rolePurvieData = $adminUser->getRolePurvie();
        $list['headerrolePurvieData'] = $rolePurvieData;
        $list['isGuest'] = Yii::$app->user->isGuest;
        if (!$list['isGuest']) {
           $list['sessionUserName'] =  Yii::$app->user->identity->username;
        }
        if (!isset($list['headtitle'])) {
           $list['headtitle'] =  '农业无人机内部系统';
        }   
          
        return $this->renderPartial($tpl,$list);
    }
     //优先读取post，然后get
    protected function getPostValue($key) {
       $value = Yii::$app->request->post($key);
       return $value ? $value : Yii::$app->request->get($key); 
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
        file_put_contents($logfile, date('Y/m/d H:i:s').":  $msg >>> $ip - $type  >> SERVER_ADDR=$SERVER_ADDR \r\n", FILE_APPEND);
    }

    protected function get_client_ip() {
        $ip = $_SERVER['REMOTE_ADDR'];
        return $ip;
    }  

    
    
}
