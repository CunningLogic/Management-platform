<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use app\models\Agroapplyinfo;
use app\models\Agroagentmis;
use app\models\Agroagentbody;
use app\models\Agrosninfo;
use app\models\Agroactiveinfo;
use yii\base\ErrorException;
use app\models\Agroagent;
use app\models\Agropolicies;


class AfterController extends Controller
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
           $key = "Aqft6b6nxhGGFka6UoBhJurvKMmc7kpgA68GucAEyWW83JufHdJ";
           return $key;
    }   

    /* 
     *  售后查询农业无人机激活接口 https://iuav.dji.com/after/search/地址 只支持post请求
     *  @parameter body_code 整机序列
     *  @parameter hardware_id 硬件id     
     *  @parameter datetime 时间戳
     *  @parameter signature  签名字符串 
     *
     *  return 
     *  {"status":200,"data":[{"id":"42","order_id":"c45e69a1877e468f8366dba17ac58992",
     * "apply_id":"36","body_code":"test26","hardware_id":"test26","company_name":"2343243",
     * "account":"weiping.huang@dji.com","realname":"dsfdsfd","phone":"12321321","agent_id":"3",
     * "upper_agent_id":"3","created_at":"2016-04-28 16:02:47","country":"kr","province":"","city":"","area":"",
     * "street":"","address":"","agentname":"21","upperagentname":"21","polnostr":"\u5904\u7406\u4e2d","pol_no":""}]}
     * 
     * "status":200 是表示返回正常; body_code(整机序列),hardware_id(硬件id),company_name(公司名称),
     * realname(真实用户),account(DJI账号),phone(手机号),created_at(激活时间),country(国家)，
     * province(省份),city(城市),area(地区),street(街道),address(详细地址),agentname(代理名称)
     * upperagentname(一级代理名称),polnostr(保险状态),pol_no(保险保单号)
     *  
     * 
     * $signature = strtoupper(hash_hmac("sha1", $body_code.$hardware_id.$datetime, $key));
    */
    public function actionSearch()
    {       
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'after/search');
        }
        $get_str = json_encode($_REQUEST);
        $get_str .= "SERVER=".json_encode($_SERVER);
        $this->add_log($get_str, 'afterSearch');
        $body_code = Yii::$app->request->post("body_code");
        $hardware_id = Yii::$app->request->post("hardware_id"); 
        $datetime = Yii::$app->request->post("datetime");        
        $signature = Yii::$app->request->post("signature");
        $key = self::getAgroKey();
        if (empty($body_code) && empty($hardware_id)) {
            $msg = "parameter is empty";
            echo json_encode(array('status' => 100,'extra' => array('msg'=>$msg)));
            exit;
        }
        $sign = strtoupper(hash_hmac("sha1", $body_code.$hardware_id.$datetime, $key));
        //echo $sign;exit;
        if ($sign != $signature) {
            $msg = "Signature does not";
            echo json_encode(array('status' => 101,'extra' => array('msg'=>$msg)));
            exit;
        } 
        $start = 0;
        $size = 10; 
        $list = array();   
        $where = array();
        if (isset($body_code) && $body_code) {
            $where['body_code'] = trim($body_code); 
        }
        if (isset($hardware_id) && $hardware_id) {
            $where['hardware_id'] = trim($hardware_id); 
        }
        $luckkeywhere = 'iuav_afterSearch'.md5(implode('', $where));
        $luckdatawhere = Yii::$app->cache->get($luckkeywhere);
        if ($luckdatawhere ) {
            die(json_encode($luckdatawhere)); 
        }

        $newData = array();        
        $fields = 'agro_active_info.id,agro_active_info.order_id,agro_active_info.apply_id,agro_active_info.body_code,agro_active_info.hardware_id,agro_apply_info.company_name,agro_apply_info.account,agro_apply_info.realname,agro_apply_info.phone';
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

        $result = array(
            'status' => 200,'data' => $newData,          
        );
        Yii::$app->cache->set($luckkeywhere, $result, 600);
        $this->add_log(json_encode($result),"afterSearch");
        die(json_encode($result));       
        exit;

    }

    /* 
     *  售后查询农业无人机无法激活接口 https://iuav.dji.com/after/searchsn/地址 只支持post请求
     *  @parameter body_code 整机序列
     *  @parameter hardware_id 硬件id     
     *  @parameter datetime 时间戳
     *  @parameter signature  签名字符串 
     *
     *  return 
     * {"status":200,"data":{"bodyinfo":[{"id":"27","body_code":"test26","hardware_id":"test26",
     *  "agentname":"","code":"0001","created_at":"2016-04-25 14:36:26"}],
     *  "sninfo":[{"id":"31","body_code":"test26","hardware_id":"test26","created_at":"2016-04-25 14:36:12"}]}}
     *
     * "status":200 是表示返回正常; bodyinfo(整机序列和代理商的关系),sninfo(是否有激活码信息)
     *  
     * 
     * $signature = strtoupper(hash_hmac("sha1", $body_code.$hardware_id.$datetime, $key));
    */
    public function actionSearchsn()
    {       
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'after/search');
        }
        $get_str = json_encode($_REQUEST);
        $get_str .= "SERVER=".json_encode($_SERVER);
        $this->add_log($get_str, 'afterSearchsn');
        $body_code = Yii::$app->request->post("body_code");
        $hardware_id = Yii::$app->request->post("hardware_id"); 
        $datetime = Yii::$app->request->post("datetime");        
        $signature = Yii::$app->request->post("signature");
        $key = self::getAgroKey();
        if (empty($body_code) && empty($hardware_id)) {
            $msg = "parameter is empty";
            echo json_encode(array('status' => 100,'extra' => array('msg'=>$msg)));
            exit;
        }
        $sign = strtoupper(hash_hmac("sha1", $body_code.$hardware_id.$datetime, $key));
        //echo $sign;exit;
        if ($sign != $signature) {
            $msg = "Signature does not";
            echo json_encode(array('status' => 101,'extra' => array('msg'=>$msg)));
            exit;
        } 
        $start = 0;
        $size = 10; 
        $list = array();   
        $where = array();
        if (isset($body_code) && $body_code) {
            $where['body_code'] = trim($body_code); 
        }
        if (isset($hardware_id) && $hardware_id) {
            $where['hardware_id'] = trim($hardware_id); 
        }       
        $where['deleted'] = 0; 
        
        $luckkeywhere = 'iuav_afterSearchsn'.md5(implode('', $where));
        $luckdatawhere = Yii::$app->cache->get($luckkeywhere);
        if ($luckdatawhere ) {
            die(json_encode($luckdatawhere)); 
        }

        $bodyinfo = Agroagentbody::getAndEqualWhere($where,$start, $size,'id',1,'id,body_code,hardware_id,agentname,code,created_at');       
        $sninfo = Agrosninfo::getAndEqualWhere($where,0, 1,'id',1,'id,body_code,hardware_id,created_at'); 
        $result = array(
            'status' => 200,'data' => array('bodyinfo' => $bodyinfo,'sninfo' => $sninfo),          
        );
        Yii::$app->cache->set($luckkeywhere, $result, 600);
        $this->add_log(json_encode($result),"afterSearchsn");
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
        return $ip;
    }



   
}
