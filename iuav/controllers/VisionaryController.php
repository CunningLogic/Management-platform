<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;

use app\models\Area;
use app\models\DraftArea;
use app\models\OperationLog;
use app\models\Email;
use app\models\VisionariesImage;
use app\models\VisionariesUser;

class VisionaryController extends Controller
{
    public $enableCsrfValidation = false;
        
    public function behaviors()
    {
        return [
            'access' => [
                'class'  => AccessControl::className(),
                'rules'  => [
                    [
                        'actions' => ['index','infolist'], 
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['index','infolist'], 
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

  public function actionIndex()
  {
     $page = Yii::$app->request->get('page', 1);
     $size = Yii::$app->request->get('size', 20);
     $start = ($page - 1) * $size;
     if ( $start < 0 ) $start = 0;      
     $userList = VisionariesUser::getAndEqualWhere(array('status' => array('published') ), $start, $size,'id');
     $this->jsonResponse($userList);    

  }
  //http://dev.e.dbeta.me/visionary/infolist/?id=1
  public function actionInfolist()
  {      
     $id =Yii::$app->request->get('id', ''); 
     $page = Yii::$app->request->get('page', 1);
     $size = Yii::$app->request->get('size', 20);
     $start = ($page - 1) * $size;
     if ( $start < 0 ) $start = 0;        
     $userList = array();
     if ($id > 0) {
          $where = array('visi_user_id' => $id,'status' => array('published'));
          $userList = VisionariesImage::getAndEqualWhere($where, $start, $size,'id');        
     } 
     $this->jsonResponse($userList);       

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
