<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\helpers\ArrayHelper;
//use yii\log;

use app\models\UserArea;
use app\models\UserAreasLicense;
use app\models\UserLicense;
use app\models\UserUnlimit;
use app\models\ErrorReportUnlimit;

class UserareaController extends Controller
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
	/*
    *  私密钥匙
    */
    private function getPrivateKey($version='1.0',$os='default')
    {
        if ( !in_array($version,array('1.0','1.1','1.2')) ) {
             $version='1.0';
        }
        if (!in_array($os,array('ios','android','airmap')) ) {
             $os='default';
        }
        $key_list = array('1.0' =>array('default'=>"0266d0567aef9b7ab3bc4eb1eade3123"),
                          '1.1'=>array('default'=>"MLfc2RpaiWmBiAcWNNz2RjkDfnkHsUZA",'ios'=>"CanVJKuo6ZT8kzYFrPwcLjcAqzWLKeUd",'android'=>"tcbRrxEPyPTzbZqPnH2kCfthvfj3Rpfz"),
                          '1.2'=>array('airmap'=>"JqY9CmQLfE4xnXGfGKHDwLehoEugbKGV")
                    );
        if (empty($key_list[$version][$os])) {
           return "0266d0567aef9b7ab3bc4eb1eade3123";
        }       
        return $key_list[$version][$os];
    }

	/*
    * 签名验证函数
    */
    public function getsignature($param){
        $tmp = '';
        foreach($param as $pv){
            $tmp .= $pv;   
        }
        $key = $this->getPrivateKey($param['version'],$param['os']);
        if ($param['version'] == '1.1' || $param['version'] == '1.2') {
            return strtoupper(hash_hmac("sha256", $tmp, $key));   
        }else{
            return substr(self::md5encrypt($tmp.$key),6,20); 
        }
        
    }
    /*
    * 返回签名验证函数
    */
    public function gethmacsignature($param,$sign){
        if ($param['version'] == '1.3' && $param['os'] == 'airmap') {
            return $this->getopensslsignature($sign);
        }
        $key = $this->getPrivateKey($param['version'],$param['os']);
        if ($param['version'] == '1.1' || $param['version'] == '1.2') {
            return strtoupper(hash_hmac("sha256", $sign, $key));   
        }else{
            return substr(self::md5encrypt($sign.$key),6,20); 
        }
        
    }    
    /*
    *重写md5方法
    */
    public function md5encrypt($value){
        return strtoupper(md5($value)); 
    } 

    /*
    * openssl签名验证函数
    */
    public function getsignatureverify($param,$mac){
        $tmp = '';
        foreach($param as $pv){
            $tmp .= $pv;   
        }        
        //echo $this->getopensslsignature($tmp);
               
        // 将 MAC  进行 decode 处理
        $mac=base64_decode($mac);
        $crtfile = $this->getcrtfile($param['os']);
        $fp = fopen($crtfile, "r"); 
        $cert = fread($fp, 8192); 
        fclose($fp); 
        $pubkeyid = openssl_get_publickey($cert); 
        $ok = openssl_verify($tmp, $mac, $pubkeyid); 
        return $ok;

    }
    /*
    * openssl签名函数生成签名字符串
    */
    public function getopensslsignature($sign){
       
        $crtfile = $this->getkeyfile();
        $fp = fopen($crtfile, "r"); 
        $priv_key = fread($fp, 8192); 
        fclose($fp); 
        $pkeyid = openssl_get_privatekey($priv_key);

        // compute signature
        openssl_sign($sign, $signMsg, $pkeyid,OPENSSL_ALGO_SHA1);

        // free the key from memory
        openssl_free_key($pkeyid);

        $MAC = base64_encode($signMsg);

        return $MAC;

    }
    /*
    * 读取公钥文件
    */
    private function getcrtfile($os='dji')
    {
        if ($os == 'airmap') {
          return  dirname(__FILE__)."/../config/airmap_ssl.crt";  //正式上线时，打开
        }

        return dirname(__FILE__)."/../config/dji_ssl.crt";
        //ssl.crt
    }
    /*
    * 读取私钥文件
    */
    private function getkeyfile()
    {
        return dirname(__FILE__)."/../config/dji_ssl.key";
        //ssl.crt
    }

     /**
     * 是否是电子邮件地址
     *
     * @param mixed $value
     *
     * @return boolean
     */
    public function validate_is_email($value)
    {
        //return preg_match('/^[a-z0-9]+[._\-\+]*@([a-z0-9]+[-a-z0-9]*\.)+[a-z0-9]+$/i', $value);
        return preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $value);
    }
    /*
    * 对于非文本参数的过滤
    */
    public function param_str_replace($param,$locale){
    	$tmp_gpc = 0;
    	if(!get_magic_quotes_gpc())  
         {  
             $tmp_gpc = 1;
         }
       
        foreach($param as $pk=>$pv){
            $param[$pk] = str_replace("'","",$pv);  
            $param[$pk] = str_replace("%","",$param[$pk]);  
            if(stristr($pv, '"') !== FALSE || stristr($pv, '%') !== FALSE || stristr($pv, "'") !== FALSE ) {
               $status = 441;
               $msg = $this->get_locale_msg($locale,$status);
			   return  array('success' => true,'status' => $status,'data' => array(),'extra' => array('msg'=> $pk.$msg.$pv));
			}

			if($tmp_gpc == 1)  
            {  
              $param[$pk] = addslashes($param[$pk]);  
            }
			

        }
        return array('success' => true,'status' => 200);    
    }
    /*
    *  返回对应的错误信息
    *
    */
    public function get_locale_msg($locale,$status)
    {
       if ($locale == 'cn') {
       	    switch ($status)
			{
				case 441:
				  $msg = " 格式不对.";
				  break;  
				case 401:
				  $msg = " 参数为空.";
				  break;
				case 410:
				  $msg = "账号格式不对.";
				  break;
				case 400:
				  $msg = "签名不对.";
				  break;
				case 402:
				  $msg = "请10秒后再请求.";
				  break;
				case 404:
				  $msg = "SN为空,请先添加SN码.";
				  break;
                case 405:
                  $msg = "用户未验证通过.";
                  break;
				default:
				  $msg = "系统繁忙.";
			}
       }else{
            switch ($status)
			{
				case 441:
				  $msg = " is not righit.";
				  break;  
				case 401:
				   $msg = "param is empty.";
				  break;
				case 410:
				   $msg = "account is not righit.";
				  break;
				case 400:
				   $msg = "signature is not righit.";
				  break;
				case 402:
				   $msg = "Can not be repeated within 10 seconds.";
				  break;
				case 404:
				   $msg = "SN is empty.";
				  break;
                case 405:
                   $msg = "account is not agreed.";
                  break;
				default:
				  $msg = "System busy.";
			}
       }
       return $msg;

    }
    /**
    *  增加申请解禁用户的数据
    *  account dji邮箱
    *  name 姓名
    *  date 用户签署时间 可以为空
    *  agreed 0:不同意;1:同意
    *  company 公司名称
    *  title 标题
    *  address 地址
    *  error_code 错误代码标示,用户校验不通过的原因 可以为空
    *  error_message 错误代码标示解释可以为空
    *  time 当前时间戳（1433223998） 10位
    *  ext1 扩展
    *  ext2 扩展
    *  token 用户认证token
    *  os 系统 ios,android,default,airmap
    *  appVersion app版本号
    *  version 接口版本 默认1.0,赵建或者app使用1.1;如果os为airmap，必须使用1.3
    *
    *  signature  签名 如果为airmap，使用post方式
    *  
    *  return {"success":true,"status":200,"account":"x.3434343@gmail.com","agreed":"1","time":1445409727,"signature":"mxpGLdwgKOoZorhP/E1wXIAG6p1Jx/7Hp51csJvFD5+9OoAWohfuVyCzy3rkayB5T3OG9DmzOVDl1VfRb1zKcpgU6wooaECzQ84IE8gWq5RYliECA2o5ORCw5GSFk54gHmmJg2266FWb+2ts1MXJvGY4cMnr2NjVKq5vX6hO7EavZNffwrqABTaXehvpgGoZ1DaWZ5IbDV4OSLbyrtE5XLVYCyFirakZD7sexOpY35q+NcImpUBLf1lvESsMvXMMLy4YpR3Erk5L9Ky/ZowMY3PZGB2NkicfV/euL362v0AHd2589Usq8iaSlCXhQN3jyrcXPnobL2+v4Dup6c7b2Q==","data":[{"id":"4"}],"extra":{"msg":"","reset":1}}
    *
    *    http://dev.flysafe-admin.dji.com/index.php?r=userarea/user&account=x.3434343@gmail.com&name=12-12321&date=2015-06-05&agreed=1&company=90&title=sfds&address=1321321&time=1433223998&token&os=ios&appVersion=3.0.1&version=1.1&signature=3207CEF681095FE219E72916183408FCFB3516F9
    *
    */
	public function actionUser()
	{
        $get_str = json_encode($_REQUEST);
        $this->add_log($get_str);
        $model = array();
		$model['account'] = Yii::$app->request->get('account', '');
		$model['name'] = Yii::$app->request->get('name', '');
		$model['date'] = Yii::$app->request->get('date', '');
		$model['agreed'] = Yii::$app->request->get('agreed', 0);
		$model['company'] = Yii::$app->request->get('company', '');
		$model['title'] = Yii::$app->request->get('title', '');
		$model['address'] = Yii::$app->request->get('address', '');
        $model['error_code'] = Yii::$app->request->get('error_code', '');
        $model['error_message'] = Yii::$app->request->get('error_message', '');
		$model['time'] = Yii::$app->request->get('time', '');
        $model['ext1'] = Yii::$app->request->get('ext1', '');
        $model['ext2'] = Yii::$app->request->get('ext2', '');
        $model['token'] = Yii::$app->request->get('token', '');
        $model['os'] = Yii::$app->request->get('os', '');
        $model['appVersion'] = Yii::$app->request->get('appVersion', '');
        $model['version'] = Yii::$app->request->get('version', '');
             
        $locale = Yii::$app->request->get('locale', 'en');
		$paramResult = $this->param_str_replace($model,$locale);
		if ($paramResult['status'] != 200) {
			return $paramResult;
		}
		$signature = Yii::$app->request->get('signature', '');
        $signature = empty($signature) ? Yii::$app->request->post('signature', '') : $signature;
		$nowtime = time();
        $account = $model['account'];
        $agreed = $model['agreed'] + 0;
		if (empty($signature) || empty($model['account']) ) {
			  $status = 401;
              $this->add_log($get_str."POST_signature=".Yii::$app->request->post('signature', '')."&signature=".$signature."status=".$status);
              $msg = $this->get_locale_msg($locale,$status);
              $return_signature = $this->gethmacsignature($model,$status.$account.$agreed.$nowtime);
        	  echo json_encode(array('success' => true,'status' => $status,'account' => $account,'agreed' => $agreed,'time'=>$nowtime,'signature' => $return_signature,'data' => array(),'extra' => array('msg'=>$msg)));
		      exit;
        }
        if (! $this->validate_is_email($model['account'])) {
        	$status = 410;
            $this->add_log($get_str."POST_signature=".Yii::$app->request->post('signature', '')."&signature=".$signature."status=".$status);
            $msg = $this->get_locale_msg($locale,$status);
            $return_signature = $this->gethmacsignature($model,$status.$account.$agreed.$nowtime);
        	echo json_encode(array('success' => true,'status' => $status,'account' => $account,'agreed' => $agreed,'time'=>$nowtime,'signature' => $return_signature,'data' => array(),'extra' => array('msg'=>$msg)));
        	exit;
        }	
        

        if ($model['os'] == 'airmap' && $model['version'] == '1.3') {
            $ok = $this->getsignatureverify($model,$signature);
            if ($ok != 1) {
                $status = 400;
                $this->add_log($get_str."POST_signature=".Yii::$app->request->post('signature', '')."&signature=".$signature."status=".$status);
                $msg = $this->get_locale_msg($locale,$status);
                $return_signature = $this->gethmacsignature($model,$status.$account.$agreed.$nowtime);
                echo json_encode(array('success' => true,'status' => $status,'account' => $account,'agreed' => $agreed,'time'=>$nowtime,'signature' => $return_signature,'data' => array(),'extra' => array('msg'=>$msg)));
                exit;
            }
            //特殊流程如果airmap，同时agreed不为1,只是记录不写入数据库
            if ($model['agreed'] == 0) {
                 $status = 200;
                                
                 $msg = '';
                 $agreed = $model['agreed'];
                 $return_signature = $this->gethmacsignature($model,$status.$account.$agreed.$nowtime);
                 $data = array('success' => true,'status' => $status,'account' => $account,'agreed'=> $agreed,'time'=>$nowtime,'signature' => $return_signature,'data' => array(),'extra' => array('msg'=>$msg));
                 $data = json_encode($data);
                 $this->add_log($get_str."&airmap_no_agreed=1&status=".$status."&data=".$data); 
                 echo $data;
                 exit;
            }


        }else{
            $signature_now = $this->getsignature($model); 
            //echo $signature_now ;
            if ($signature_now != $signature) {
                $status = 400;
                $msg = $this->get_locale_msg($locale,$status);
                $return_signature = $this->gethmacsignature($model,$status.$account.$agreed.$nowtime);
                echo json_encode(array('success' => true,'status' => $status,'account' => $account,'agreed'=> $agreed,'time'=>$nowtime,'signature' => $return_signature,'data' => array(),'extra' => array('msg'=>$msg)));
                exit;
            }

        }
        
        $luckkey = 'userarea_actionUser'.$model['account'].md5($signature);
        $luckdata = Yii::$app->cache->get($luckkey);
		if ( $luckdata ) {
			$status = 402;
            $this->add_log($get_str."POST_signature=".Yii::$app->request->post('signature', '')."&signature=".$signature."status=".$status);
            $msg = $this->get_locale_msg($locale,$status);
            $return_signature =  $this->gethmacsignature($model,$status.$account.$agreed.$nowtime);
        	echo json_encode(array('success' => true,'status' => $status,'account' => $account,'agreed'=> $agreed,'time'=>$nowtime,'signature' => $return_signature,'data' => array(),'extra' => array('msg'=>$msg)));
			exit;
		}
		Yii::$app->cache->set($luckkey, 1, 10);		
        $where = array();
        $where['account'] = $model['account'];
        $start =0;
        $data = UserUnlimit::getAndEqualWhere($where,$start,1);
        if ($data)
        {
            $status = 200;
            $return_signature = $this->gethmacsignature($model,$status.$account.$data[0]['agreed'].$nowtime);
            $data = array('success' => true,'status' => $status,'account' => $account,'agreed'=> $data[0]['agreed'],'time'=>$nowtime,'signature' => $return_signature,'data' => array(array('id'=>$data[0]['id'] )),'extra' => array('msg'=>'','reset'=>1));
            $this->add_log(json_encode($data)); 
            return $data;
        }
		$data = UserUnlimit::add($model);
		if ($data > 0) {
            $status = 200;
            $return_signature = $this->gethmacsignature($model,$status.$account.$agreed.$nowtime);
			$data = array('success' => true,'status' => $status,'account' => $account,'agreed'=> $agreed,'time'=>$nowtime,'signature' => $return_signature,'data' => array(array('id'=>$data)),'extra' => array('msg'=>''));
		    $this->add_log(json_encode($data)); 
        }else{
           $status = 500;
           $this->add_log($get_str."POST_signature=".Yii::$app->request->post('signature', '')."&signature=".$signature."status=".$status);
           $msg = $this->get_locale_msg($locale,$status);
           $return_signature = $this->gethmacsignature($model,$status.$account.$agreed.$nowtime);
           $data = array('success' => false,'status' => $status,'account' => $account,'agreed'=> $agreed,'time'=>$nowtime,'signature' => $return_signature,'data' => array(),'extra' => array('msg'=>$msg));
		}		
		return $data;
	}  

	/**
    *  查询用户是否已经签署协议
    *  account dji邮箱   
    *  longitude 经度
    *  latitude 纬度
    *  country 国家
    *  time 当前时间戳（1433223998） 10位
    *  token 用户认证token
    *  os 系统 ios,android default
    *  appVersion app版本号
    *  version 接口版本 默认1.0,赵建使用1.1
    *
    *  signature  签名
    *  
    *  返回内容：{"success":true,"status":200,"data":[{"id":"1","account":"11@dij.com","name":"12-12321","date":"2015-06-05 00:00:00","agreed":"0","company":"90","title":"sfds","address":"1321321","ext1":"","ext2":"","updated_at":"2015-08-14 08:21:02","create_at":"2015-07-28 10:31:26"}],"extra":{"msg":""}}
    *   http://dev.flysafe-admin.dji.com/index.php?r=userarea/getuser&account=11@dij.com&time=1433223998&token&os=ios&appVersion=2.3.1&version=1.1&signature=FECB4970699CCB5252711DA0640632E93C0C6606
    *
    */
	public function actionGetuser()
	{
        $get_str = json_encode($_REQUEST);
        $this->add_log($get_str,"userarea_getuser");
        //Yii::log('My log message.','info','cool.collectpd');
        $model = array();
		$model['account'] = Yii::$app->request->get('account', '');
        $model['longitude'] = Yii::$app->request->get('longitude', '');
        $model['latitude'] = Yii::$app->request->get('latitude', ''); 

        $model['country'] = Yii::$app->request->get('country', '');
		$model['time'] = Yii::$app->request->get('time', '');
        $model['token'] = Yii::$app->request->get('token', '');
        $model['os'] = Yii::$app->request->get('os', '');
        $model['appVersion'] = Yii::$app->request->get('appVersion', '');
        $model['version'] = Yii::$app->request->get('version', '');
        $country = Yii::$app->request->get('country', '');
		$locale = Yii::$app->request->get('locale', 'en');
		$paramResult = $this->param_str_replace($model,$locale);
		if ($paramResult['status'] != 200) {
			return $paramResult;
		}
        $nowtime = time();  
        $url = '';
        $type = '';
        $url_key = '';         
		$signature = Yii::$app->request->get('signature', '');
		if (empty($signature) || empty($model['account']) ) {
        	  $status = 401;
              $msg = $this->get_locale_msg($locale,$status);
              $return_signature = $this->gethmacsignature($model,$status.$country.$type.$url.$url_key.$nowtime);
        	  echo json_encode(array('success' => true,'status' => $status,'country'=>$country,'type'=>$type,'url'=>$url,'url_key'=>$url_key,'time'=>$nowtime,'signature' => $return_signature,'data' => array(),'extra' => array('msg'=>$msg)));
		      exit;
        }
        if (! $this->validate_is_email($model['account'])) {
        	$status = 410;
            $msg = $this->get_locale_msg($locale,$status);
            $return_signature = $this->gethmacsignature($model,$status.$country.$type.$url.$url_key.$nowtime);
        	echo json_encode(array('success' => true,'status' => $status,'country'=>$country,'type'=>$type,'url'=>$url,'url_key'=>$url_key,'time'=>$nowtime,'signature' => $return_signature,'data' => array(),'extra' => array('msg'=>$msg)));
		    exit;
        }
		
        $signature_now = $this->getsignature($model); 
        //echo $signature_now ;
        if ($signature_now != $signature) {
            $status = 400;
            $msg = $this->get_locale_msg($locale,$status);
            $return_signature = $this->gethmacsignature($model,$status.$country.$type.$url.$url_key.$nowtime);
            echo json_encode(array('success' => true,'status' => $status,'country'=>$country,'type'=>$type,'url'=>$url,'url_key'=>$url_key,'time'=>$nowtime,'signature' => $return_signature,'data' => array(),'extra' => array('msg'=>$msg)));
            exit;
        }
		$where = array();
		$where['account'] = $model['account'];
		$start =0;
        $data = UserUnlimit::getAndEqualWhere($where,$start,1);
		if ($data || $data === array()) {            
            $status = 200;
                   
            if ($data === array() && in_array($country, array('United States'))) {
               $url = 'https://verify.airmap.io/v1/user';
               $type = 'airmap';
               $url_key = 'AB405F38-15F1-B20C-32A8-4BDA509A5B73';
               $return_signature = $this->gethmacsignature($model,$status.$country.$type.$url.$url_key.$nowtime);
               $data = array('success' => true,'status' => $status,'country'=>$country,'type'=>$type,'url'=>$url,'url_key'=>$url_key,'time'=>$nowtime,'signature' => $return_signature,'data' => $data,'extra' => array('msg'=>'')); 
               $get_str = json_encode($data);
               $this->add_log($get_str,"userarea_getuser");
               return $data;
            }
            $return_signature = $this->gethmacsignature($model,$status.$country.$type.$url.$url_key.$nowtime);
			$data = array('success' => true,'status' => $status,'country'=>$country,'type'=>$type,'url'=>$url,'url_key'=>$url_key,'time'=>$nowtime,'signature' => $return_signature,'data' => $data,'extra' => array('msg'=>''));
		}else{           
           $status = 500;
           $msg = $this->get_locale_msg($locale,$status);
           $return_signature = $this->gethmacsignature($model,$status.$country.$type.$url.$url_key.$nowtime);
           $data = array('success' => false,'status' => $status,'country'=>$country,'type'=>$type,'url'=>$url,'url_key'=>$url_key,'time'=>$nowtime,'signature' => $return_signature,'data' => array(),'extra' => array('msg'=>$msg));
		}		
		return $data;
	}
    
    /*
    * 验证邮件是否已经通过认证
    * return true，false
    */
    public function agreed_is_email($account)
    {

        $where = array();
        $where['account'] = $account;
        $start =0;
        $data = UserUnlimit::getAndEqualWhere($where,$start,1);
        if ($data && $data[0]['agreed'] == "1" ) {
              return true;
        }else{
              return false;
          
        }
    }

    /**
    *  插入新解禁飞区域的数据
    *  account dji邮箱
    *  device_code SN1,SN2
    *  lat 经度
    *  lng 纬度
    *  radius 半径
    *  height 高度
    *  begin_at 开始时间戳（1433223998） 10位
    *  end_at 结束时间戳（1433223998） 10位
    *  country 国家
    *  city 城市
    *  time 当前时间戳（1433223998） 10位
    *  os 系统 ios,android default
    *  appVersion app版本号
    *  version 接口版本 默认1.0,赵建使用1.1
    *
    *  signature  签名
    *  
    *  return {"success":true,"status":200,"extra":{"id":8}}
    *   http://dev.flysafe-admin.dji.com/index.php?r=userarea/insert&account=11@dij.com&device_code=12-12321&lat=12.2132&lng=123.23223&radius=100&height=90&begin_at=1433223998&end_at=1433223998&country=cn&city=beijing&time=1433223998&version=1.1&signature=219AA995D840D990F51136B4808199FE4DF0073B
    *
    */
	public function actionInsert()
	{
		$model = array();
		$model['account'] = Yii::$app->request->get('account', '');
		$model['device_code'] = Yii::$app->request->get('device_code', '');
		$model['lat'] = Yii::$app->request->get('lat', '');
		$model['lng'] = Yii::$app->request->get('lng', '');
		$model['radius'] = Yii::$app->request->get('radius', '');
		$model['height'] = Yii::$app->request->get('height', '');
		$model['begin_at'] = Yii::$app->request->get('begin_at', '');
		$model['end_at'] = Yii::$app->request->get('end_at', '');
		$model['country'] = Yii::$app->request->get('country', '');
		$model['city'] = Yii::$app->request->get('city', '');
		$model['time'] = Yii::$app->request->get('time', '');
        $model['os'] = Yii::$app->request->get('os', '');
        $model['appVersion'] = Yii::$app->request->get('appVersion', '');
        $model['version'] = Yii::$app->request->get('version', '');

		$locale = Yii::$app->request->get('locale', 'en');
		$paramResult = $this->param_str_replace($model,$locale);
		if ($paramResult['status'] != 200) {
			return $paramResult;
		}
        $nowtime = time();		
		//echo time();exit;
		$signature = Yii::$app->request->get('signature', '');
		if (empty($signature) || empty($model['account']) || empty($model['lat']) || empty($model['lng']) || empty($model['radius']) || empty($model['begin_at']) || empty($model['end_at'])
			 || empty($model['country']) || empty($model['city']) ) {
        	  $status = 401;
              $msg = $this->get_locale_msg($locale,$status);
              $return_signature = $this->gethmacsignature($model,$status.$nowtime);
        	  echo json_encode(array('success' => true,'status' => $status,'time'=>$nowtime,'signature' => $return_signature,'data' => array(),'extra' => array('msg'=>$msg)));
		      exit;
        }
        if (! $this->validate_is_email($model['account'])) {
        	$status = 410;
            $msg = $this->get_locale_msg($locale,$status);
            $return_signature = $this->gethmacsignature($model,$status.$nowtime);
        	echo json_encode(array('success' => true,'status' => $status,'time'=>$nowtime,'signature' => $return_signature,'data' => array(),'extra' => array('msg'=>$msg)));
		    exit;
        }		
        $signature_now = $this->getsignature($model); 
        //echo $signature_now ;
        if ($signature_now != $signature) {
            $status = 400;
            $msg = $this->get_locale_msg($locale,$status);
            $return_signature = $this->gethmacsignature($model,$status.$nowtime);
            echo json_encode(array('success' => true,'status' => $status,'time'=>$nowtime,'signature' => $return_signature,'data' => array(),'extra' => array('msg'=>$msg)));
            exit;
        }

        if (! $this->agreed_is_email($model['account'])) {
             $status = 405;
             $msg = $this->get_locale_msg($locale,$status);
             $return_signature = $this->gethmacsignature($model,$status.$nowtime);
             echo json_encode(array('success' => true,'status' => $status,'time'=>$nowtime,'signature' => $return_signature,'data' => array(),'extra' => array('msg'=>$msg)));
             exit;
        }
        //1433223998
		$model['begin_at'] = date('Y-m-d H:i:s',$model['begin_at'] +0);
		$model['end_at'] = date('Y-m-d H:i:s',$model['end_at'] +0);
		
        $luckkey = 'userarea_insert'.$model['account'].$signature_now;
        $luckdata = Yii::$app->cache->get($luckkey);
		if ( $luckdata ) {
			$status = 402;
            $msg = $this->get_locale_msg($locale,$status);
            $return_signature = $this->gethmacsignature($model,$status.$nowtime);
        	echo json_encode(array('success' => true,'status' => $status,'time'=>$nowtime,'signature' => $return_signature,'data' => array(),'extra' => array('msg'=>$msg)));
		    exit;
		}
		Yii::$app->cache->set($luckkey, 1, 10);

        $where = $model;        
        unset($where['time']);       
        unset($where['os']);
        unset($where['appVersion']);
        unset($where['version']);
        $start =0;
        $data = UserArea::getAndEqualWhere($where,$start,1);
        //var_dump($data);exit;
        if ($data)
        {
            $status = 200;
            $return_signature = $this->gethmacsignature($model,$status.$nowtime);
            $data = array('success' => true,'status' => $status,'time'=>$nowtime,'signature' => $return_signature,'data' => array(array('id'=>$data[0]['id'] )),'extra' => array('msg'=>'','reset'=>1));
            return $data;
        }		
		$data = UserArea::add($model);
		if ($data > 0) {
            $status = 200;
            $return_signature = $this->gethmacsignature($model,$status.$nowtime);
			$data = array('success' => true,'status' => $status,'time'=>$nowtime,'signature' => $return_signature,'data' => array(array('id'=>$data)),'extra' => array('msg'=>''));
		}else{
           $status = 500;
           $return_signature = $this->gethmacsignature($model,$status.$nowtime);
           $msg = $this->get_locale_msg($locale,$status);
           $data = array('success' => false,'status' => $status,'time'=>$nowtime,'signature' => $return_signature,'data' => array(),'extra' => array('msg'=>$msg));
		}
		
		return $data;
	}

	/**
    *  删除解禁飞区域的数据
    *  id  解禁飞区域的数据表对应的ID
    *  account dji邮箱   
    *  time 当前时间戳（1433223998） 10位
    *  os 系统 ios,android default
    *  appVersion app版本号
    *  version 接口版本 默认1.0,赵建使用1.1
    *
    *  signature  签名
    *  
    *   return {"success":true,"status":200,"extra":{"id":8}}
    * http://10.11.0.188:6688/index.php?r=userarea/cancel&id=3&account=11@dij.com&time=1433223998&signature=58D56A02F957EFFE60F6   
    *
    */
	public function actionCancel()
	{
		$model = array();
			
		$model['id'] = Yii::$app->request->get('id', '');
		$model['account'] = Yii::$app->request->get('account', '');
		$model['time'] = Yii::$app->request->get('time', '');
        $model['os'] = Yii::$app->request->get('os', '');
        $model['appVersion'] = Yii::$app->request->get('appVersion', '');
        $model['version'] = Yii::$app->request->get('version', '');

		$locale = Yii::$app->request->get('locale', 'en');
		$paramResult = $this->param_str_replace($model,$locale);
		if ($paramResult['status'] != 200) {
			return $paramResult;
		}
		
		

		$signature = Yii::$app->request->get('signature', '');
		if (empty($signature) || empty($model['account']) || empty($model['id'])  ) {
        	  $status = 401;
              $msg = $this->get_locale_msg($locale,$status);
        	  echo json_encode(array('success' => true,'status' => $status,'data' => array(),'extra' => array('msg'=>$msg)));
		      exit;
        }
        if (! $this->validate_is_email($model['account'])) {
        	$status = 410;
            $msg = $this->get_locale_msg($locale,$status);
        	echo json_encode(array('success' => true,'status' => $status,'data' => array(),'extra' => array('msg'=>$msg)));
		    exit;
        }
        $signature_now = $this->getsignature($model); 
        //echo $signature_now ;
        if ($signature_now != $signature) {
            $status = 400;
            $msg = $this->get_locale_msg($locale,$status);
            echo json_encode(array('success' => true,'status' => $status,'data' => array(),'extra' => array('msg'=>$msg)));
            exit;
        }
        if (! $this->agreed_is_email($model['account'])) {
             $status = 405;
             $msg = $this->get_locale_msg($locale,$status);
             echo json_encode(array('success' => true,'status' => $status,'data' => array(),'extra' => array('msg'=>$msg)));
             exit;
        }

        //echo time();exit;
        $model['status'] = 'cancel';
      
		
        $luckkey = 'userarea_cancel'.$model['account'].$signature_now;
        $luckdata = Yii::$app->cache->get($luckkey);
		if ( $luckdata ) {
			$status = 402;
            $msg = $this->get_locale_msg($locale,$status);
        	echo json_encode(array('success' => true,'status' => $status,'data' => array(),'extra' => array('msg'=>$msg)));
		    exit;
		}
		Yii::$app->cache->set($luckkey, 1, 10);
		
		$data = UserArea::updateInfoStatus($model);
		if ($data > 0) {
			$data = array('success' => true,'status' => 200,'data' => array(array('id'=>$data)),'extra' => array('msg'=>''));
		}else{
           $status = 500;
           $msg = $this->get_locale_msg($locale,$status);
           $data = array('success' => false,'status' => $status,'data' => array(),'extra' => array('msg'=>$msg));
		}
		
		return $data;
	}

	/**
    *  查询解禁飞区域的数据列表
    *  account dji邮箱
    *  page 第几页
    *  size 每页个数
    *  time 当前时间戳（1433223998） 10位
    *  os 系统 ios,android default
    *  appVersion app版本号
    *  version 接口版本 默认1.0,赵建使用1.1
    *
    *  signature  签名
    *  
    *   return {"success":true,"status":200,"data":[{"id":"14","account":"11@dij.com","device_code":"12-12321","lat":"12.213200","lng":"123.232230","radius":"100","height":"90","begin_at":"2015-06-02 13:46:38","end_at":"2015-06-02 13:46:38","country":"cn","city":"beijing","title":"","level":"0","updated_at":"2015-06-02 14:50:44","create_at":"2015-06-02 14:50:44","status":null},
    *         {"id":"13","account":"11@dij.com","device_code":"12-12321","lat":"12.213200","lng":"123.232230","radius":"100","height":"90","begin_at":"2015-06-02 13:46:38","end_at":"2015-06-02 13:46:38","country":"cn","city":"beijing","title":"","level":"0","updated_at":"2015-06-02 14:25:36","create_at":"2015-06-02 14:25:36","status":null}],"extra":{"msg":"","count":"12","page":"1"}}
    * http://10.11.0.188:6688/index.php?r=userarea/getarea&account=11@dij.com&page=1&size=2&time=1433223998&signature=9FE941140114602E94FC
    * 
    */
	public function actionGetarea()
	{
		$model = array();
		$model['account'] = Yii::$app->request->get('account', '');
		$page = Yii::$app->request->get('page', 1) +0 ;
		$size = Yii::$app->request->get('size', 20) + 0;
		
		$model['page'] = $page;
		$model['size'] = $size;
		$model['time'] = Yii::$app->request->get('time', '');
        $model['os'] = Yii::$app->request->get('os', '');
        $model['appVersion'] = Yii::$app->request->get('appVersion', '');
        $model['version'] = Yii::$app->request->get('version', '');

		$locale = Yii::$app->request->get('locale', 'en');
		$paramResult = $this->param_str_replace($model,$locale);
		if ($paramResult['status'] != 200) {
			return $paramResult;
		}
		
        
		$signature = Yii::$app->request->get('signature', '');
		if (empty($signature) || empty($model['account']) ) {
        	  $status = 401;
              $msg = $this->get_locale_msg($locale,$status);
        	  echo json_encode(array('success' => true,'status' => $status,'data' => array(),'extra' => array('msg'=>$msg)));
		      exit;
        }
        if (! $this->validate_is_email($model['account'])) {
        	$status = 410;
            $msg = $this->get_locale_msg($locale,$status);
        	echo json_encode(array('success' => true,'status' => $status,'data' => array(),'extra' => array('msg'=>$msg)));
		    exit;
        }	
        $signature_now = $this->getsignature($model); 
        //echo $signature_now ;
        if ($signature_now != $signature) {
            $status = 400;
            $msg = $this->get_locale_msg($locale,$status);
            echo json_encode(array('success' => true,'status' => $status,'data' => array(),'extra' => array('msg'=>$msg)));
            exit;
        }
        if (! $this->agreed_is_email($model['account'])) {
             $status = 405;
             $msg = $this->get_locale_msg($locale,$status);
             echo json_encode(array('success' => true,'status' => $status,'data' => array(),'extra' => array('msg'=>$msg)));
             exit;
        }

		$where = array();
		$where['account'] = $model['account'];
		$where['status'] = 'success';
		$start = ($page - 1) * $size;

		$count = UserArea::getWhereCount($where);
		if ($count  > 0 ) {
			$data = UserArea::getAndEqualWhere($where,$start,$size);
		}else{
			echo json_encode(array('success' => true,'status' => 200,'data' => array(),'extra' => array('msg'=>'','count' => $count,'page' => $page)));
		    exit;
		}

		if ($data) {
			$data = array('success' => true,'status' => 200,'data' => $data,'extra' => array('msg'=>'','count' => $count,'page' => $page));
		}else{
		   $status = 500;
           $msg = $this->get_locale_msg($locale,$status);
           $data = array('success' => false,'status' => 500,'data' => array(),'extra' => array('msg'=>$msg,'count' => $count,'page' => $page));
		}
		
		return $data;
	}

	/**
    *  设置SN码的接口
    *  account dji邮箱
    *  id  区域id
    *  device_code  SN1,SN2
    *  time 当前时间戳（1433223998） 10位
    *  os 系统 ios,android default
    *  appVersion app版本号
    *  version 接口版本 默认1.0,赵建使用1.1
    *
    *  signature  签名
    *  
    *   return {"success":true,"status":200,"data":[{"id":11}],"extra":{"msg":""}}
    * http://10.11.0.188:6688/index.php?r=userarea/setdevicecode&account=11@dij.com&id=11&device_code=p76dcd07010958,p76dcd07010953&time=1433223998&signature=0B716FAB4C9B756C947F
    * 
    */
	public function actionSetdevicecode()
	{
		$model = array();
		$model['account'] = Yii::$app->request->get('account', '');
		$model['id'] = Yii::$app->request->get('id', '');
		$model['device_code'] = Yii::$app->request->get('device_code', '');
				
		$model['time'] = Yii::$app->request->get('time', '');
        $model['os'] = Yii::$app->request->get('os', '');
        $model['appVersion'] = Yii::$app->request->get('appVersion', '');
        $model['version'] = Yii::$app->request->get('version', '');

		$locale = Yii::$app->request->get('locale', 'en');
		$paramResult = $this->param_str_replace($model,$locale);
		if ($paramResult['status'] != 200) {
			return $paramResult;
		}		
        
		$signature = Yii::$app->request->get('signature', '');
		if (empty($signature) || empty($model['account']) || empty($model['id']) || empty($model['device_code']) ) {
        	  $status = 401;
              $msg = $this->get_locale_msg($locale,$status);
        	  echo json_encode(array('success' => true,'status' => $status,'data' => array(),'extra' => array('msg'=>$msg)));
		      exit;
        }
        if (! $this->validate_is_email($model['account'])) {
        	$status = 410;
            $msg = $this->get_locale_msg($locale,$status);
        	echo json_encode(array('success' => true,'status' => $status,'data' => array(),'extra' => array('msg'=>$msg)));
		    exit;
        }		
        $signature_now = $this->getsignature($model); 
        //echo $signature_now ;
        if ($signature_now != $signature) {
            $status = 400;
            $msg = $this->get_locale_msg($locale,$status);
            echo json_encode(array('success' => true,'status' => $status,'data' => array(),'extra' => array('msg'=>$msg)));
            exit;
        }
        if (! $this->agreed_is_email($model['account'])) {
             $status = 405;
             $msg = $this->get_locale_msg($locale,$status);
             echo json_encode(array('success' => true,'status' => $status,'data' => array(),'extra' => array('msg'=>$msg)));
             exit;
        }

        $luckkey = 'userarea_setdevicecode'.$model['account'].$signature_now;
        $luckdata = Yii::$app->cache->get($luckkey);
		if ( $luckdata ) {
			$status = 402;
            $msg = $this->get_locale_msg($locale,$status);
        	echo json_encode(array('success' => true,'status' => $status,'data' => array(),'extra' => array('msg'=>$msg)));
		    exit;
		}
		Yii::$app->cache->set($luckkey, 1, 10);

		$data = UserArea::updateDeviceCode($model);
		
        if ($data > 0) {
			$data = array('success' => true,'status' => 200,'data' => array(array('id'=>$data)),'extra' => array('msg'=>''));
		}else if($data == -1){
            $data = array('success' => true,'status' => 200,'data' => array(),'extra' => array('msg'=>'id and device_code is not find'));

		}else{

           $status = 500;
           $msg = $this->get_locale_msg($locale,$status);
           $data = array('success' => false,'status' => $status,'data' => array(),'extra' => array('msg'=>$msg));
		}
		
		return $data;
	}

	/**
    *  设置生成license文件的接口
    *  account dji邮箱
    *  areaid_code  区域id1;区域id2
    *  name  license对应的名称
    *  time 当前时间戳（1433223998） 10位
    *  os 系统 ios,android default
    *  appVersion app版本号
    *  version 接口版本 默认1.0,赵建使用1.1
    *
    *  signature  签名
    *  
    *  return {"success":true,"status":200,"data":[{"id":11}],"extra":{"msg":""}}
    * http://10.11.0.188:6688/index.php?r=userarea/setlicense&account=11@dij.com&areaid_code=11;12&name=dsfsd&time=1433223998&signature=8FAAEE2CA8AFD9C5D8CC
    * 
    */
	public function actionSetlicense()
	{
		$model = array();
		$model['account'] = Yii::$app->request->get('account', '');
		$model['areaid_code'] = Yii::$app->request->get('areaid_code', '');
		$model['name'] = Yii::$app->request->get('name', '');
		
		$model['time'] = Yii::$app->request->get('time', '');
        $model['os'] = Yii::$app->request->get('os', '');
        $model['appVersion'] = Yii::$app->request->get('appVersion', '');
        $model['version'] = Yii::$app->request->get('version', '');

		$locale = Yii::$app->request->get('locale', 'en');
		$paramResult = $this->param_str_replace($model,$locale);
		if ($paramResult['status'] != 200) {
			return $paramResult;
		}		
       
		$signature = Yii::$app->request->get('signature', '');
		if (empty($signature) || empty($model['account']) || empty($model['areaid_code']) ) {
        	  $status = 401;
              $msg = $this->get_locale_msg($locale,$status);
        	  echo json_encode(array('success' => true,'status' => $status,'data' => array(),'extra' => array('msg'=>$msg)));
		      exit;
        }
        if (! $this->validate_is_email($model['account'])) {
        	$status = 410;
            $msg = $this->get_locale_msg($locale,$status);
        	echo json_encode(array('success' => true,'status' => $status,'data' => array(),'extra' => array('msg'=>$msg)));
		    exit;
        }       
        $signature_now = $this->getsignature($model); 
        //echo $signature_now ;
        if ($signature_now != $signature) {
            $status = 400;
            $msg = $this->get_locale_msg($locale,$status);
            echo json_encode(array('success' => true,'status' => $status,'data' => array(),'extra' => array('msg'=>$msg)));
            exit;
        }
        if (! $this->agreed_is_email($model['account'])) {
             $status = 405;
             $msg = $this->get_locale_msg($locale,$status);
             echo json_encode(array('success' => true,'status' => $status,'data' => array(),'extra' => array('msg'=>$msg)));
             exit;
        }

         $luckkey = 'userarea_setlicense'.$model['account'].$signature_now;
        $luckdata = Yii::$app->cache->get($luckkey);
		if ( $luckdata ) {
			$status = 402;
            $msg = $this->get_locale_msg($locale,$status);
        	echo json_encode(array('success' => true,'status' => $status,'data' => array(),'extra' => array('msg'=>$msg)));
		    exit;
		}
		Yii::$app->cache->set($luckkey, 1, 10);
		
        $areaid_code_array = explode(';',$model['areaid_code']);
        $LicenseKey = time();
        $run_result = false;
        foreach ($areaid_code_array as $key => $value) {
        	 if(stristr($value, ':') === FALSE)
        	 {
        	 	$value += 0;
                $tmpcode = UserArea::getAndEqualWhere(array('id' => $value));
               
                if ($tmpcode && $tmpcode['0']['device_code']) {
                	$tmpmodel = array();
	                $tmpmodel['account'] = $model['account'];
	                $tmpmodel['device_code'] = $tmpcode['0']['device_code'];
	                $tmpmodel['user_areas_id'] = $value;
	                $tmpmodel['key'] = $LicenseKey;
	                $tmpResult = UserAreasLicense::add($tmpmodel);
	                if ($tmpResult > 0) {
	                	$run_result = true;
	                } 
                }else{
                	$status = 404;
                    $msg = $this->get_locale_msg($locale,$status);
        	        echo json_encode(array('success' => true,'status' => $status,'data' => array(),'extra' => array('msg'=>$msg)));
		            exit;
                }
        	 }else{
        	 	  $tmp_array = explode(':',$value);
        	 	  if ($tmp_array && $tmp_array[0] && $tmp_array[1]) {
        	 	  	$tmpmodel = array();
	                $tmpmodel['account'] = $model['account'];
	                $tmpmodel['device_code'] = $tmp_array[1];
	                $tmpmodel['user_areas_id'] =$tmp_array[0];
	                $tmpmodel['key'] = $LicenseKey;
	                $tmpResult = UserAreasLicense::add($tmpmodel);
	                if ($tmpResult > 0) {
	                	$run_result = true;
	                }        	 	  	  
        	 	  }
        	 }
        }        
		//$data = UserArea::updateDeviceCode($model);		
        if ($run_result) {
        	    $tmpmodel = array();
	            $tmpmodel['account'] = $model['account'];
	            $tmpmodel['key'] = $LicenseKey;
	            $tmpmodel['name'] = $model['name'];
	            $tmpResult = UserLicense::add($tmpmodel);
	            if ($tmpResult > 0) {
	               $data = array('success' => true,'status' => 200,'data' => array(array('id'=>$tmpResult)),'extra' => array('msg'=>'','key'=>$LicenseKey));
	            }else{

                  $status = 500;
                  $msg = $this->get_locale_msg($locale,$status);
                  $data = array('success' => false,'status' => $status,'data' => array(),'extra' => array('msg'=>$msg));
		       }			
		}else if($data == -1){
            $data = array('success' => true,'status' => 200,'data' => array(),'extra' => array('msg'=>'id and device_code is not find'));
		}else{
           $status = 500;
           $msg = $this->get_locale_msg($locale,$status);
           $data = array('success' => false,'status' => $status,'data' => array(),'extra' => array('msg'=>$msg));
		}		
		return $data;
	}
	/**
    *  查询解禁飞区域的数据列表
    *  account dji邮箱
    *  page 第几页
    *  size 每页个数
    *  time 当前时间戳（1433223998） 10位
    *  os 系统 ios,android default
    *  appVersion app版本号
    *  version 接口版本 默认1.0,赵建使用1.1
    *
    *  signature  签名
    *  
    *   return {"success":true,"status":200,"data":[{"id":"14","account":"11@dij.com","device_code":"12-12321","lat":"12.213200","lng":"123.232230","radius":"100","height":"90","begin_at":"2015-06-02 13:46:38","end_at":"2015-06-02 13:46:38","country":"cn","city":"beijing","title":"","level":"0","updated_at":"2015-06-02 14:50:44","create_at":"2015-06-02 14:50:44","status":null},
    *         {"id":"13","account":"11@dij.com","device_code":"12-12321","lat":"12.213200","lng":"123.232230","radius":"100","height":"90","begin_at":"2015-06-02 13:46:38","end_at":"2015-06-02 13:46:38","country":"cn","city":"beijing","title":"","level":"0","updated_at":"2015-06-02 14:25:36","create_at":"2015-06-02 14:25:36","status":null}],"extra":{"msg":"","count":"12","page":"1"}}
    * 10.11.0.188:6688/index.php?r=userarea/getlicense&account=11@dij.com&page=1&size=2&time=1433223998&signature=9FE941140114602E94FC
    * 
    */
	public function actionGetlicense()
	{
		$model = array();
		$model['account'] = Yii::$app->request->get('account', '');
		$page = Yii::$app->request->get('page', 1) +0 ;
		$size = Yii::$app->request->get('size', 20) + 0;
		
		$model['page'] = $page;
		$model['size'] = $size;
		$model['time'] = Yii::$app->request->get('time', '');
        $model['os'] = Yii::$app->request->get('os', '');
        $model['appVersion'] = Yii::$app->request->get('appVersion', '');
        $model['version'] = Yii::$app->request->get('version', '');

		$locale = Yii::$app->request->get('locale', 'en');
		$paramResult = $this->param_str_replace($model,$locale);
		if ($paramResult['status'] != 200) {
			return $paramResult;
		}		
       
		$signature = Yii::$app->request->get('signature', '');
		if (empty($signature) || empty($model['account']) ) {
        	  $status = 401;
              $msg = $this->get_locale_msg($locale,$status);
        	  echo json_encode(array('success' => true,'status' => $status,'data' => array(),'extra' => array('msg'=>$msg)));
		      exit;
        }
        if (! $this->validate_is_email($model['account'])) {
        	$status = 410;
            $msg = $this->get_locale_msg($locale,$status);
        	echo json_encode(array('success' => true,'status' => $status,'data' => array(),'extra' => array('msg'=>$msg)));
		    exit;
        }
        $signature_now = $this->getsignature($model); 
        //echo $signature_now ;
        if ($signature_now != $signature) {
            $status = 400;
            $msg = $this->get_locale_msg($locale,$status);
            echo json_encode(array('success' => true,'status' => $status,'data' => array(),'extra' => array('msg'=>$msg)));
            exit;
        }
        if (! $this->agreed_is_email($model['account'])) {
             $status = 405;
             $msg = $this->get_locale_msg($locale,$status);
             echo json_encode(array('success' => true,'status' => $status,'data' => array(),'extra' => array('msg'=>$msg)));
             exit;
        }
		$where = array();
		$where['account'] = $model['account'];
        $where['disable'] = 1;
		$start = ($page - 1) * $size;

		$count = UserLicense::getWhereCount($model['account']);
		if ($count  > 0 ) {
			$data = UserLicense::getAndEqualWhere($where,$start,$size);

		}else{
			echo json_encode(array('success' => true,'status' => 200,'data' => array(),'extra' => array('msg'=>'','count' => $count,'page' => $page)));
		    exit;
		}
		if ($data) {
			foreach ($data as $key => $value) {
				 if ($value && $value['key'] && $value['account']) {
				 	 //$tmpArealist = UserAreasLicense::
				 	$primaryConnection = \Yii::$app->db;
				 	$connection = new \yii\db\Connection([
					    'dsn' => $primaryConnection->dsn,
					     'username' => $primaryConnection->username,
					     'password' => $primaryConnection->password,
					]);
					$connection->open();

				 //	$connection = new \yii\db\Query();
					$sql = "SELECT ual.device_code,ua.lat,ua.lng,ua.radius,ua.height,ua.begin_at,ua.end_at,ua.country,ua.city FROM `user_areas_license_unlimit` as ual, user_areas_unlimit as ua where  ual.user_areas_id = ua.id  and  ual.`key` = '".$value['key'] ."'ORDER BY ual.id";
					$command = $connection->createCommand($sql);
					$result = $command->queryAll();
					$data[$key]['area_list'] = $result;
				 }
			}
			$data = array('success' => true,'status' => 200,'data' => $data,'extra' => array('msg'=>'','count' => $count,'page' => $page));
		}else{
		   $status = 500;
           $msg = $this->get_locale_msg($locale,$status);
           $data = array('success' => false,'status' => 500,'data' => array(),'extra' => array('msg'=>$msg,'count' => $count,'page' => $page));
		}
		
		return $data;
	}
    /**
    *  删除license的数据
    *  id  license数据表对应的ID
    *  account dji邮箱   
    *  time 当前时间戳（1433223998） 10位
    *  os 系统 ios,android default
    *  appVersion app版本号
    *  version 接口版本 默认1.0,赵建使用1.1
    *
    *  signature  签名
    *  
    *   return {"success":true,"status":200,"extra":{"id":8}}
    * http://10.11.0.188:6688/index.php?r=userarea/cancellicense&id=3&account=11@dij.com&time=1433223998&signature=58D56A02F957EFFE60F6   
    *
    */
    public function actionCancellicense()
    {
        $model = array();
            
        $model['id'] = Yii::$app->request->get('id', '');
        $model['account'] = Yii::$app->request->get('account', '');
        $model['time'] = Yii::$app->request->get('time', '');
        $model['os'] = Yii::$app->request->get('os', '');
        $model['appVersion'] = Yii::$app->request->get('appVersion', '');
        $model['version'] = Yii::$app->request->get('version', '');

        $locale = Yii::$app->request->get('locale', 'en');
        $paramResult = $this->param_str_replace($model,$locale);
        if ($paramResult['status'] != 200) {
            return $paramResult;
        }
        $signature = Yii::$app->request->get('signature', '');
        if (empty($signature) || empty($model['account']) || empty($model['id'])  ) {
              $status = 401;
              $msg = $this->get_locale_msg($locale,$status);
              echo json_encode(array('success' => true,'status' => $status,'data' => array(),'extra' => array('msg'=>$msg)));
              exit;
        }
        if (! $this->validate_is_email($model['account'])) {
            $status = 410;
            $msg = $this->get_locale_msg($locale,$status);
            echo json_encode(array('success' => true,'status' => $status,'data' => array(),'extra' => array('msg'=>$msg)));
            exit;
        }
        $signature_now = $this->getsignature($model); 
        //echo $signature_now ;
        if ($signature_now != $signature) {
            $status = 400;
            $msg = $this->get_locale_msg($locale,$status);
            echo json_encode(array('success' => true,'status' => $status,'data' => array(),'extra' => array('msg'=>$msg)));
            exit;
        }
        if (! $this->agreed_is_email($model['account'])) {
             $status = 405;
             $msg = $this->get_locale_msg($locale,$status);
             echo json_encode(array('success' => true,'status' => $status,'data' => array(),'extra' => array('msg'=>$msg)));
             exit;
        }
        //echo time();exit;
        $model['disable'] = 0;        
        $luckkey = 'userarea_cancellicense'.$model['account'].$signature_now;
        $luckdata = Yii::$app->cache->get($luckkey);
        if ( $luckdata ) {
            $status = 402;
            $msg = $this->get_locale_msg($locale,$status);
            echo json_encode(array('success' => true,'status' => $status,'data' => array(),'extra' => array('msg'=>$msg)));
            exit;
        }
        Yii::$app->cache->set($luckkey, 1, 10);
        
        $data = UserLicense::updateInfoDisable($model);
        if ($data > 0) {
            $data = array('success' => true,'status' => 200,'data' => array(array('id'=>$data)),'extra' => array('msg'=>''));
        }else{
           $status = 500;
           $msg = $this->get_locale_msg($locale,$status);
           $data = array('success' => false,'status' => $status,'data' => array(),'extra' => array('msg'=>$msg));
        }
        
        return $data;
    }

    /**
    *  app专用插入新解禁飞区域的数据同时返回license key 
    *  account dji邮箱
    *  device_code SN1,SN2
    *  lat 经度
    *  lng 纬度
    *  radius 半径
    *  height 高度
    *  begin_at 开始时间戳（1433223998） 10位
    *  end_at 结束时间戳（1433223998） 10位
    *  country 国家
    *  city 城市
    *  time 当前时间戳（1433223998） 10位
    *  token 用户认证token
    *  name license key 对应的名字
    *  os 系统 ios,android default
    *  appVersion app版本号
    *  version 接口版本 默认1.0,赵建使用1.1
    *
    *  signature  签名
    *  
    *  return {"success":true,"status":200,"data":[{"id":1}],"extra":{"msg":"","key":1444791087}}
    *  http://dev.flysafe-admin.dji.com/index.php?r=userarea/insertlicense&account=11@dij.com&device_code=12-12321&lat=12.2132&lng=123.23223&radius=100&height=90&begin_at=1433223998&end_at=1433223998&country=cn&city=beijing&time=1433223998&token&name=20151012&os=ios&appVersion=2.3.1&version=1.1&signature=46475897907BE2B50938AAD812561C5DCA124C10
    *
    */
    public function actionInsertlicense()
    {
        $get_str = json_encode($_REQUEST);
        $this->add_log($get_str,"userarea_insertlicense");

        $model = array();
        $model['account'] = Yii::$app->request->get('account', '');
        $model['device_code'] = Yii::$app->request->get('device_code', '');
        $model['lat'] = Yii::$app->request->get('lat', '');
        $model['lng'] = Yii::$app->request->get('lng', '');
        $model['radius'] = Yii::$app->request->get('radius', '');
        $model['height'] = Yii::$app->request->get('height', '');
        $model['begin_at'] = Yii::$app->request->get('begin_at', '');
        $model['end_at'] = Yii::$app->request->get('end_at', '');
        $model['country'] = Yii::$app->request->get('country', '');
        $model['city'] = Yii::$app->request->get('city', '');
        $model['time'] = Yii::$app->request->get('time', '');
        $model['token'] = Yii::$app->request->get('token', '');
        $model['name'] = Yii::$app->request->get('name', '');
        $model['os'] = Yii::$app->request->get('os', '');
        $model['appVersion'] = Yii::$app->request->get('appVersion', '');
        $model['version'] = Yii::$app->request->get('version', '');
       

        $locale = Yii::$app->request->get('locale', 'en');
        $paramResult = $this->param_str_replace($model,$locale);
        if ($paramResult['status'] != 200) {
            return $paramResult;
        }  
        $nowtime = time();
        //echo time();exit;
        $signature = Yii::$app->request->get('signature', '');
        $signature = empty($signature) ? Yii::$app->request->post('signature', '') : $signature;
        
        if (empty($signature) || empty($model['account']) || empty($model['lat']) || empty($model['lng']) || empty($model['radius']) || empty($model['begin_at']) || empty($model['end_at'])
             || empty($model['country']) || empty($model['city']) ) {
              $status = 401;
              $msg = $this->get_locale_msg($locale,$status);
              $return_signature = $this->gethmacsignature($model,$status.$nowtime);
              echo json_encode(array('success' => true,'status' => $status,'time'=>$nowtime,'signature' => $return_signature,'data' => array(),'extra' => array('msg'=>$msg)));
              exit;
        }
        if (! $this->validate_is_email($model['account'])) {
            $status = 410;
            $msg = $this->get_locale_msg($locale,$status);
            $return_signature = $this->gethmacsignature($model,$status.$nowtime);
            echo json_encode(array('success' => true,'status' => $status,'time'=>$nowtime,'signature' => $return_signature,'data' => array(),'extra' => array('msg'=>$msg)));
            exit;
        }       
        $signature_now = $this->getsignature($model); 
        //echo $signature_now ;
        if ($signature_now != $signature) {
                $status = 400;
                $msg = $this->get_locale_msg($locale,$status);
                $return_signature = $this->gethmacsignature($model,$status.$nowtime);
                echo json_encode(array('success' => true,'status' => $status,'time'=>$nowtime,'signature' => $return_signature,'data' => array(),'extra' => array('msg'=>$msg)));
                exit;
        }        
        if (! $this->agreed_is_email($model['account'])) {
             $status = 405;
             $msg = $this->get_locale_msg($locale,$status);
             $return_signature = $this->gethmacsignature($model,$status.$nowtime);
             echo json_encode(array('success' => true,'status' => $status,'time'=>$nowtime,'signature' => $return_signature,'data' => array(),'extra' => array('msg'=>$msg)));
             exit;
        }       
        $model['begin_at'] = date('Y-m-d H:i:s',substr($model['begin_at'], 0,10) + 0);
        $model['end_at'] = date('Y-m-d H:i:s',substr($model['end_at'], 0,10) +0);
        
        $luckkey = 'userarea_insert'.$model['account'].$signature;
        $luckdata = Yii::$app->cache->get($luckkey);
        if ( $luckdata ) {
            $status = 402;
            $msg = $this->get_locale_msg($locale,$status);
            $return_signature = $this->gethmacsignature($model,$status.$nowtime);
            echo json_encode(array('success' => true,'status' => $status,'time'=>$nowtime,'signature' => $return_signature,'data' => array(),'extra' => array('msg'=>$msg)));
            exit;
        }
        Yii::$app->cache->set($luckkey, 1, 10);

        $where = $model;
        unset($where['time']);
        unset($where['name']);
        unset($where['os']);
        unset($where['appVersion']);
        unset($where['version']);
        unset($where['token']);
        $start = $reid = 0;       
        $data = UserArea::getAndOREqualWhere($where,$start,1);   
        if (!$data)
        {
           $data = UserArea::add($model);
        }else{
           $data = $reid = $data[0]['id'];          
        }        
        $LicenseKey = time();      
        if ($data > 0) {
            $tmpmodel = array();
            $tmpmodel['account'] = $model['account'];
            $tmpmodel['device_code'] = $model['device_code'];
            $tmpmodel['user_areas_id'] = $data;
            if ($reid > 0) {
                $dataAL = UserAreasLicense::getAndEqualWhere($tmpmodel,$start,1);
                if ( $dataAL &&  $dataAL['0']['id'] > 0) {
                  $tmpmodel = array();
                  $tmpmodel['account'] = $model['account'];
                  $tmpmodel['key'] = $dataAL['0']['key'];                            
                  $tmpResultLicense = UserLicense::getAndEqualWhere($tmpmodel);                 
                  if ($tmpResultLicense && $tmpResultLicense['0']['id'] > 0) {
                    $status = 200;
                    $return_signature = $this->gethmacsignature($model,$status.$nowtime);
                    $data = array('success' => true,'status' => $status,'time'=>$nowtime,'signature' => $return_signature,'data' => array(array('id'=> $tmpResultLicense['0']['id'])),'extra' => array('msg'=>'','key'=> $tmpResultLicense['0']['key'],'re' => 1));
                    return $data;
                  }
                }               
                
            }
           
            $tmpmodel['key'] = $LicenseKey;
            $tmpResult = UserAreasLicense::add($tmpmodel);
            if ($tmpResult>0) {
                $tmpmodel = array();
                $tmpmodel['account'] = $model['account'];
                $tmpmodel['key'] = $LicenseKey;
                $tmpmodel['name'] = $model['name'];               
                $tmpResultLicense = UserLicense::add($tmpmodel);
                if ($tmpResultLicense > 0) {
                   $status = 200;
                   $return_signature = $this->gethmacsignature($model,$status.$nowtime);

                   $data = array('success' => true,'status' => $status,'time'=>$nowtime,'signature' => $return_signature,'data' => array(array('id'=>$tmpResultLicense)),'extra' => array('msg'=>'','key'=>$LicenseKey));
                }else{
                  $status = 500;
                  $msg = $this->get_locale_msg($locale,$status);
                  $return_signature = $this->gethmacsignature($model,$status.$nowtime);
                  $data = array('success' => false,'status' => $status,'time'=>$nowtime,'signature' => $return_signature,'data' => array(),'extra' => array('msg'=>$msg));
               }            
            }else{
              $status = 500;
              $msg = $this->get_locale_msg($locale,$status);
              $return_signature = $this->gethmacsignature($model,$status.$nowtime);
              $data = array('success' => false,'status' => $status,'time'=>$nowtime,'signature' => $return_signature,'data' => array(),'extra' => array('msg'=>$msg));
            }            
        }else{
           $status = 500;
           $msg = $this->get_locale_msg($locale,$status);
           $return_signature = $this->gethmacsignature($model,$status.$nowtime);
           $data = array('success' => false,'status' => $status,'time'=>$nowtime,'signature' => $return_signature,'data' => array(),'extra' => array('msg'=>$msg));
        }
        
        return $data;
    }   
    /**
    *  用户意见反馈记录接口
    *  account dji邮箱
    *  name  
    *  email
    *  message
    *  area_id
    *  area_name
    *  latitude
    *  longitude
    *  token 用户认证token
    *  os 系统 ios,android default
    *  appVersion app版本号
    *  version 接口版本 默认1.0,赵建使用1.1
    *
    *  signature  签名 以上参数按照顺序链接在一起
    *  
    *  return {"success":true,"status":200,"time":1445504208,"signature":"E903BD9729888049390E25744C09CC8A744832E6C52C6CA18DB30AA8049E3BAA","data":[{"id":28}],"extra":{"msg":""}}
    *  http://dev.flysafe-admin.dji.com/index.php?r=userarea/errorreport&account=11@dij.com&name=1&email=2&message=1433223998&area_id=3&area_name=23432&latitude=34&longitude=233&token=232&os=ios&appVersion=3.4.3&version=1.1&signature=EC7EE8C9BCD2532BA5BCF79236729875D2C5435D5FD0C7CDE7C818A739EEB3D5
    *
    */
    public function actionErrorreport()
    {
        $model = array();
        $model['account'] = Yii::$app->request->get('account', '');
        $model['name'] = Yii::$app->request->get('name', '');
        $model['email'] = Yii::$app->request->get('email', '');
        $model['message'] = Yii::$app->request->get('message', '');
        $model['type'] = Yii::$app->request->get('type', ''); 
        $model['area_id'] = Yii::$app->request->get('area_id', '');
        $model['area_name'] = Yii::$app->request->get('area_name', '');
        $model['latitude'] = Yii::$app->request->get('latitude', '');
        $model['longitude'] = Yii::$app->request->get('longitude', '');
        $model['token'] = Yii::$app->request->get('token', '');
        $model['os'] = Yii::$app->request->get('os', '');
        $model['appVersion'] = Yii::$app->request->get('appVersion', '');
        $model['version'] = Yii::$app->request->get('version', '');        

        $locale = Yii::$app->request->get('locale', 'en');
        $paramResult = $this->param_str_replace($model,$locale);
        if ($paramResult['status'] != 200) {
            return $paramResult;
        }  
        $nowtime = time();
        //echo time();exit;
        $signature = Yii::$app->request->get('signature', '');
        $signature = empty($signature) ? Yii::$app->request->post('signature', '') : $signature;
        
        if (empty($signature) || empty($model['account']) || empty($model['message']) ) {
              $status = 401;
              $msg = $this->get_locale_msg($locale,$status);
              $return_signature = $this->gethmacsignature($model,$status.$nowtime);
              echo json_encode(array('success' => true,'status' => $status,'time'=>$nowtime,'signature' => $return_signature,'data' => array(),'extra' => array('msg'=>$msg)));
              exit;
        }
        if (! $this->validate_is_email($model['account'])) {
            $status = 410;
            $msg = $this->get_locale_msg($locale,$status);
            $return_signature = $this->gethmacsignature($model,$status.$nowtime);
            echo json_encode(array('success' => true,'status' => $status,'time'=>$nowtime,'signature' => $return_signature,'data' => array(),'extra' => array('msg'=>$msg)));
            exit;
        }       
        $signature_now = $this->getsignature($model); 
        //echo $signature_now ;
        if ($signature_now != $signature) {
                $status = 400;
                $msg = $this->get_locale_msg($locale,$status);
                $return_signature = $this->gethmacsignature($model,$status.$nowtime);
                echo json_encode(array('success' => true,'status' => $status,'time'=>$nowtime,'signature' => $return_signature,'data' => array(),'extra' => array('msg'=>$msg)));
                exit;
        }         
        
        $luckkey = 'userarea_errorreport'.$model['account'].$signature;
        $luckdata = Yii::$app->cache->get($luckkey);
        if ( $luckdata ) {
            $status = 402;
            $msg = $this->get_locale_msg($locale,$status);
            $return_signature = $this->gethmacsignature($model,$status.$nowtime);
            echo json_encode(array('success' => true,'status' => $status,'time'=>$nowtime,'signature' => $return_signature,'data' => array(),'extra' => array('msg'=>$msg)));
            exit;
        }
        Yii::$app->cache->set($luckkey, 1, 10);

        $tmpResult = ErrorReportUnlimit::add($model);
        if ($tmpResult > 0) {
           $status = 200;
           $return_signature = $this->gethmacsignature($model,$status.$nowtime);
           $data = array('success' => true,'status' => $status,'time'=>$nowtime,'signature' => $return_signature,'data' => array(array('id'=>$tmpResult)),'extra' => array('msg'=>''));
        }else{
          $status = 500;
          $msg = $this->get_locale_msg($locale,$status);
          $return_signature = $this->gethmacsignature($model,$status.$nowtime);
          $data = array('success' => false,'status' => $status,'time'=>$nowtime,'signature' => $return_signature,'data' => array(),'extra' => array('msg'=>$msg));
       }            
        
       return $data;

    }



    // 写入文件
    protected function add_log($msg, $type = 'userarea')
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
