<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use GeoIp2\Database\Reader;
use PHPMailer;
use yii\base\ErrorException;
use app\components\DjiUser;
use app\models\Agroactiveinfo;
use app\models\Agropolicies;

class IuavagentController extends Controller
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
    protected function getAgroKey()
    {
        $key = "hwE37PPhVkJhwE37PPhVkJ2kx9wvapb2kx9wvapb";
        if (isset(Yii::$app->params['GWServer']) ) {
            $key = Yii::$app->params['GWServer']['IUAVAGENTKEY'];                       
        }
        return $key;
    }   
    /*
     *  农业无人机激活码上传接口 https://iuav-mg.dji.com/iuavagent/activeinfo/地址 只支持post请求
     *  @parameter datetime 时间戳
     *  @parameter order_id 当前激活id,用于传给保险公司
     *  @parameter apply_id 农业无人机申请表
     *  @parameter uid 用户中心id
     *  @parameter body_code 整机序列号
     *  @parameter hardware_id 硬件id
     *  @parameter type 型号:mg-1;mg-2;mg-3
     *  @parameter signature  签名字符串 
     *
     *  return {"status":0}  
     *     
     * $signature = strtoupper(hash_hmac("sha1", $datetime.$order_id.$apply_id.$uid.$body_code.$hardware_id.$type, $key));
    */
    public function actionActiveinfo()
    {
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'iuavagent/activeinfo');
        }
       
        $get_str = json_encode($_REQUEST);
        $get_str .= "SERVER=".json_encode($_SERVER);
        $this->add_log($get_str, 'iuavagent_activeinfo');
        $datetime = Yii::$app->request->post("datetime");
        $order_id = Yii::$app->request->post("order_id");
        $apply_id = Yii::$app->request->post("apply_id");
        $uid = Yii::$app->request->post("uid");
        $body_code = Yii::$app->request->post("body_code");
        $hardware_id = Yii::$app->request->post("hardware_id");
        $type = Yii::$app->request->post("type");
        $signature = Yii::$app->request->post("signature");
        $key = self::getAgroKey();
        if (empty($hardware_id) || empty($body_code) ) {
            $msg = "";
            echo json_encode(array('status' => 100,'extra' => array('msg'=>$msg)));
            exit;
        }
        $luckkeyid = 'iuavagent_iuavagentController_actionActiveinfo'.$body_code.$hardware_id;
        $luckdata = Yii::$app->cache->get($luckkeyid);
        if ($luckdata ) {
             $result = array('status' => 110);
             die(json_encode($result));   
        }
        Yii::$app->cache->set($luckkeyid, 1, 10);

        $sign = strtoupper(hash_hmac("sha1", $datetime.$order_id.$apply_id.$uid.$body_code.$hardware_id.$type, $key));
        //echo $sign;
        if ($sign != $signature) {
            $msg = "Signature does not";
            echo json_encode(array('status' => 101,'extra' => array('msg'=>$msg)));
            exit;
        } 
        $created = time();      
        $updated_at = date("Y-m-d H:i:s",$created);
        $source = 0;
        $ip = $this->get_client_ip();
        $resultData = array();
        $where = array();
        $where['hardware_id'] = $hardware_id;
        $where['body_code'] = $body_code;
        $where['uid'] = $uid;
        $where['order_id'] = $order_id;
        $where['apply_id'] = $apply_id;
        $where['type'] = $type;
        $activeInfo = Agroactiveinfo::getAndEqualWhere($where,0,1,'id',1,'id,deleted');
        if ($activeInfo && is_array($activeInfo) && $activeInfo['0']['id'] > 0) {
            if ($activeInfo['0']['deleted'] == 1) {
                $model = array('deleted' => 0,'id' => $activeInfo['0']['id'] );
                Agroactiveinfo::updateDeleted($model);
            }
        }else{          
            Agroactiveinfo::simpleAdd($where);
        }

        $result = array(
            'status' => 0,'data' => $resultData ,          
        );
        die(json_encode($result));       
        exit;

    }

    /*
     *  农业无人机激活码上传接口 https://iuav-mg.dji.com/iuavagent/policies/地址 只支持post请求
     *  @parameter datetime 时间戳
     *  @parameter order_id 当前激活id,用于传给保险公司
     *  @parameter apply_id 农业无人机申请表
     *  @parameter pol_no  保单号
     *  @parameter exp_tm 保险结束时间,格式为：YYYYMMDDHHMMSS
     *  @parameter query_flag 1-获取保单成功,0-失败
     *  @parameter signature  签名字符串 
     *
     *  return {"status":0}  
     *     
     * $signature = strtoupper(hash_hmac("sha1",$datetime.$order_id.$apply_id.$pol_no.$exp_tm.$query_flag, $key));
    */
    public function actionPolicies()
    {
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'iuavagent/policies');
        }
       
        $get_str = json_encode($_REQUEST);
        $get_str .= "SERVER=".json_encode($_SERVER);
        $this->add_log($get_str, 'iuavagent_policies');
        $datetime = Yii::$app->request->post("datetime");
        $order_id = Yii::$app->request->post("order_id");
        $apply_id = Yii::$app->request->post("apply_id");
        $pol_no = Yii::$app->request->post("pol_no");
        $exp_tm = Yii::$app->request->post("exp_tm");
        $query_flag = Yii::$app->request->post("query_flag");
        $signature = Yii::$app->request->post("signature");
        $key = self::getAgroKey();
        if (empty($pol_no) || empty($apply_id) || empty($order_id)) {
            $msg = "";
            echo json_encode(array('status' => 100,'extra' => array('msg'=>$msg)));
            exit;
        }
        $sign = strtoupper(hash_hmac("sha1", $datetime.$order_id.$apply_id.$pol_no.$exp_tm.$query_flag, $key));
        //echo $sign;
        if ($sign != $signature) {
            $msg = "Signature does not";
            echo json_encode(array('status' => 101,'extra' => array('msg'=>$msg)));
            exit;
        } 
        $luckkeyid = 'iuavagent_iuavagentController_actionPolicies'.$order_id.$apply_id;
        $luckdata = Yii::$app->cache->get($luckkeyid);
        if ($luckdata ) {
             $result = array('status' => 110);
             die(json_encode($result));   
        }
        Yii::$app->cache->set($luckkeyid, 1, 10);

        $created = time();      
        $updated_at = date("Y-m-d H:i:s",$created);
        $source = 0;
        $ip = $this->get_client_ip();
        $resultData = array();
        $where = array();
        $where['pol_no'] = $pol_no;
        $where['exp_tm'] = $exp_tm;
        $where['query_flag'] = $query_flag;
        $where['order_id'] = $order_id;
        $where['apply_id'] = $apply_id;
        $policiesInfo = Agropolicies::getAndEqualWhere($where,0,1,'id',1,'id,deleted');
        if (empty($policiesInfo) ) {      
           
            Agropolicies::simpleAdd($where);
        }

        $result = array(
            'status' => 0,'data' => $resultData ,          
        );
        die(json_encode($result));       
        exit;

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
