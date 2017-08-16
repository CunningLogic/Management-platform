<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\Agroapplyinfo;

use GeoIp2\Database\Reader;
use PHPMailer;
use app\models\Agroagentmis;
use app\models\Agroagentbody;
use app\models\Agrosninfo;
use app\models\UserExchange;
use yii\base\ErrorException;
use app\components\DjiUser;

class MispiController extends Controller
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
           $key = "HYmCTKMNfHUV9UZVbqy4q6QRYgA7R3a3anGyERUAzJJs";
           return $key;
    }

    //网关测试接口
    public function actionCheck()
    {
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'mispi/check');
        }
        return array('ok');
    }

    /* 
     *  代理商信息上传接口 https://iuav.dji.com/mispi/agroagent/地址 只支持post请求
     *  @parameter datetime 时间戳
     *  @parameter info json格式( [{"uid":"121","agentname":"aabc","staff":"负责人",code":"12121","realname":"代理商负责人","idcard":"32432313","phone":"1213223","email":"wer@qq.com","country":"cn","province":"343","city":"dsfd","address":"334"},
     * {"uid":"122","agentname":"aabc","staff":"负责人","code":"121212121","realname":"代理商负责人","idcard":"32432313","phone":"1213223","email":"we1111r@qq.com","country":"cn","province":"343","city":"dsfd","address":"334"}] )
     *  @parameter signature  签名字符串 
     *
     *  return 
     *     
     * $signature = strtoupper(hash_hmac("sha1", $datetime, $key));
    */
    public function actionAgroagent()
    {
        set_time_limit(0);
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'mispi/agroagent');
        }
        $get_str = json_encode($_REQUEST);

        $get_str .= "SERVER=".json_encode($_SERVER);
        $this->add_log($get_str, 'agroagent');

        $datetime = Yii::$app->request->post("datetime");
        $info = Yii::$app->request->post("info");
        $signature = Yii::$app->request->post("signature");
        $key = self::getAgroKey();
        if (empty($info)) {
            $msg = "info is empty";
            echo json_encode(array('status' => 100,'extra' => array('msg'=>$msg)));
            exit;
        }
        $sign = strtoupper(hash_hmac("sha1", $datetime, $key));
        //echo $sign;exit;
        if ($sign != $signature) {
            $msg = "Signature does not";
            echo json_encode(array('status' => 101,'extra' => array('msg'=>$msg)));
            exit;
        } 
        $created = time();
        $sn_count = 0;
        //$info = str_replace("\\", '', $info);
        $info = json_decode($info, TRUE);
        $updated_at = date("Y-m-d H:i:s",time());
        $source = 0;
        $ip = $this->get_client_ip();
        $this->add_log('agroagent_count='.count($info),"agroagent");  
        foreach ($info as $key => $value) 
        {
            if (empty($value['code']) ) {
                 continue;
            } 

            //$this->add_log(json_encode($value)."&key=".$key."&sn_count".$sn_count,"agroagent");
           
            $value['ip'] = $ip;
            $value['misuid'] = trim($value['uid']);
            $value['code'] = trim($value['code']);
            $where = array();
            $where['misuid'] = $value['misuid'];             
            $where['deleted'] = 0;

            $luckwhere = 'iuav_MispiController_actionAgroagent_'.md5(implode('', $where));
            $luckdatawhere1 = Yii::$app->cache->get($luckwhere);
            if ($luckdatawhere1 ) {
                $findHave = $luckdatawhere1;
            }else{
                $findHave = Agroagentmis::getAndEqualWhere($where,0,1);
                Yii::$app->cache->set($luckwhere, $findHave, 86400+$key);
            }
            if (empty($findHave)) {
                $sn_count++;
                if ($sn_count % 20 == 0) {
                     sleep(1);
                }    
                $adddata = Agroagentmis::add($value);
                $this->add_log('add&'.json_encode($value),"agroagent");   
            }else{
                if ($findHave['0']['code'] != $value['code'] || $findHave['0']['agentname'] != $value['agentname']  || $findHave['0']['phone'] != $value['phone'] || $findHave['0']['email'] != $value['email']  ) {
                    $value['id'] = $findHave['0']['id'];
                    $adddata = Agroagentmis::updateInfo($value);
                    $this->add_log('updateInfo&'."&key=".$key,"agroagent");  
                }
                
            }            
        }       
        $result = array(
            'status' => 200,'sn_count' => $sn_count,          
        );
        $this->add_log(json_encode($result),"agroagent");
        die(json_encode($result));       
        exit;

    }

     /* 
     *  代理商和设备id上传接口 https://iuav.dji.com/mispi/agroagentbody/地址 只支持post请求
     *  @parameter datetime 时间戳
     *  @parameter info json格式( [{"id":1,body_code":"12121","hardware_id":"23212","agentname":"sdfdsfd","code":"1213223","email":"wer@qq.com"},
     *              {"id":2"id":1,,"body_code":"12121","hardware_id":"23212","agentname":"sdfdsfd","code":"1213223","email":"wer@qq.com"}] )
     *  @parameter signature  签名字符串 
     *
     *  return 
     *     
     * $signature = strtoupper(hash_hmac("sha1", $datetime, $key));
    */

    public function actionAgroagentbody()
    {
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'mispi/agroagentbody');
        }
        $luckkeyid = 'iuav_MispiController_actionAgroagentbody';
        $luckdata = Yii::$app->cache->get($luckkeyid);
        if ($luckdata ) {
             $result = array('status' => 110);
             die(json_encode($result));   
        }
        Yii::$app->cache->set($luckkeyid, 1, 600);

        $get_str = json_encode($_REQUEST);

        $get_str .= "SERVER=".json_encode($_SERVER);
        $this->add_log($get_str, 'agroagentbody');
        $datetime = Yii::$app->request->post("datetime");
        $info = Yii::$app->request->post("info");
        $signature = Yii::$app->request->post("signature");
        $key = self::getAgroKey();
        if (empty($info)) {
            $msg = "";
            echo json_encode(array('status' => 100,'extra' => array('msg'=>$msg)));
            exit;
        }
        $sign = strtoupper(hash_hmac("sha1", $datetime, $key));
        //echo $sign;
        if ($sign != $signature) {
            $msg = "Signature does not";
            echo json_encode(array('status' => 101,'extra' => array('msg'=>$msg)));
            exit;
        } 
        $created = time();
        $sn_count = 0;
        //$info = str_replace("\\", '', $info);
        $info = json_decode($info, TRUE);
        $updated_at = date("Y-m-d H:i:s",time());
        $source = 0;
        $ip = $this->get_client_ip();
        $resultData = array();
        foreach ($info as $key => $value) 
        {
            if (empty($value['body_code'])) {
                 continue;
            } 
            if (!isset($value['id'])) {
                $value['id'] = $key;
            }
            $value['ip'] = $ip;
            $value['body_code'] = trim($value['body_code']);
            $where = array();
            $where['body_code'] = $value['body_code'];
            $where['deleted'] = 0;              
           // $findHave = Agroagentbody::getAndEqualWhere($where,0,1);

            $luckwhere = 'iuav_MispiController_actionAgroagentbody1_'.md5(implode('', $where));
            $luckdatawhere1 = Yii::$app->cache->get($luckwhere);
            if ($luckdatawhere1 ) {
                $findHave = $luckdatawhere1;
            }else{
                $findHave = Agroagentbody::getAndEqualWhere($where,0,1);
                Yii::$app->cache->set($luckwhere, $findHave, 3600+$key);
            } 

            if (empty($findHave)) {
                $sn_count++;
                if ($sn_count % 20 == 0) {
                     sleep(1);
                }    
                $adddata = Agroagentbody::add($value);
                $this->add_log(json_encode($value),"agroagentbody");   
            }else{
                $where = array();
                $modetmp = $value;
                $modetmp['id'] = $findHave['0']['id'];
                $where['body_code'] = $value['body_code'];
                $where['hardware_id'] = $value['hardware_id'];
                $where['code'] = $value['code'];
                $where['deleted'] = 0;   
                $luckkeywhere = 'iuav_MispiController_actionAgroagentbody_'.md5(implode('', $where));
                $luckdatawhere = Yii::$app->cache->get($luckkeywhere);
                if ($luckdatawhere ) {
                    $findHave = $luckdatawhere;
                }else{
                    $findHave = Agroagentbody::getAndEqualWhere($where,0,1);
                    Yii::$app->cache->set($luckkeywhere, $findHave, 3600+$key);
                } 

                
                if (empty($findHave)) {
                    Agroagentbody::updateInfo($modetmp);

                }

            } 
            $resultData[] = array('id' => $value['id']);           
        }       
        $result = array(
            'status' => 200,'sn_count' => $sn_count,'data' => $resultData,          
        );
        die(json_encode($result));       
        exit;

    }  
     /* 
     *  农业无人机激活码上传接口 https://iuav.dji.com/mispi/sninfo/地址 只支持post请求
     *  @parameter datetime 时间戳
     *  @parameter info json格式( [{"id":1,"body_code":"121121","hardware_id":"23212","activation":"sdfdsfd"},
     *              {"id":2,"body_code":"1212211","hardware_id":"23ad212","activation":"sdvsfdsfdsdsfdsfd"}] )
     *  @parameter signature  签名字符串 
     *
     *  return {"status":200,"sn_count":0,"data":[{"id":"1"},{"id":"2"}]}  
     *  data 里面返回添加成功的机身码
     *  `body_code`  '机身码',
     *  `hardware_id` '硬件id',
     *   `activation`  '激活码',
     *     
     * $signature = strtoupper(hash_hmac("sha1", $datetime, $key));
    */
    public function actionSninfo()
    {
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'mispi/sninfo');
        }
        $luckkeyid = 'iuav_MispiController_actionSninfo';
        $luckdata = Yii::$app->cache->get($luckkeyid);
        if ($luckdata ) {
             $result = array('status' => 110);
             die(json_encode($result));   
        }
        Yii::$app->cache->set($luckkeyid, 1, 600);

        $get_str = json_encode($_REQUEST);

        $get_str .= "SERVER=".json_encode($_SERVER);
        $this->add_log($get_str, 'agrosninfo');
        $datetime = Yii::$app->request->post("datetime");
        $info = Yii::$app->request->post("info");
        $signature = Yii::$app->request->post("signature");
        $key = self::getAgroKey();
        if (empty($info)) {
            $msg = "";
            echo json_encode(array('status' => 100,'extra' => array('msg'=>$msg)));
            exit;
        }
        $sign = strtoupper(hash_hmac("sha1", $datetime, $key));
        //echo $sign;
        if ($sign != $signature) {
            $msg = "Signature does not";
            echo json_encode(array('status' => 101,'extra' => array('msg'=>$msg)));
            exit;
        } 
        $created = time();
        $sn_count = 0;
        //$info = str_replace("\\", '', $info);
        $info = json_decode($info, TRUE);
        $updated_at = date("Y-m-d H:i:s",time());
        $source = 0;
        $ip = $this->get_client_ip();
        $resultData = array();
        foreach ($info as $key => $value) 
        {
            if (empty($value['body_code'])) {
                 continue;
            } 
            if (!isset($value['id'])) {
                $value['id'] = $key;
            }
            $value['ip'] = $ip;
            $value['scan_date'] = date("Y-m-d",time());
            $value['type'] = 'mg-1';
            $value['operator'] = 'api';
            $value['body_code'] = trim($value['body_code']);
            $where = array();
            $where['body_code'] = $value['body_code'];
            $where['deleted'] = 0;              
            $luckwhere = 'iuav_MispiController_actionSninfo1_'.md5(implode('', $where));
            $luckdatawhere1 = Yii::$app->cache->get($luckwhere);
            if ($luckdatawhere1 ) {
                $findHave = $luckdatawhere1;
            }else{
                $findHave = Agrosninfo::getAndEqualWhere($where,0,1);
                Yii::$app->cache->set($luckwhere, $findHave, 3600+$key);
            } 
            if (empty($findHave)) {
                $sn_count++;
                if ($sn_count % 20 == 0) {
                     sleep(1);
                }    
                $adddata = Agrosninfo::add($value);
                if ($adddata > 0) {
                     $resultData[] = array('id' => $value['id']);
                }
                $this->add_log(json_encode($value),"agrosninfo");   
            }else{
                $where = array();
                $modetmp = $value;
                $modetmp['id'] = $findHave['0']['id'];
                $where['body_code'] = $value['body_code'];
                $where['hardware_id'] = $value['hardware_id'];
                $where['activation'] = $value['activation'];
                $where['deleted'] = 0; 
                $luckkeywhere = 'iuav_MispiController_actionSninfo_'.md5(implode('', $where));
                $luckdatawhere = Yii::$app->cache->get($luckkeywhere);
                if ($luckdatawhere ) {
                     $findHave = $luckdatawhere;
                }else{
                     $findHave = Agrosninfo::getAndEqualWhere($where,0,1);
                     Yii::$app->cache->set($luckkeywhere, $findHave, 3600+$key);
                }                
                if (empty($findHave)) {
                    Agrosninfo::updateInfo($modetmp);
                }
                
                $resultData[] = array('id' => $value['id']);
            }            
        }       
        $result = array(
            'status' => 200,'sn_count' => $sn_count,'data' => $resultData ,          
        );
        die(json_encode($result));       
        exit;

    }

    /* 
     *  农业无人机已经激活列表接口 https://iuav.dji.com/mispi/getagrouserid/地址 只支持post请求
     *  @parameter datetime 时间戳
     *  @parameter signature  签名字符串 
     *
     *  return {"status":200,"data":[{"id":"1"},{"id":"2"}]}  
     *
     * $signature = strtoupper(hash_hmac("sha1", $datetime, $key));
    */
    public function actionGetagrouserid()
    {
        echo "test";exit;
        $datetime = Yii::$app->request->post("datetime");       
        $signature = Yii::$app->request->post("signature");
        $key = self::getAgroKey();
        if (empty($datetime) || empty($signature) ) {
            //$msg = "";
            //echo json_encode(array('status' => 100,'extra' => array('msg'=>$msg)));
            //exit;
        }
        $sign = strtoupper(hash_hmac("sha1", $datetime, $key));
        //echo $sign;
        if ($sign != $signature) {
            //$msg = "Signature does not";
            //echo json_encode(array('status' => 101,'extra' => array('msg'=>$msg)));
            //exit;
        } 
        $userObj = new DjiUser();
        $comfile = __DIR__.'/../yii';
        $userlist = Agroapplyinfo::getAllUserList();
        $i = 0;
        foreach ($userlist as $key => $value) { 
          
           $userInfo = $userObj->direct_get_user($value['account']);
           if ($userInfo && $userInfo['status'] == '0' && $userInfo['status_msg'] == 'ok' ) {
               $uid =  $userInfo['items']['0']['user_id'];          
               // 通知商城用户已经购买农业无人机
               @system("php $comfile store/index \"$uid\" > /dev/null & ");               
               $i++;
               if ($i % 20 == 0) {
                    sleep(1);
               }
                
           }
        }
        echo "ok";
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
