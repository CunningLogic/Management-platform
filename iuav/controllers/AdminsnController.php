<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;

use app\models\Agrosninfo;
use app\models\Agroagentbody;
use app\models\OperationLog;
use app\models\Email;
use app\components\AdminUser;


class AdminsnController extends Controller
{
    //public $enableCsrfValidation = false;
    public function beforeAction($action)
    {
         parent::beforeAction($action);
         //$userId = Yii::$app->user->getId();
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
                        'actions' => ['index1111dsfds'], 
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['index','add','edit','del','view','list','listbody','addbody'], 
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

    

    public function actionList()
    {
       
        $request = Yii::$app->getRequest();  
        $page = Yii::$app->request->get('page', 1);
        $size = Yii::$app->request->get('size', 30);
        $start = ($page - 1) * $size;
        if ( $start < 0 ) $start = 0;
        $body_code = Yii::$app->request->post("body_code");
        $hardware_id = Yii::$app->request->post("hardware_id");
        if (empty($body_code)) {
            $body_code = Yii::$app->request->get("body_code");
        }
        if (empty($hardware_id)) {
            $hardware_id = Yii::$app->request->get("hardware_id");
        }

        $list = $where =array(); 
        if (isset($body_code) && $body_code) {
            $where['body_code'] = str_replace(' ','',$body_code);
        }
        if (isset($hardware_id) && $hardware_id) {
            $where['hardware_id'] = $hardware_id; 
        }
        $where['deleted'] = 0; 
        $list = Agrosninfo::getAndEqualWhere($where,$start, $size,'id',1);  
        $count = 0;
        if ($list){           
           foreach ($list as $key => $value) {
               $tmpwhere = array();
               $tmpwhere['body_code'] = $value['body_code'];
               $tmpwhere['hardware_id'] = $value['hardware_id'];
               $tmpbody = Agroagentbody::getAndEqualWhere($tmpwhere,0, 1); 
               if ($tmpbody) {
                   $list[$key]['body'] = $tmpbody;
               }else{
                   $list[$key]['body'] = array();
               }

           }
           $count = Agrosninfo::getAndEqualWhereCount($where);   
          // var_dump($count)    ;   exit;
        }else if ($page > 1) {
           $this->redirect("/adminsn/list"); 
        }
        $page_count = ceil($count / $size);
        $base_url = "/adminsn/list/?body_code=".$body_code.'&hardware_id='.$hardware_id;

        return $this->renderSmartyTpl('list.tpl', ['body_code' => $body_code,'hardware_id' => $hardware_id,'base_url' => $base_url,'page' => $page,'page_count' => $page_count,'size' => $size,'count' => $count, 'LIST' => $list,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);
        exit;     
      
    }    
    public function actionAdd()
    {
        //echo Yii::$app->user->identity->username;exit;
        $idpost =Yii::$app->request->post('id', '');
        $LoginFormList = Yii::$app->request->post("LoginForm");
        $list = array();
        $request = Yii::$app->getRequest(); 
        if (empty($idpost) && empty($LoginFormList)) {
            $id =Yii::$app->request->get('id', '');
            if ($id > 0 ) {
                $list = Agrosninfo::getAndEqualWhere(array('id' => $id), 0, 1,'id','desc');               
            }           
            return $this->renderSmartyTpl('add.tpl', ['LIST' => $list,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);          
        }else{
           $id = $idpost;
           $LoginFormList['ip'] = $this->get_client_ip();
           $LoginFormList['operator'] = Yii::$app->user->identity->username;
           if ($id > 0 ) {
                $LoginFormList['id'] = $id;               
                $result = Agrosninfo::updateInfo($LoginFormList);              
                if ($result> 0) {
                       $this->redirect('/adminsn/list/');
                }
           }else{     
               $result = Agrosninfo::add($LoginFormList);
               if ($result> 0) {
                   $this->redirect('/adminsn/list/');
               }
               
           }          

        }
        return $this->renderSmartyTpl('add.tpl', ['LIST' => $list,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);
        exit;     
      
    }
    //增加代理和sn的关系
    public function actionAddbody()
    {
        //echo Yii::$app->user->identity->username;exit;
        $idpost =Yii::$app->request->post('id', '');
        $LoginFormList = Yii::$app->request->post("LoginForm");
        $list = array();
        $request = Yii::$app->getRequest(); 
        if (empty($idpost) && empty($LoginFormList)) {
            $id =Yii::$app->request->get('id', '');
            if ($id > 0 ) {
                $list = Agroagentbody::getAndEqualWhere(array('id' => $id), 0, 1,'id','desc');               
            }           
            return $this->renderSmartyTpl('addbody.tpl', ['LIST' => $list,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);          
        }else{
           $id = $idpost;
           $LoginFormList['ip'] = $this->get_client_ip();
           $LoginFormList['operator'] = Yii::$app->user->identity->username;
           if ($id > 0 ) {
                $LoginFormList['id'] = $id;               
                $result = Agroagentbody::updateInfo($LoginFormList);              
                if ($result> 0) {
                       $this->redirect('/adminsn/listbody/');
                }
           }else{     
               $result = Agroagentbody::add($LoginFormList);
               if ($result> 0) {
                   $this->redirect('/adminsn/listbody/');
               }
               
           }          

        }
        return $this->renderSmartyTpl('addbody.tpl', ['LIST' => $list,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);
        exit;     
      
    }
    public function actionListbody()
    {
        $LoginFormList = Yii::$app->request->post("LoginForm");
        $request = Yii::$app->getRequest();  
        $page = Yii::$app->request->get('page', 1);
        $size = Yii::$app->request->get('size', 30);
        $start = ($page - 1) * $size;
        if ( $start < 0 ) $start = 0;
        $body_code = Yii::$app->request->post("body_code");
        $hardware_id = Yii::$app->request->post("hardware_id");
        $agentname = Yii::$app->request->post("agentname");
        $code = Yii::$app->request->post("code");
        if (empty($body_code)) {
            $body_code = Yii::$app->request->get("body_code");
        }
        if (empty($hardware_id)) {
            $hardware_id = Yii::$app->request->get("hardware_id");
        }
        if (empty($agentname)) {
            $agentname = Yii::$app->request->get("agentname");
        }
        if (empty($code)) {
            $code = Yii::$app->request->get("code");
        }

        $list = $where =array(); 
        if (isset($body_code) && $body_code) {
            $where['body_code'] = str_replace(' ','',$body_code); 
        }
        if (isset($hardware_id) && $hardware_id) {
            $where['hardware_id'] = str_replace(' ','',$hardware_id); 
        }
        if (isset($agentname) && $agentname) {
            $where['agentname'] = $agentname; 
        }
        if (isset($code) && $code) {
            $where['code'] = $code; 
        }

        $where['deleted'] = 0; 
        $list = Agroagentbody::getAndEqualWhere($where,$start, $size,'id',1);  
        $count = 0;
        if ($list){
            foreach ($list as $key => $value) {
               $tmpwhere = array();
               $tmpwhere['body_code'] = $value['body_code'];
               $tmpwhere['hardware_id'] = $value['hardware_id'];
               $tmpbody = Agrosninfo::getAndEqualWhere($tmpwhere,0, 1); 
               if ($tmpbody) {
                   $list[$key]['sninfo'] = $tmpbody;
               }else{
                   $list[$key]['sninfo'] = array();
               }
               $tmpbody = array();

           }
           $count = Agroagentbody::getAndEqualWhereCount($where);   
          // var_dump($count)    ;   exit;
        }else if ($page > 1) {
           $this->redirect("/adminsn/listbody"); 
        }
        $page_count = ceil($count / $size);
        $base_url = "/adminsn/listbody/?body_code=".$body_code.'&hardware_id='.$hardware_id.'&agentname='.$agentname.'&code='.$code;

        return $this->renderSmartyTpl('listbody.tpl', ['body_code' => $body_code,'hardware_id' => $hardware_id,'agentname' => $agentname,'code' => $code,'base_url' => $base_url,'page' => $page,'page_count' => $page_count,'size' => $size,'count' => $count, 'LIST' => $list,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);
        exit;     
      
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
        return $this->renderPartial($tpl,$list);
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
    
    
}
