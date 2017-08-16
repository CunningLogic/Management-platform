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
use app\models\Apply;
use app\models\Prize;
use app\models\UserExchange;
use yii\base\ErrorException;

class BlackfridayController extends Controller
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

    public function actionIndex()
    {
       $dataType = Yii::$app->request->get('dataType', '');

       return array("2343");
    }
    //抽奖春节抽奖活动的结束时间
    protected function getEventEndDate()
    {
        $firstTime = '2016-01-29 10:00:00';
        return $firstTime;
    }


    //抽奖活动的结束时间
    protected function getEndDate()
    {
        $firstTime = '2015-11-26 06:00:00';
        return $firstTime;
    }
    //抽奖活动的结束时间
    protected function getProdEndDate()
    {
        $firstTime = '2015-11-26 06:00:00';
        return $firstTime;
    }

    /**
    *  黑五活动产品列表爷们
    *  @parameter country 国家缩写 CN,US 可以为空时，默认读取ip地址对应的国家
    *  return {"country":"US","currency":"US","firstDown":872516,"seconDown":1218116,"nowTime":1447738684,"pirce":{"p3p":{"US":["$1,259","$1,159"],"GBP":["£1,159","£1,079"],"AUD":["$2,199","$2,049"],"EUR":["€1,399","€1,299"]},"p3s":{"US":["$799","$599"],"GBP":["£649","£539"],"AUD":["$1,299","$1,049"],"EUR":["€919","€719"]}},"csrftoken":"C8FCE3AE347A826FD429F0AE39D8392E4DE542931A35FB11E026645C9C3DF34F"}
    *
    */
    public function actionProduct()
    {
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'friday/product');
        }
        $get_str = json_encode($_REQUEST);

        $get_str .= "SERVER=".json_encode($_SERVER);
        $this->add_log($get_str, 'friday_product');
        $ip = $this->get_client_ip();
        $country = Yii::$app->request->get('country', '');

        $luckkey = 'friday_Produc_111'.md5($ip.$country);
        $luckdata = Yii::$app->cache->get($luckkey);
        if ( $luckdata ) {
           return $luckdata;
        }
        if (empty($country)) {
            if ($ip == '127.0.0.2' || $ip == '10.60.215.53') {
               $country = 'US';
            }else{
               try {
                   $reader = new Reader(__DIR__.'/../config/GeoIP2-Country.mmdb');
                   $record = $reader->country($ip);
                   if ($record ==false) {
                       //use MaxMind\Db\Reader 修改文件，不会报错导致程序无法进行
                        $this->add_log($get_str."ipnotindatabase=The address $ip is not in the database.", 'friday_product');
                       $country = 'US';
                   }else{
                       $country = $record->country->isoCode;
                   }
               } catch (ErrorException $e) {
                   $country = 'US';
               }

            }
        }

        $currency = '';
        if (in_array($country,array('US','CA')) ) {
            $currency = 'US';
        }elseif (in_array($country,array('AU','NZ'))) {
            $currency = 'AUD';
        }elseif (in_array($country,array('GB'))) {
            $currency = 'GBP';
        }elseif (in_array($country,array('DK','NO','FI','SE'))) {
            $currency = 'EUR';
        }else{
           $currency = 'US';
        }
        //,'IE'        

        $nowIntTime = time();
        $nowTime = date('Y-m-d H:i:s',$nowIntTime);
        $firstTime = $this->getProdEndDate();
        if ($nowTime < $firstTime) {
           $firstDown = strtotime($firstTime)-$nowIntTime;
        }else{
           $firstDown =-1;
        }
        $secondTime = '2015-12-01 13:00:00';
        if ($nowTime < $secondTime) {
           $seconDown = strtotime($secondTime)-$nowIntTime;
        }else{
           $seconDown = -1;
        }
        $price = array();
        $price['p3p'] = array('US' => array('$1,259','$1,159','8%') , 'GBP' => array('£1,159','£1,079','7%') ,'AUD' => array('$2,199','$2,049','7%') ,'EUR' => array('€1,399','€1,299','7%') );
        $price['p3s'] = array('US' => array('$799','$599','25%') , 'GBP' => array('£649','£539','17%') ,'AUD' => array('$1,299','$1,049','19%') ,'EUR' => array('€919','€719','22%') );

        $csrftoken = md5($ip).$nowIntTime;
        $csrftoken = strtoupper(hash_hmac("sha256", $csrftoken, "yfEpGRiagV2wDYr7bsrz"));
        $data = array('country' => $country,'currency' => $currency,'firstDown' => $firstDown,'seconDown' => $seconDown,'nowTime' => $nowIntTime,'price' => $price,'csrftoken'=>$csrftoken);
        Yii::$app->cache->set($luckkey, $data, 10);
        return $data;

    }

    /*
    *  开奖发送邮件 http://dev.e.dbeta.me/friday/prize
    *  @parameter nowTime 当前时间戳
    *  @parameter csrftoken 上次的token
    *  @parameter email 邮箱
    *  @parameter country 国家
    *  @parameter source  来源 0表示pc，1表示wap
    *  return {"status":200,"status_msg":"","prize_id":0,"level":0,"enabled":1,"nowTime":1447825167,"signature":"549d897ff728a780b45ff86fc96e226e"}
    * "status":200 表示用户抽奖完成,"prize_id":0 和 level 为0 表示没有中奖，如果prize_id为大于0，说明中奖了，level表示对应的奖级（1:桨叶,2:保护罩,3:礼品包-A,4:礼品包-B）
    * "status":1000 表示country参数不全或不合法; 1001表示email参数不全或不合法;1002表示csrftoken参数不全或不合法;1003表示Toke已经过期;1004表示重复提交5秒后，再重试;
    * 1005表示参数不全或不合法,实际意思是一小时内ip超过100个用户；1006表示活动已经结束；
    */
    public function actionPrize()
    {
        /*
        Yii::$app->mail->compose()
                ->setFrom('weiping.huang@dji.com')
                ->setTo('weiping.huang@dji.com')
                ->setSubject('friday')
                ->setTextBody('内容friday <a>dsfds</a>')
                ->send();
        */
        //use PHPMailer\PHPMailer\PHPMailerAutoload;
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'friday/prize');
        }
        $get_str = json_encode($_REQUEST);

        $get_str .= "SERVER=".json_encode($_SERVER);
        $this->add_log($get_str, 'friday_prize');
        $ip = $this->get_client_ip();
        $nowTime = Yii::$app->request->post('nowTime', '');
        $csrftoken = Yii::$app->request->post('csrftoken', '');
        $email = Yii::$app->request->post('email', '');
        $source = Yii::$app->request->post('source', 0);
        $country = Yii::$app->request->post('country', 0);
        if (empty($nowTime)) {
            $nowTime = Yii::$app->request->get('nowTime', '');
            $csrftoken = Yii::$app->request->get('csrftoken', '');
            $email = Yii::$app->request->get('email', '');
            $source = Yii::$app->request->get('source', 0);
            $country = Yii::$app->request->get('country', 0);
        }
        $source += 0;
        $email = strtolower($email);
        $email = trim($email);
        //,'IE'
        if ( empty($nowTime)  || empty($country)  || !in_array($country,array('US','CA','AU','NZ','GB','DK','NO','FI','SE')) ) {
            $this->add_log($get_str.'status=1000', 'friday_prize');
            $status = 1000;
            $result = array('status' => $status, 'status_msg' => 'Only countries involved in the promotion are eligable. If you reside in one of the promotional countries, please select from the above list.' );
            die(json_encode($result));
        }

        if (empty($email) ||  !$this->validate_is_email($email)  || strlen($email) > 50 ) {
            $this->add_log($get_str.'status=1001', 'friday_prize');
            $status = 1001;
            $result = array('status' => $status, 'status_msg' => 'Please enter correct email address.' );
            die(json_encode($result));
        }

        $checkcsrftoken = md5($ip).$nowTime;
        $checkcsrftoken = strtoupper(hash_hmac("sha256", $checkcsrftoken, "yfEpGRiagV2wDYr7bsrz"));
        $nowIntTime = time();

        //$newcsrftoken = md5($ip).$nowIntTime;
        //$newcsrftoken = strtoupper(hash_hmac("sha256", $newcsrftoken, "yfEpGRiagV2wDYr7bsrz"));
        if ($csrftoken != $checkcsrftoken) {
            $this->add_log($get_str.'status=1002', 'friday_prize');
            $status = 1002;
            $result = array('status' => $status,  'status_msg' => 'Please enter correct email address .');
            die(json_encode($result));
        }

        if ($nowIntTime-$nowTime > 3600) {
           // $this->add_log($get_str.'status=1003', 'friday_prize');
           // $status = 1003;
           // $result = array('status' => $status, 'status_msg' => 'Toke已经过期');
           // die(json_encode($result));
        }

        $emailkey = 'actionPrizeData'.md5($email);
        $emaildata = Yii::$app->cache->get($emailkey);
        if ( $emaildata ) {
            return $emaildata;
        }

        Yii::$app->cache->set($emaildata, 1, 5);


        $luckkey = 'actionPrizeluck'.md5($email);
        $luckdata = Yii::$app->cache->get($luckkey);
        if ( $luckdata ) {
            $status = 1004;
            $result = array('status' => $status,  'status_msg' => 'This email address has already been entered into the Lucky Draw.');
            die(json_encode($result));
        }
        Yii::$app->cache->set($luckkey, 1, 5);


        $firstTime = $this->getEndDate();
        if (strtotime($firstTime) < $nowIntTime) {
            $status = 1006;
            $result = array('status' => $status,  'status_msg' => 'Activity has ended.');
            die(json_encode($result));
        }
        $activity_id = 1;

        $where = array();
        $where['user_key'] = $email;
        $where['activity_id'] = $activity_id;
        $start =0;
        $data = Apply::getAndEqualWhere($where,$start,1);
        $friday_key = 'yQRfN7Euppb3aTcJCCdy';
        $level = 0;
        $levelname = '';
        if ($data)
        {
            $status=200;
            $prize_id = $data['0']['prize_id'];
            if ($prize_id > 0) {
                $where = array();
                $where['id'] = $prize_id;
                $where['activity_id'] = 1;
                $prizedata = Prize::getAndEqualWhere($where,0,1);
                if ($prizedata) {
                     $level = $prizedata['0']['level'];
                     $levelname = $prizedata['0']['ext1'];
                }

            }
            $enabled = 2;
            $signature = md5($status.$prize_id.$level.$enabled.$nowIntTime.$friday_key);
            $result = array('status' => $status,  'status_msg' => '','prize_id' => $prize_id,'level' => $level, 'levelname' => $levelname ,'enabled' =>$enabled,'nowTime' => $nowIntTime,'signature' => $signature);
            Yii::$app->cache->set($emaildata, $result, 3600);
            return $result;
        }else{
            if (strlen($ip) > 7) {
                $ipluckkey = 'actionPrizeluck'.md5($ip);
                $ipluckdata = Yii::$app->cache->get($ipluckkey);
                if ( $ipluckdata > 60  ) {
                    $status = 1005;
                    $this->add_log($get_str.'status=1005', 'friday_prize');
                    $result = array('status' => $status,  'status_msg' => 'Please note, to avoid fraudulent behavior, only the IP address you are currently connecting from will be accepted as useable.');
                    die(json_encode($result));
                }
                Yii::$app->cache->set($ipluckkey, $ipluckdata+1, 3600);
            }

            $prize_id =$updatePrizeId = $wherelevel = 0;
            $ext2 = $ext1 = '';
            $rand = mt_rand(1, 20);
            //echo $rand;
            $lotterydate = date("Y-m-d",$nowIntTime);
            if ($ip == '116.66.221.253') {
                 //目前不是公司的IP无法中奖，公司的邮箱不能中奖
                 $rand = 30;
            }

           // $rand = 10; //上线时去掉
            if ($rand < 21 ) {
                if ($rand < 5 ) {
                    $wherelevel = 1;
                }elseif ($rand < 10) {
                    $wherelevel = 2;
                }elseif ($rand < 15) {
                    $wherelevel = 3;
                }else{
                    $wherelevel = 4;
                }
                while ($wherelevel > 0 ) {
                    $where = array();
                    //$where['lotterydate'] = $lotterydate; //每天中奖限制
                    $where['activity_id'] = $activity_id;
                    $where['number'] = 1;
                    $where['level'] = $wherelevel;
                    $prizedata = Prize::getAndEqualWhere($where,0,1);
                    if ($prizedata && $prizedata['0']['number'] > 0 ) {
                        $prize_id = $prizedata['0']['id'] + 0;
                        $level = $prizedata['0']['level'];
                        $ext2 = $prizedata['0']['coupon'];
                        $ext1 = $prizedata['0']['ext1'];
                        $model = array();
                        $model['id'] = $prize_id;
                        $model['number'] = 0;
                        $updatePrizeId = Prize::updateInfoNumber($model);
                        $wherelevel = 0;
                    }else{
                       $wherelevel--;
                    }

                }



            }
            if ($updatePrizeId > 0) {
                if ($prize_id > 0) {
                    $where = array();
                    $where['prize_id'] = $prize_id;
                    $where['activity_id'] = $activity_id;
                    $prizedata = apply::getAndEqualWhere($where,0,1);
                    if ($prizedata) {
                        $prize_id = 0;
                        $ext2 = $ext1 = '';
                    }
                }
            }else{
                $prize_id = 0;
                $ext2 = $ext1 = '';
            }
            $model = array();
            $model['activity_id'] = $activity_id;
            $model['user_key'] = $email;
            $model['email'] = $email;
            $model['nationality'] = $country;
            $model['ip'] = $ip;
            $model['ip_country'] = $country;
            $model['ext1'] = $ext1;
            $model['ext2'] = $ext2;
            $model['prize_id'] = $prize_id;
            $model['source'] = $source;
            $model['joindate'] = $lotterydate;
            $activityId = apply::add($model);
            //$activityId =0;
            if ($activityId > 0 && $prize_id >0 ) {
               $resultEmail = $this->send_email($email,$ext2,$ext1);
            }
            $enabled = 1;
            $status = 200;
            $signature = md5($status.$prize_id.$level.$enabled.$nowIntTime.$friday_key);
            $result = array('status' => $status,  'status_msg' => '','prize_id' => $prize_id,'level' => $level, 'levelname' => $ext1,'enabled' =>$enabled,'nowTime' => $nowIntTime,'signature' => $signature);
            Yii::$app->cache->set($emaildata, $result, 3600);
            return $result;
        }



    }
    /*
    * 发送详细的中奖邮件
    */
    protected function send_email($address,$coupon,$name,$currency='USD',$level='3')
    {

        $comfile = __DIR__.'/../commands/FridaySendEmail.php';
       // echo "nohup php $comfile $address $coupon '$name' > /dev/null &";
        @system("php $comfile $address \"$coupon\"  \"$name\" \"$currency\" \"$level\"  > /dev/null & ");
        return true;

        require __DIR__.'/../vendor/phpmailer/phpmailer/PHPMailerAutoload.php';
        $config_mail = require(__DIR__.'/../config/mail.php');
        $mail = new PHPMailer;
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = $config_mail['host'];  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = $config_mail['options']['username'];                 // SMTP username
        $mail->Password = $config_mail['options']['password'];                           // SMTP password
        $mail->SMTPSecure = $config_mail['options']['ssl'];                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = $config_mail['options']['port'];                                    // TCP port to connect to

        $mail->setFrom($config_mail['from']['mail'], $config_mail['from']['name']);

        $mail->addAddress($address);     // Add a recipient
        //$mail->addAddress('ellen@example.com');              // Name is optional       
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = "Congratulations! You've won DJI's Lucky Draw!";
        $addresslist = explode('@',$address);
        $addressname = $addresslist[0];
        $emailbody = <<<EOF
        Dear ${addressname},<br/>
        We're happy to tell you that you've won a [${name}] in our Lucky Draw. Your gift code is [${coupon}] and is valid between November 26-30th.<br/>
        When you purchase a Phantom 3 Professional or Phantom 3 Standard in the Official DJI Store, you can enter the gift code on the checkout page when you complete your purchase.<br/>
        Only one code can be used per purchase. Please note that the gift code can not be used together with DJI Credits.<br/><br/><br/>
        Click to <a href='https://store.dji.com/event/black-friday'>DJI Black Friday</a> <br/>
        Page store.dji.com/event/black-friday<br/>
        Thanks for playing!<br/>
        DJI
EOF;

        $mail->Body    = $emailbody;
        $mail->AltBody = $emailbody;

        if(!$mail->send()) {
            $this->add_log('Mailer Error: ' . $mail->ErrorInfo.$mail->Body.$mail->Subject , $type = 'friday_error');
            return false;
        } else {
            return true;
        }

    }


    protected function get_activity_id($country)
    {
        if ($country == 'cn') {
            return 2;
        }else if (in_array($country,array('tw','mo','my','sg'))) {
            return 3;
        }else if ($country == 'hk' ) {
            return 4;
        }
        return 0;
    }

     protected function get_currency($country)
    {
        if ($country == 'cn') {
            return 'RMB';
        }else if (in_array($country,array('tw','mo','my','sg'))) {
            return 'USD';
        }else if ($country == 'hk' ) {
            return 'HKD';
        }
        return '';
    }


    protected function getMetaKey()
    {
        return Yii::$app->request->cookies['_meta_key'];
    }

    protected function checkIfNotLogin()
    {
        return empty($this->getMetaKey());
    }

    protected function getLoginUrl($country)
    {
        $from = '9205e11f-4d21-423a-b7ac-a83de267f58b';
        if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']) {
           $data = base64_encode($_SERVER['HTTP_REFERER']);
        }else{
           $data = "";
        }        
        $sign = md5($from . $data);

        $host = YII_DEBUG ? 'https://www.dbeta.me' : ($this->isMobile() ? 'https://m.dji.com' : 'https://www.dji.com');
        if (in_array($country,array('cn'))) {
            $url = $host . ($this->isMobile() ? '/cn/sessions/new' : '/cn/user/login');
        }elseif (in_array($country,array('tw','mo','hk'))) {
            $url = $host . ($this->isMobile() ? '/cn/sessions/new' : '/zh-tw/user/login');
        }else{
            $url = $host . ($this->isMobile() ? '/sessions/new' : '/user/login');
        }        
        return $url . '?from=dji-event&data=' . $data . '&sign=' .$sign;
    }

    protected function isMobile()
    {
        return $_SERVER['HTTP_HOST'] == 'm.dji.com';
    }

    protected $prizeData = array(
        'RMB' => array(array(4000, 1000), array(500, 50), array(100, 5)),
        'HKD' => array(array(5300, 1200), array(720, 72), array(150, 8)),
        'USD' => array(array(680, 160), array(95, 5), array(20, 1))
    );

    protected function getPrizeResult($currency, $level)
    {
        return $this->prizeData[$currency][((int) $level) - 1];
    }

    /*
    *  春节抽奖活动 参与接口  http://dev.e.dbeta.me/blackfriday/eventprize   
    *  @parameter meta_key meta_key
    *  @parameter country 国家
    *  @parameter source  来源 0表示pc，1表示wap
    *  return {"status":200,"status_msg":"","prize_id":0,"level":0,"enabled":1,"nowTime":1447825167,"signature":"549d897ff728a780b45ff86fc96e226e"}
    * "status":200 表示用户抽奖完成,"prize_id":0 和 level 为0 表示没有中奖，如果prize_id为大于0，说明中奖了，level表示对应的奖级（1:1000,2:50,3:5,4:50个名额以旧换新）
    * "status":1000 表示country参数不全或不合法; 1001表示email参数不全或不合法;1002表示csrftoken参数不全或不合法;1003表示Toke已经过期;1004表示重复提交5秒后，再重试;
    * 1005表示参数不全或不合法,实际意思是一小时内ip超过100个用户；1006表示活动已经结束；
    */
    public function actionEventprize()
    {  
        /*   session_start();
        
        $csrftoken = md5($ip);
        if (!isset($_SESSION['event_csrftoken']) || $_SESSION['event_csrftoken'] != $csrftoken) {
            $status = 1010;           
            $result = array('status' => $status, 'status_msg'=>'failed','message' => '' );
            die(json_encode($result)); 
        }  
        */       

        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'blackfriday/eventprize');
        }
        $get_str = json_encode($_REQUEST);
       
        $get_str .= "SERVER=".json_encode($_SERVER);
        $this->add_log($get_str, 'eventprize');
        $ip = $this->get_client_ip();
        $nowTime = Yii::$app->request->post('nowTime', '');
        $csrftoken = Yii::$app->request->post('csrftoken', '');
        $meta_key = Yii::$app->request->post('meta_key', '');
        $source = Yii::$app->request->post('source', 0);
        $country = Yii::$app->request->post('country', '');
        if (empty($country)) {
            $nowTime = Yii::$app->request->get('nowTime', '');
            $csrftoken = Yii::$app->request->get('csrftoken', ''); 
            $meta_key = Yii::$app->request->get('meta_key', '');          
            $source = Yii::$app->request->get('source', 0);
            $country = Yii::$app->request->get('country', '');
        }
        $source += 0;        
        $meta_key = trim($meta_key);
        $country = strtolower($country);
        $token = '';
        if (empty($meta_key) && isset($_COOKIE['wn2grgIkKubUV044']) && $_COOKIE['wn2grgIkKubUV044']) {
            $token = $_COOKIE['wn2grgIkKubUV044'];
        }else{
            if ($meta_key) {
                $meta_key = base64_decode($meta_key);
            }elseif ($this->checkIfNotLogin()) {
                $status = 308;
                $result = array('status' => $status, 'url' => $this->getLoginUrl($country));
                die(json_encode($result));
            } else if (empty($meta_key)) {
                $meta_key = $this->getMetaKey();
            }

            if ( empty($meta_key) ) {
                $meta_key = $_COOKIE['_meta_key'];
            }
            if (empty($meta_key)) {
                $status = 505;                              
                $result = array('status' => $status, 'status_msg'=>'failed','message' => 'key无效或者已过期' );
                die(json_encode($result)); 
            }

        }       
        

        if ( empty($country) ) {
            $this->add_log($get_str.'status=1000', 'eventprize');
            $status = 1000;           
            $result = array('status' => $status, 'status_msg'=>'failed','message' => 'Only countries involved in the promotion are eligable. If you reside in one of the promotional countries, please select from the above list.' );
            die(json_encode($result)); 
        }
        
        if ($this->get_activity_id($country) == '0') {
            $status = 1002;  
            $this->add_log($get_str.'status='.$status, 'eventprize');                     
            $result = array('status' => $status, 'status_msg'=>'failed','message' => 'Only countries involved in the promotion are eligable. If you reside in one of the promotional countries, please select from the above list.' );
            die(json_encode($result)); 
        }
        $nowIntTime = time();

        /*        

        $checkcsrftoken = md5($ip).$nowTime;
        $checkcsrftoken = strtoupper(hash_hmac("sha256", $checkcsrftoken, "yfEpGRiagV2wDYr7bsrz"));
        

        //$newcsrftoken = md5($ip).$nowIntTime;
        //$newcsrftoken = strtoupper(hash_hmac("sha256", $newcsrftoken, "yfEpGRiagV2wDYr7bsrz"));
        
        if ($csrftoken != $checkcsrftoken) {
            $this->add_log($get_str.'status=1002', 'friday_prize');
            $status = 1002;           
            $result = array('status' => $status,  'status_msg' => 'Please enter correct email address .');
            die(json_encode($result));
        }   
        */ 
        $emailkey = 'v2actionPrizeEeventDataKey'.md5($meta_key.$token);   
        $emaildata = Yii::$app->cache->get($emailkey);
        if ( $emaildata ) {
            return $emaildata;
        }

        $lastkey = 'actioneventprizelast'.md5($meta_key.$token); 
        $luckkey = 'actioneventprizeluck'.md5($meta_key.$token);   
        $luckdata = Yii::$app->cache->get($luckkey);
        if ( $luckdata ) {
            $lastdate = Yii::$app->cache->get($lastkey);
            if ($lastdate) {
                return $lastdate;
            }
            $status = 1004;           
            $result = array('status' => $status, 'status_msg'=>'failed', 'message' => 'This email address has already been entered into the Lucky Draw.');
            die(json_encode($result));
        }
        Yii::$app->cache->set($luckkey, 1, 5);
        $firstTime = $this->getEventEndDate();

        if (strtotime($firstTime) < $nowIntTime) {
            $status = 1006;           
            $result = array('status' => $status, 'status_msg'=>'failed', 'message' => 'Activity has ended.');
            Yii::$app->cache->set($lastkey, $result, 600);
            die(json_encode($result));
        }

        if (file_exists(__DIR__ . '/../config/.config.php')) {
            require(__DIR__ . '/../config/.config.php');
        }
        $gwapi = isset($YII_GLOBAL['GWServer']['GWAPIURL']) ? $YII_GLOBAL['GWServer']['GWAPIURL'] : '';
        $gwapikey = isset($YII_GLOBAL['GWServer']['GWAPIFLYSAFEKEY']) ? $YII_GLOBAL['GWServer']['GWAPIFLYSAFEKEY'] : '';

        $account = $this->get_account_info_by_key($gwapi,$gwapikey,$meta_key,$token); 
        if ($account && $account['status'] == '0' && $account['status_msg'] == 'ok' ) {
           $account = $account['items']['0'];
        }else{
           Yii::$app->cache->set($lastkey, $account, 600);
           die(json_encode($account));
        }

        $activity_id = array('2','3','4');
        $activity_count = 3; //每天抽奖3次
        $user_key = $account['account_info']['user_id'];
        $email = $account['account_info']['email'];
        $lotterydate = date("Y-m-d",$nowIntTime);  
        $where = array();
        $where['user_key'] = $user_key;
        $where['activity_id'] = $activity_id;
        $start =0;
        $data = Apply::getAndEqualWhere($where,$start,30);        
        $friday_key = 'yQRfN7Euppb3aTcJCCdy';
        $level = $tmpactivity_id= 0;
        $isPrize = 0;
        $levelname = $coupon= '';
        if ($data)
        {            
            foreach ($data as $key => $value) {
                if ($value['prize_id']  > 0) {
                    $isPrize = 1;
                    /*
                    $prize_id = $value['prize_id'];   
                    $where = array();
                    $where['id'] = $prize_id;
                    //$where['activity_id'] = $activity_id;
                    $prizedata = Prize::getAndEqualWhere($where,0,1); 
                    if ($prizedata) {
                         $level = $prizedata['0']['level'];
                         $levelname = $prizedata['0']['ext1'];
                         $coupon=$prizedata['0']['coupon'];
                         $tmpactivity_id = $prizedata['0']['activity_id'];
                    }
                    $status= 0;
                    $enabled = 2;
                    $signature = md5($status.$prize_id.$level.$enabled.$nowIntTime.$friday_key);
                    $result = array('status' => $status,  'status_msg' => 'ok','prize_id' => $prize_id,'level' => $level, 'levelname' => $levelname ,'enabled' =>$enabled,'nowTime' => $nowIntTime,'coupon' => $coupon,
                      'activity_id' => $tmpactivity_id,'country' =>$value['nationality'],'currency' => $this->get_currency($value['nationality']),'signature' => $signature); 
                    Yii::$app->cache->set($emaildata, $result, 3600);
                    return $result;
                    */
                }
                
                if ($lotterydate == $value['joindate']) {
                    $activity_count--;
                }

            }                     
            if ($activity_count <=  0) {  
                $activity_count = 0;                  
                if (strtotime('2016-01-27 23:59:59') < $nowIntTime) {
                    $status = 1008;  
                }else{
                    $status = 1007;  
                }                 
                $this->add_log($get_str.'status='.$status, 'eventprize');        
                $result = array('status' => $status,'status_msg' => 'failed', 'activity_count' => $activity_count,'nowTime' => $nowIntTime, 'message' => 'activity_count');
                Yii::$app->cache->set($emailkey, $result, 600);
                return $result;
            }           
        }

        if ($activity_count > 0) {            
            $activity_id = $this->get_activity_id($country);
            if (strlen($ip) > 7) {
                $ipluckkey = 'actioneventprizeluck'.md5($ip);   
                $ipluckdata = Yii::$app->cache->get($ipluckkey);
                if ( $ipluckdata > 300  ) {
                    $status = 1005;   
                    $this->add_log($get_str.'status=1005', 'eventprize');        
                    $result = array('status' => $status,'status_msg' => 'ok',  'message' => 'Please note, to avoid fraudulent behavior, only the IP address you are currently connecting from will be accepted as useable.');
                    die(json_encode($result));
                }
                Yii::$app->cache->set($ipluckkey, $ipluckdata+1, 3600);
            }

            $prize_id = $updatePrizeId = $wherelevel = 0;
            $ext2 = $ext1 = '';
            $rand = mt_rand(1, 10000);
            
            
            if ($ip == '116.66.221.253') {
                 //目前不是公司的IP无法中奖，公司的邮箱不能中奖
                 //$rand = 9000;
                 //$isPrize = 1;
            }
            $PrizeCountkey = 'v2actionPrizeEeventPrizeCountkey';   
            $PrizeCountkeydata = Yii::$app->cache->get($PrizeCountkey);
            Yii::$app->cache->set($PrizeCountkey, $PrizeCountkeydata + 1, 3600); 
            $Prizekey = 'v2actionPrizeEeventPrizekey';   
            $Prizekeydata = Yii::$app->cache->get($Prizekey);
            if ( $Prizekeydata && $PrizeCountkeydata && $PrizeCountkeydata > 0 && $Prizekeydata >0) {
                  //增加风险控制
                  if ($Prizekeydata/$PrizeCountkeydata > 0.6 ) {
                      $isPrize = 1; 
                  }                 
            }
            //如果已经中奖了，就不能在中奖
            if ($isPrize == 1) {
                $wherelevel = 0;
            }else{
                
                //如果是
                if ($email && $activity_id == '2' && UserExchange::getAndEqualWhere(array('activity_id' => 2,'account' => $email),0,1) ) {
                    if ($rand < 3 ) {
                        $wherelevel = 1;
                    }else{
                        $rand = mt_rand(1, 100);
                        //echo $rand."=====";exit;
                        if ($rand < 4) {
                            $wherelevel = 2;
                        }elseif ($rand < 54) {
                            $wherelevel = 3;
                        }elseif ($rand < 60) {
                            $wherelevel = 4;
                        }else{
                            $wherelevel = 0;
                        } 
                    }      
                }else{
                    if ($rand < 3 ) {
                        $wherelevel = 1;
                    }else{
                        $rand = mt_rand(1, 100);
                        if ($rand < 4) {
                            $wherelevel = 2;
                        }elseif ($rand < 54) {
                            $wherelevel = 3;
                        }else{
                            $wherelevel = 0;
                        } 
                    }      
                }


            }
            //echo $rand."=====".$wherelevel;exit;
                            
            while ($wherelevel > 0 ) {
                $where = array();
                $where['lotterydate'] = $lotterydate; //每天中奖限制
                $where['activity_id'] = $activity_id;
                $where['number'] = 1;
                $where['level'] = $wherelevel;
                $prizedata = Prize::getAndEqualWhere($where,0,1);               
                if ($prizedata && $prizedata['0']['number'] > 0 ) {
                    $prize_id = $prizedata['0']['id'] + 0;
                    $level = $prizedata['0']['level'];
                    $ext2 = $prizedata['0']['coupon'];
                    $ext1 = $prizedata['0']['ext1']; 
                    $model = array();
                    $model['id'] = $prize_id;
                    $model['number'] = $prizedata['0']['number']-1;
                    $updatePrizeId = Prize::updateInfoNumber($model); 
                    $wherelevel = 0;
                }else{
                   $wherelevel--; 
                }
                
            }            
            if ($updatePrizeId > 0) {
                if ($prize_id > 0) {
                    $where = array();
                    $where['prize_id'] = $prize_id;
                    $where['activity_id'] = $activity_id;                   
                    $prizedata = apply::getAndEqualWhere($where,0,1);                     
                    if ($prizedata) {
                        $prize_id = 0;
                        $ext2 = $ext1 = '';
                    }
                }
            }else{
                $prize_id = 0;
                $ext2 = $ext1 = '';
            }  

            if ($prize_id > 0) {
                 Yii::$app->cache->set($Prizekey, $Prizekeydata + 1, 3600);  
            }

            $model = array();
            $model['activity_id'] = $activity_id;
            $model['user_key'] = $user_key;
            $model['email'] = $email;
            $model['nationality'] = $country;            
            $model['ip'] = $ip;
            $model['ip_country'] = $country;
            $model['ext1'] = $ext1;
            $model['ext2'] = $ext2;
            $model['prize_id'] = $prize_id;
            $model['source'] = $source;
            $model['joindate'] = $lotterydate;
            $activityId = apply::add($model); 
            //$activityId =0;
            $currency = $this->get_currency($country);
            if ($email && $activityId > 0 && $prize_id >0  && $level !='4') {
                //发邮件
               $resultEmail = $this->send_email($email,$ext2,$ext1,$country,$level);

            }            
            $enabled = 1;
            $status = 0;
            $signature = md5($status.$prize_id.$level.$enabled.$nowIntTime.$ext2.$friday_key);
            $activity_count = $activity_count == 0 ? $activity_count : $activity_count-1;

            // use it for NewYear LuckyDraw
            if ($prize_id > 0 && $level != '4') {
                $prize = $this->getPrizeResult($currency, $level);
            }else{
                $prize = 0;
            }

            $result = array(
                'status' => $status,
                'status_msg' => 'ok',
                'prize_id' => $prize_id,
                'prize' => $prize,
                'level' => $level,
                'levelname' => $ext1,
                'enabled' =>$enabled,
                'nowTime' => $nowIntTime,
                'country' => $country,
                'currency' => $currency,
                'coupon' => $ext2,
                'activity_count' => $activity_count,
                'activity_id' => $activity_id,
                'signature' => $signature);
            //Yii::$app->cache->set($lastkey, $result, 10);
            $get_str .= json_encode($result)."model=".json_encode($model)."&user_key=".$user_key."&email=".$email."&Prizekeydata=".$Prizekeydata."&PrizeCountkeydata=".$PrizeCountkeydata;
            $this->add_log($get_str, 'eventprize',0);
            return $result;           
        }
    }
    //用户登录系统
    /*
    *  用户登录系统 参与接口  http://dev.e.dbeta.me/blackfriday/userlogin
    *  @parameter login_name 用户名
    *  @parameter passwd 密码
    *  @parameter source  来源 0表示pc，1表示wap
    *  @parameter time  当前时间戳
    *  @parameter signature 签名 
    *  return {"status":200,"status_msg":"","prize_id":0,"level":0,"enabled":1,"nowTime":1447825167,"signature":"549d897ff728a780b45ff86fc96e226e"}
    * "status":200 表示用户抽奖完成,"prize_id":0 和 level 为0 表示没有中奖，如果prize_id为大于0，说明中奖了，level表示对应的奖级（1:1000,2:50,3:5,4:50个名额以旧换新）
    * "status":1000 表示country参数不全或不合法; 1001表示email参数不全或不合法;1002表示csrftoken参数不全或不合法;1003表示Toke已经过期;1004表示重复提交5秒后，再重试;
    * 1005表示参数不全或不合法,实际意思是一小时内ip超过100个用户；1006表示活动已经结束；
    */
    public function actionUserlogin()
    {
        $get_str = json_encode($_REQUEST);       
        $get_str .= "SERVER=".json_encode($_SERVER);
        $this->add_log($get_str, 'userlogin');
        $ip = $this->get_client_ip();
        $nowTime = Yii::$app->request->post('nowTime', '');
        $csrftoken = Yii::$app->request->post('csrftoken', '');
        $login_name = Yii::$app->request->post('login_name', '');
        $passwd = Yii::$app->request->post('passwd', '');
        $source = Yii::$app->request->post('source', 0);
        $time = Yii::$app->request->post('time', '');    
        $signature = Yii::$app->request->post('signature', '');     
        if (empty($login_name)) {
            $nowTime = Yii::$app->request->get('nowTime', '');
            $csrftoken = Yii::$app->request->get('csrftoken', '');
            $login_name = Yii::$app->request->get('login_name', '');
            $passwd = Yii::$app->request->get('passwd', '');
            $source = Yii::$app->request->get('source', 0);
            $time = Yii::$app->request->get('time', '');  
            $signature = Yii::$app->request->get('signature', ''); 
           
        }
        if (empty($signature) || empty($login_name) || empty($passwd) ) {
            $status = 1000;           
            $result = array('status' => $status, 'status_msg'=>'failed', 'message' => '');
            die(json_encode($result));
        }
        $privateKey = 'ymZDBhxvEQLtk79';
        $nowSignature = substr(md5($login_name.$passwd.$source.$time.$privateKey),5,20);
        //echo $nowSignature ;
        if ($signature != $nowSignature ) {
            $status = 1003;           
            $result = array('status' => $status, 'status_msg'=>'failed', 'message' => 'signature ');
            die(json_encode($result));
        }
        $emailkey = 'actionUserlogin'.md5($login_name.$passwd);   
        $emaildata = Yii::$app->cache->get($emailkey);
        if ( $emaildata ) {
            return $emaildata;
        }       
        $luckkey = 'actionUserlogin'.md5($login_name);   
        $luckdata = Yii::$app->cache->get($luckkey);
        if ( $luckdata ) {           
            $status = 1004;           
            $result = array('status' => $status, 'status_msg'=>'failed', 'message' => '');
           // die(json_encode($result));
        }
        Yii::$app->cache->set($luckkey, 1, 5);
        $firstTime = $this->getEventEndDate();
        $nowIntTime =time();
        if (strtotime($firstTime) < $nowIntTime) {
            $status = 1006;           
            $result = array('status' => $status, 'status_msg'=>'failed', 'message' => 'Activity has ended.');          
            die(json_encode($result));
        }
        $UserIP =  Yii::$app->request->getUserIP();
        if ($UserIP) {
            $luckkeyIP = 'actionUserlogin'.md5($UserIP);
            $luckdataIP = Yii::$app->cache->get($luckkeyIP);
            if ( $luckdataIP > 10 ) {
                $status = 1009;           
                $result = array('status' => $status, 'status_msg'=>'failed', 'message' => '');
                die(json_encode($result));
            }
        }
        $namekey = 'actionUserlogin'.md5($login_name);

        $namedata = Yii::$app->cache->get($namekey);
        if ( $namedata > 5 ) {
                $status = 1010;           
                $result = array('status' => $status, 'status_msg'=>'failed', 'message' => '');
                die(json_encode($result));
        }       
        $luckkeypassword = 'actionUserlogin'.md5($passwd);
        $luckdatapassword = Yii::$app->cache->get($luckkeypassword);
        if ( $luckdatapassword > 5 ) {
            $status = 1011;           
            $result = array('status' => $status, 'status_msg'=>'failed', 'message' => '');
            die(json_encode($result));
        } 
        if (file_exists(__DIR__ . '/../config/.config.php')) {
            require(__DIR__ . '/../config/.config.php');
        }
        $gwapi = isset($YII_GLOBAL['GWServer']['GWAPIURL']) ? $YII_GLOBAL['GWServer']['GWAPIURL'] : '';
        $gwapikey = isset($YII_GLOBAL['GWServer']['GWAPIFLYSAFEKEY']) ? $YII_GLOBAL['GWServer']['GWAPIFLYSAFEKEY'] : '';

        $account = $this->get_user_all_login($gwapi,$gwapikey,$login_name,$passwd); 
        if ($account && $account['status'] == '0' && $account['status_msg'] == 'ok' ) {
            //var_dump($account['items']);exit;
            setcookie($account['items'][0]['cookie_name'], $account['items'][0]['cookie_key'] ,  time()+3600);
            return $account;
        }else{
            Yii::$app->cache->set($namekey, $namedata+1, 3600);
            Yii::$app->cache->set($luckkeypassword, $luckdatapassword+1, 3600);
            if ($luckkeyIP) {
                Yii::$app->cache->set($luckkeyIP, $luckdataIP+1, 600);
            } 
           Yii::$app->cache->set($emailkey, $account, 600);
        }
        return $account;

    }

    protected function get_user_all_login($gwapi,$gwapikey,$login_name,$passwd,$appid_device='dji_events')
    {
        $luckkey = 'get_user_all_login'.md5($login_name.$passwd.$appid_device);   
        $luckdata = Yii::$app->cache->get($luckkey);
        if ( $luckdata ) {
           return $luckdata;
        }        

        $accessToken = $this->getAccessToken($gwapi,$gwapikey);
       
        $invokeId = time();
        $url = $gwapi."/gwapi/api/accounts/user_all_login";
        $imgdata = array();
        $imgdata['login_name'] = $login_name;
        $imgdata['passwd'] = $passwd;
        $imgdata['appid_device'] = $appid_device;

        $header = array('User-Agent: Opera/9.80 (Windows NT 6.2; Win64; x64) Presto/2.12.388 Version/12.15','Referer: http://www.dji.com','Content-Type: multipart/form-data');
        $header[] = 'consumerAppId-gw: dji_events';
        $header[] = 'providerAppId-gw: member_center';
        $header[] = 'accessToken-gw: '.$accessToken;
        $header[] = 'invokeId-gw: '.$invokeId;
        $header[] = 'directDomain-gw: ';
        $header[] = 'domain: ';

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        //curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_TIMEOUT,5); 
        curl_setopt($ch, CURLOPT_POST, 0);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $imgdata);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // if any redirection after upload
        $data = curl_exec($ch);//运行curl
        curl_close($ch);       
        $data = json_decode($data,true);
        if ($data && $data['status'] == '0' && $data['status_msg'] == 'ok' ) {
             Yii::$app->cache->set($luckkey, $data, 3600);
        }
        return $data;


    }

    protected function get_account_info_by_key($gwapi,$gwapikey,$meta_key,$token)
    {
        $luckkey = 'get_account_info_by_key'.md5($meta_key.$token);   
        $luckdata = Yii::$app->cache->get($luckkey);
        if ( $luckdata ) {
           return $luckdata;
        }
        $accessToken = $this->getAccessToken($gwapi,$gwapikey);
        if (empty($token)) {
            $token = $this->get_token_by_meta_key($gwapi,$gwapikey,$meta_key);       
            if ($token && $token['status'] == '0' && $token['status_msg'] == 'ok' ) {
               $token = $token['items']['0']['token'];
            }else{
               return $token;
            }
        }
       
        $invokeId = time();
        $url = $gwapi."/gwapi/api/accounts/get_account_info_by_key";
        $imgdata = array();
        $imgdata['token'] = $token;

        $header = array('User-Agent: Opera/9.80 (Windows NT 6.2; Win64; x64) Presto/2.12.388 Version/12.15','Referer: http://www.dji.com','Content-Type: multipart/form-data');
        $header[] = 'consumerAppId-gw: dji_events';
        $header[] = 'providerAppId-gw: member_center';
        $header[] = 'accessToken-gw: '.$accessToken;
        $header[] = 'invokeId-gw: '.$invokeId;
        $header[] = 'directDomain-gw: ';
        $header[] = 'domain: ';

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        //curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_TIMEOUT,5); 
        curl_setopt($ch, CURLOPT_POST, 0);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $imgdata);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // if any redirection after upload
        $data = curl_exec($ch);//运行curl
        curl_close($ch);       
        $data = json_decode($data,true);
        if ($data && $data['status'] == '0' && $data['status_msg'] == 'ok' ) {
             Yii::$app->cache->set($luckkey, $data, 3600);
        }
        return $data;

    }
    protected function get_token_by_meta_key($gwapi,$gwapikey,$meta_key,$appId='dji_events')
    {
        $luckkey = 'get_token_by_meta_key'.md5($meta_key);   
        $luckdata = Yii::$app->cache->get($luckkey);
        if ( $luckdata ) {
           return $luckdata;
        }        
        $accessToken = $this->getAccessToken($gwapi,$gwapikey);
        
        $invokeId = time();
        $url = $gwapi."/gwapi/api/accounts/get_token_by_meta_key";
        $imgdata = array();
        $imgdata['meta_key'] = $meta_key;
        $header = array('User-Agent: Opera/9.80 (Windows NT 6.2; Win64; x64) Presto/2.12.388 Version/12.15','Referer: http://www.dji.com','Content-Type: multipart/form-data');
        $header[] = 'consumerAppId-gw: dji_events';
        $header[] = 'providerAppId-gw: member_center';
        $header[] = 'accessToken-gw: '.$accessToken;
        $header[] = 'invokeId-gw: '.$invokeId;
        $header[] = 'directDomain-gw: ';
        $header[] = 'domain: ';

        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        //curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_TIMEOUT,5); 
        curl_setopt($ch, CURLOPT_POST, 0);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $imgdata);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // if any redirection after upload
        $data = curl_exec($ch);//运行curl
        $data = json_decode($data,true);
        if ($data && $data['status'] == '0' && $data['status_msg'] == 'ok' ) {
             Yii::$app->cache->set($luckkey, $data, 3600);
        }else{
            $luckkey = 'EventsGetAccessToken'.md5($gwapi.$appId);   
            Yii::$app->cache->set($luckkey, 0, 10); 
        }
        
        return $data;
    }

    protected function getAccessToken($gwapi,$gwapikey,$appId='dji_events')
    {
        $luckkey = 'EventsGetAccessToken'.md5($gwapi.$appId);   
        $luckdata = Yii::$app->cache->get($luckkey);
        if ( $luckdata ) {
           return $luckdata;
        }        
        $opts = array(
                 'http' => array(
                  'method'=>'GET',
                  'timeout' => 3
              )
         );
       
        $context = stream_context_create($opts);
        $locUrl =  $gwapi."/api/token/challengeCode?appId=".$appId;
        $challengeCode = file_get_contents($locUrl,false,$context);
        $signCode = urlencode(base64_encode(hash_hmac("sha1", $challengeCode, $gwapikey,true)  ));
        $url = $gwapi."/api/token?appId=dji_events&signCode=$signCode&challengeCode=$challengeCode";       
        $token = file_get_contents($url,false,$context);      
        $token = json_decode($token,true);
        $accessToken =  $token['accessToken'];
        Yii::$app->cache->set($luckkey, $accessToken, 3600);
        return $accessToken;

    }
       

   

     /**
     * 是否是电子邮件地址
     *
     * @param mixed $value
     *
     * @return boolean
     */
    protected function validate_is_email($value)
    {
        //return preg_match('/^[a-z0-9]+[._\-\+]*@([a-z0-9]+[-a-z0-9]*\.)+[a-z0-9]+$/i', $value);
        return preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $value);
    }
    
    protected function apache_request_headers() {
                // Source: http://www.php.net/manual/en/function.apache-request-headers.php#70810
                $arh = array();
                $rx_http = '/\AHTTP_/';
                foreach ($_SERVER as $key => $val) {
                    if (preg_match($rx_http, $key)) {
                        $arh_key = preg_replace($rx_http, '', $key);
                        $rx_matches = array();
                        // do some nasty string manipulations to restore the original letter case
                        // this should work in most cases
                        $rx_matches = explode('_', $arh_key);
                        if (count($rx_matches) > 0 and strlen($arh_key) > 2) {
                            foreach ($rx_matches as $ak_key => $ak_val) {
                                $rx_matches[$ak_key] = ucfirst(strtolower($ak_val));
                            }
                            $arh_key = implode('-', $rx_matches);
                        }
                        $arh[$arh_key] = $val;
                    }
                }
                return ($arh);
    }

    protected function get_headers()
    {
        if (!function_exists('apache_request_headers')) {
            $headers = $this->apache_request_headers();
        }else{
            $headers = apache_request_headers();
        }

        
        return $headers;
    }
     // 写入文件
    protected function add_log($msg, $type = 'friday', $isheader = '1')
    {
        $ip = $this->get_client_ip();
        $logfile = __DIR__.'/../runtime/logs/'.date('Ymd').'-'.$type.'.log';
        if ( ! file_exists($logfile))
        {
            file_put_contents($logfile, '');
        }
        $SERVER_ADDR = $_SERVER["SERVER_ADDR"];
        if ($isheader == '1') {
             $headers = $this->get_headers();
             $headers = json_encode($headers);
        }else{
            $headers = '';
        }
       
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
