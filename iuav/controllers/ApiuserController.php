<?php

namespace app\controllers;

use app\components\ErrorObj;
use app\models\Bossviewworkinfo;
use app\models\FirstTimeVisit;
use app\models\Manager;
use app\models\ManagerDrone;
use app\models\NotificationLastReadIdx;
use app\models\UserAvatar;
use app\models\Viewworkinfo;
use Aws\S3\S3Client;
use Aws\Exception;
use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use OSS\Core\OssException;
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
use app\models\Agrorecord;
use yii\base\ErrorException;
use yii\web\Request;
use app\components\DjiUser;
use app\components\DjiAgentUser;
use app\models\Agroteam;
use app\models\Agroflyer;
use app\models\Agrotask;
use app\models\Agroactiveflyer;
use app\models\Agroactiveinfo;
use app\models\Agropolicies;
use app\models\Agroflight;
use app\models\Iuavflightdata;
use app\models\Agroteamtask;
use app\models\AgroMissionComplete;
use app\models\PublicNotification;
use app\models\Agroflyerworkinfo;
use app\models\LastModifyPhoneTime;
use app\components\Djihmac;
use PHPExcel;
use PHPExcel_Writer_Excel2007;
use PHPExcel_Style_Border;
use PHPExcel_Style_Alignment;
use PHPExcel_IOFactory;
use PHPExcel_Style_Fill;
use  yii\web\Session;
use OSS\OssClient;

class ApiuserController extends Controller
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

    protected function getUserInfo($actionName,$lucked=1)
    {
        $status_msg = 'failed'; 
        if (empty(Yii::$app->request->cookies['_meta_key']) )
        {
          $data = array('status' => 1001,'status_msg'=> $status_msg,'message'=>'登录已经过期,请重新登录');
          return $data;
        } 
        $meta_key = Yii::$app->request->cookies['_meta_key']->value; 
        $djiUser = new DjiUser();       
        $userData = $djiUser->get_account_info_by_key($meta_key);
        if ($userData && $userData['status'] == '0' && $userData['status_msg'] == 'ok') {
          return $userData;
        } else {
           $data = array('status' => 1001,'status_msg'=> $status_msg,'message'=>'登录已经过期,请重新登录');
           return $data;
        }
    }

    protected function get_user_info()
    {
        if (empty(Yii::$app->request->cookies['_meta_key'])) {
            return null;
        }

        $meta_key = Yii::$app->request->cookies['_meta_key']->value;
        $djiUser = new DjiUser();
        $userData = $djiUser->get_account_info_by_key($meta_key);
        if ($userData && $userData['status'] == '0' && $userData['status_msg'] == 'ok') {
            return $userData;
        }

        return null;
    }
   
    /*
    *  读取用户的信息  /apiuser/userinfo
    *  return {"status":0,"status_msg":"ok","message":"","item_total":0,"items":{"user_type":"1","nick_name":"weiping.huang@dji.com","email":"weiping.huang@dji.com","user_id":"23617225210722647","country":"","sex":"","avatar":""}}
    *  {"status":0,"status_msg":"ok","message":"","item_total":0,"items":{"user_type":2,"team_info":[{"name":"RM_testwqew","id":"2"}],"nick_name":"ozBnD8xjADN3","email":"hwphwp@dji.com","user_id":"772985517407567872","country":"","sex":"","avatar":""}}
    *  "status":0 和 "status_msg":"ok" 表示创建成功
    * "status":1000 表示参数不合法；"status":1001 表示登录已经过期,请重新登录；"status":1004 表示5秒内重复提交,请稍后重试；
    *  "status":1007 表示权限不足；
    */
    public function actionUserinfo()
    {
      $actionName = 'actionUserinfo';
      $userData = $this->getUserInfo($actionName);
      if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
          return array('status' => 1001,'status_msg'=> 'failed','message'=>'登录已经过期,请重新登录');
      }
      if (empty($userData['items']['0']['account_info'])) {
          return array('status' => 1001,'status_msg'=> 'failed','message'=>'登录已经过期,请重新登录');
      }

      $self_uid = $userData['items']['0']['account_info']['user_id'];
      $self_account = $userData['items']['0']['account_info']['email'];

      $newItems = array();
      $newItems['user_type'] = 0;

      do {
          $activeInfo = Agroactiveinfo::getAndEqualWhere(['uid' => $self_uid, 'deleted' => '0'], 0, 1);
          if ($activeInfo && is_array($activeInfo) && count($activeInfo) > 0) {
              // means boss
              $newItems['user_type'] = 1;
              break;
          }

          $manager_info = Manager::findAll(['account' => $self_account]);
          if ($manager_info && is_array($manager_info) && count($manager_info) > 0) {
              // means manager
              $newItems['user_type'] = 3;
              foreach ($manager_info as $item) {
                  $newItems['manager_info'][$item['boss_uid']]['account'] = $item['boss_account'];
                  $newItems['manager_info'][$item['boss_uid']]['level'] = $item['level'];
              }
              break;
          }

          $fields = 'agro_team.name,agro_team.id,agro_team.uid,agro_flyer.job_level';
          $flyerInfo = Agroflyer::getTeamWhere(['uid' => $self_uid, 'deleted' => '0'],$fields,0,-1);
          if ($flyerInfo && is_array($flyerInfo) && count($flyerInfo) > 0) {
              $newItems['user_type'] = 2;
              $newItems['team_info'] = $flyerInfo;
              break;
          }

          return ['status' => 2000, 'status_msg' => 'failed', 'message' => '非法用户'];
      } while (false);

      $newItems['nick_name'] = $userData['items']['0']['account_info']['nick_name'];
      $newItems['email'] = $userData['items']['0']['account_info']['email'];            
      $newItems['user_id'] = $userData['items']['0']['account_info']['user_id'];
      $newItems['country'] = '';
      $newItems['gender'] = '';
      $newItems['avatar'] = UserAvatar::getUserAvatar($userData['items']['0']['account_info']['user_id']);
      $newItems['phone'] = $this->get_boss_phone($userData['items']['0']['account_info']['user_id'], true);

      $userData['items'] = $newItems; 
      return $userData; 
    }
    /* 修改用户的名称  /apiuser/editname   
    * @parameter string name  飞手的名称
    * "status":0 和 "status_msg":"ok" 表示修改成功
    * "status":1000 表示参数不合法；"status":1001 表示登录已经过期,请重新登录；"status":1004 表示5秒内重复提交,请稍后重试；
    * "status":1007 表示权限不足；
    */
    public function actionEditname() {

        $name = $this->getPostValue("name"); 

        if (!isset($name)) {
            return array('status' => 1000, 'status_msg'=> 'failed','message'=>'参数不合法');
        }

        $actionName = 'actionEditname';
        $userData = $this->getUserInfo($actionName);
        if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
            return $userData;
        }
        if (empty($userData['items']['0']['account_info'])) {
            return array('status' => 1001,'status_msg'=> 'failed','message'=>'登录已经过期,请重新登录');
        }

        if (isset(Yii::$app->params['GWServer']) ) {
            $gwapi = Yii::$app->params['GWServer']['GWAPIURL']; 
            $gwapikey = Yii::$app->params['GWServer']['GWAPIFLYSAFEKEY'];   
            $appId = Yii::$app->params['GWServer']['GWAPIAPPID'];  
        } 

        $djiUser = new DjiUser();

        $meta_key = Yii::$app->request->cookies['_meta_key']->value;
        $token = $djiUser->get_token_by_meta_key($gwapi,$gwapikey,$meta_key,$appId);  

        $imgdata = array();
        $imgdata['nick_name'] = $name;  
        $imgdata['token'] = $token['items'][0]['token']; 

        $url = $gwapi."/gwapi/api/accounts/account_update";
        $data = $djiUser->postGateway($url, $imgdata); 

        return array('status' => $data['status'], 'status_msg'=> $data['status_msg'],);
    }

    /* 获取用户的名称  /apiuser/getname   
    * "status":0 和 "status_msg":"ok" 表示修改成功 "name":"xxxx" 表示获取的用户名称
    * "status":1000 表示参数不合法；"status":1001 表示登录已经过期,请重新登录；"status":1004 表示5秒内重复提交,请稍后重试；
    * "status":1007 表示权限不足；
    */
    public function actionGetname() {

        $actionName = 'actionEditname';
        $userData = $this->getUserInfo($actionName);
        if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
            return $userData;
        }
        if (empty($userData['items']['0']['account_info'])) {
            return array('status' => 1001,'status_msg'=> 'failed','message'=>'登录已经过期,请重新登录');
        }

        if (isset(Yii::$app->params['GWServer']) ) {
            $gwapi = Yii::$app->params['GWServer']['GWAPIURL']; 
            $gwapikey = Yii::$app->params['GWServer']['GWAPIFLYSAFEKEY'];
            $appId = Yii::$app->params['GWServer']['GWAPIAPPID'];         
        } else {
            return array('status' => 1000, 'status_msg'=> 'failed');
        }

        $meta_key = Yii::$app->request->cookies['_meta_key']->value;
        $djiUser = new DjiUser();

        $token = $djiUser->get_token_by_meta_key($gwapi,$gwapikey,$meta_key,$appId); 

        $imgdata = array();
        $imgdata['token'] = $token['items'][0]['token']; 

        $url = $gwapi."/gwapi/api/accounts/get_account_info_by_key";
        $data = $djiUser->postGateway($url, $imgdata); 

        return array('status' => $data['status'], 
                    'status_msg'=> $data['status_msg'], 
                    'name'=> $data['items']['0']['account_info']['nick_name']);
    }
    /*
    *  飞行器管理  /apiuser/aerocraft 
    *  @parameter string page  页面 
    *  @parameter string size  每页数目 
    *  参数用GET方式
    * {"status":0,"status_msg":"ok","count":"1","data":[{"id":"1","body_code":"test26","hardware_id":"test26","type":"mg-1","nickname":"1232","team_id":"2","locked":"0","created_at":"2016-08-22 18:36:01","pol_no":"test20160322001","exp_tm":"2017-03-21","query_flag":"1","pol_str":"","team_info":{"id":"2","name":"RM_testwqew","flyer_info":[{"flyerid":"3","uid":"22410717222020942","realname":"","account":"19193213@qq.com","idcard":"","phone":null,"avatar":null,"job_level":"0","address":""}],"flyer_count":1}}]}
    *  "status":0 和 "status_msg":"ok" 表示创建成功
    * "status":1000 表示参数不合法；"status":1001 表示登录已经过期,请重新登录；"status":1004 表示5秒内重复提交,请稍后重试；
    *  "status":1007 表示权限不足；
    */
    public function actionAerocraft()
    {
      $page = intval(Yii::$app->request->get("page"));
      $size = intval(Yii::$app->request->get("size"));
      if ($size < 1 || $size > 100 ) {
          $size = 30;
      }
      if ($page < 1 ) {
          $page = 1;
      }

      $start = ($page - 1) * $size;
      $start = $start < 0 ? 0 : $start;

      $status_msg = 'failed';    
      $actionName = 'actionAerocraft';
      $userData = $this->getUserInfo($actionName);
      if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
          return $userData;
      }
      if (empty($userData['items']['0']['account_info'])) { 
          return array('status' => 1007, 'status_msg'=> $status_msg,'message'=>'权限不足');
      }

      $hardwares = array();
      $data = array('data' => array() );
      $list = array();     
      $where = $newItems = $newFlyer = array();
      $where['uid'] = $userData['items']['0']['account_info']['user_id'];
      $where['deleted'] = '0';

      $activeCount = Agroactiveinfo::getAndEqualWhereCount($where);
      if ($activeCount > 0) {
          $fields = 'id,pol_no,exp_tm,query_flag,uid,body_code,hardware_id,type,nickname,team_id,
              locked,locked_notice,timelocked,timelocked_notice,lock_begin,lock_end,created_at, is_online';
          $activeInfo = Agroactiveinfo::getPoliciesWhereOrderByIsOnline($where,$fields);
          if(!$activeInfo) {
              return array('status' => 1007, 'status_msg'=> $status_msg,'message'=>'权限不足');
          }
          foreach ($activeInfo as $key => $value) {
              $value['pol_str'] =  Agropolicies::getPolNoStr($value['pol_no'],$value['query_flag'],$value['exp_tm']);  
              $value['is_boss'] = '1';          
              //说明这架飞机属于这个teamid                    
              $where['id'] = $value['team_id'];                        
              $teamInfo = Agroteam::getAndEqualWhere($where, 0,1);
              $newTeamInfo = array();
              if ($teamInfo && is_array($teamInfo)) {
                  $flyerWhere = array();
                  $flyerWhere['deleted'] = '0';
                  $flyerWhere['team_id'] = $value['team_id'];
                  $flyerWhere['active_id'] = $value['id'];
                  $flyerWhere['hardware_id'] = $value['hardware_id'];
                  $fields = 'agro_flyer.id as flyerid,agro_flyer.uid,agro_flyer.realname,agro_flyer.account,
                      agro_flyer.idcard,agro_flyer.phone,agro_flyer.avatar,agro_flyer.job_level,agro_flyer.address';
                  $flyerInfo = Agroactiveflyer::getFlyerWhere($flyerWhere,$fields,0, -1);//飞手信息是通过agroactiveflyer这张表查出来的
                  for ($i = 0; $i < count($flyerInfo); $i++) {
                      $flyerInfo[$i]['avatar'] = UserAvatar::getUserAvatar($flyerInfo[$i]['uid']);
                  }
                  $newTeamInfo = array('id' => $teamInfo['0']['id'] ,'name' => $teamInfo['0']['name']);
                  if(empty($flyerInfo)){
                      $newTeamInfo['flyer_info'] = null;
                  } else {
                      $newTeamInfo['flyer_info'] = $flyerInfo;
                  }
                  $newTeamInfo['flyer_count'] = count($flyerInfo);
                  $value['team_info'] = $newTeamInfo; 
              }                    
              $value['exp_tm'] = substr($value['exp_tm'], 0,4).'-'.substr($value['exp_tm'], 4,2).'-'.substr($value['exp_tm'], 6,2);
              $value['aircraft_status'] = $value['is_online']; //aircraft_status ：0 未联网 ；1 正在作业
              $activeInfo[$key] = $value;
              $hardwares[$key] = $value['hardware_id'];
          }
          $list['status'] = 0;  
          $list['status_msg'] = 'ok';  
          $list['count'] = $activeCount;                
          $list['data'] = $activeInfo;           
          //return $list; 
      } 
      //添加作为飞手能控制的飞行器
      $where = array();
      $where['uid'][] = $whereActive['uid'] = $userData['items']['0']['account_info']['user_id'];
      $whereActive['deleted'] = '0';
      $fields = 'agro_team.name,agro_team.id,agro_team.uid, agro_flyer.job_level, agro_flyer.uid as flyer_uid';
      $flyerInfo = Agroflyer::getTeamWhere($whereActive, $fields, 0, -1);   
      if ( $flyerInfo && is_array( $flyerInfo)) {  //这个飞手属于多个老板的队伍
          $where = array();
          $where['deleted'] = '0'; 
          foreach ($flyerInfo as $key => $value) {
              $where['uid'][] = $value['uid']; 
              $where['team_id'][] = $value['id'];
              $where[$value['id']] = $value['job_level'];
              $where['flyer_uid'] = $value['flyer_uid'];
          }
      } else {      
          return $list; 
      }
      $whereActive = array();

      $whereActive['showed'] = '1';
      $whereActive['deleted'] = '0';
      $whereActive['flyer_uid'] = $where['flyer_uid'];
      $whereActive['team_id'] = $where['team_id']; //要在team里面找
      $fields = 'pol_no, exp_tm, query_flag, id, uid, body_code, hardware_id, type, 
          nickname, team_id, locked,locked_notice,timelocked, timelocked_notice,lock_begin,lock_end, created_at, is_online';
      $activeInfo = Agroactiveinfo::getPoliciesWhereOrderByIsOnline($whereActive,$fields); //var_dump($activeInfo);die;
      $result = array();
      if ( $activeInfo && is_array( $activeInfo)) {
         foreach ($activeInfo as $key => $value) {
            $value['pol_str'] =  Agropolicies::getPolNoStr($value['pol_no'],$value['query_flag'],$value['exp_tm']); 
            $value['is_boss'] = '0'; 
            if($value['team_id'] <= 0) {
                continue;
            }              
            //说明这架飞机属于这个teamid   
            $whereTeam = array();                 
            $whereTeam['id'] = $value['team_id'];                        
            $teamInfo = Agroteam::getAndEqualWhere($whereTeam, 0,1);
            $newTeamInfo = array();
            if ($teamInfo && is_array($teamInfo)) {
                $flyerWhere = array();
                $flyerWhere['deleted'] = '0';
                $flyerWhere['team_id'] = $value['team_id'];
                $flyerWhere['active_id'] = $value['id'];
                $flyerWhere['hardware_id'] = $value['hardware_id'];
                if ($activeCount > 0) {
                    if(in_array($value['hardware_id'], $hardwares)){//去重
                        continue;
                    }
                }
                $fields = 'agro_flyer.id as flyerid,agro_flyer.uid,agro_flyer.realname,agro_flyer.account,agro_flyer.idcard,agro_flyer.phone,agro_flyer.avatar,agro_flyer.job_level,agro_flyer.address';
                $flyerInfo = Agroactiveflyer::getFlyerWhere($flyerWhere,$fields,0, -1); //飞手信息是通过agroactiveflyer这张表查出来的
                for ($i = 0; $i < count($flyerInfo); $i++) {
                    $flyerInfo[$i]['avatar'] = UserAvatar::getUserAvatar($flyerInfo[$i]['uid']);
                }
                $newTeamInfo = array('id' => $teamInfo['0']['id'] ,'name' => $teamInfo['0']['name']);
                if(empty($flyerInfo)){
                    $newTeamInfo['flyer_info'] = null;
                }else{
                    $newTeamInfo['flyer_info'] = $flyerInfo;
                }
                $newTeamInfo['flyer_count'] = count($flyerInfo);
                $value['team_info'] = $newTeamInfo; 
            } else {
                $value['team_info'] = null;
            }                        
            $value['exp_tm'] = substr($value['exp_tm'], 0,4).'-'.substr($value['exp_tm'], 4,2).'-'.substr($value['exp_tm'], 6,2);
            $value['aircraft_status'] = $value['is_online']; //aircraft_status ：0 未联网 ；1 正在作业
            $result[$key] = $value;
         }
      }
      $list['status'] = 0;  
      $list['status_msg'] = 'ok';  
      if(isset($list['count'])) {
          $list['count'] = count($result) + $list['count'];
      } else {
          $list['count'] = count($result);
      }
      if(isset($list['data'])) {
          $list['data'] = array_merge($list['data'], $result);
      } else {
          $list['data'] = $result;
      }               
/*      if ($list['data']) {
          $list['data'] = $this->array_sort($list['data'], 'is_online'); //按照在线排序 
      }*/
      $tmp = array();
      for ($i = 0; $i < $size && $start + $i < $list['count']; $i++) {
          $j = $i + $start;
          $tmp[] = $list['data'][$j];
      }

      $list['data'] = $tmp;

      return $list; 
               
  } 
  protected function array_sort($arr, $keys, $type = 'asc') { //二维数组排序
      $keysvalue = $new_array= array();
      foreach($arr as $k=>$v) {
          $keysvalue[$k] = $v[$keys];
      }
      if($type== 'asc') {
          arsort($keysvalue);
      } else {
          arsort($keysvalue);
      }
      reset($keysvalue);
      foreach($keysvalue as $k=>$v) {
          $new_array[$k] = $arr[$k];
      }
      return $new_array;
  }
      /*
    *  时间段锁定植保机  /apiuser/timelock
    *  @parameter string id  飞行器id
    *  @parameter string locked   0:解锁，1:锁定 
    *  @parameter string lock_begin  锁定开始时间 
    *  @parameter string lock_end  锁定结束时间 
    *  参数用POST方式
    * {"status":0,"status_msg":"ok"}
    *  "status":0 和 "status_msg":"ok" 表示创建成功
    * "status":1000 表示参数不合法；"status":1001 表示登录已经过期,请重新登录；"status":1004 表示5秒内重复提交,请稍后重试；
    *  "status":1007 表示权限不足；
    */
    public function actionTimelock() {
        $model['id'] = intval(Yii::$app->request->post("id")); 
        $model['timelocked'] = Yii::$app->request->post("locked");
        $model['lock_begin'] = Yii::$app->request->post("lock_begin"); 
        $model['lock_end'] = Yii::$app->request->post("lock_end"); 

        $status_msg = 'failed';
        if (empty($model['id']) || !isset($model['timelocked'])) {
           return array('status' => 1000, 'status_msg'=> $status_msg,'message'=>'参数不合法');
        }

        $actionName = 'actionTimelock';
        $userData = $this->getUserInfo($actionName);
        if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
            return $userData;
        }
        if (empty($userData['items']['0']['account_info'])) { 
            return array('status' => 1007, 'status_msg'=> $status_msg,'message'=>'权限不足');
        }

        $where = array();
        $where['uid'] = $userData['items']['0']['account_info']['user_id'];
        $where['deleted'] = '0';
        $where['id'] = $model['id'];

        $activeinfo = Agroactiveinfo::getAndEqualWhere($where); 
        if (empty($activeinfo)) {
            return array('status' => 1007, 'status_msg'=> $status_msg,'message'=>'权限不足');
        }

        $model['timelocked_notice'] = '0'; 

        if (isset($model['timelocked']) && $model['timelocked'] == '0') { //取消时间段锁定
            $begin = $end = '0';
            $model['lock_begin'] = '0';
            $model['lock_end'] = '0';
            $sendAPP['cmd'] = 'unlock';

        } else { //时间段锁定
            if (strlen($model['lock_begin']) != 10 || strlen($model['lock_end']) != 10) {
                return array('status' => 1000, 'status_msg'=> $status_msg,'message'=>'参数不合法');
            }

            $begin = 1000 * mktime(0, 0, 0, substr($model['lock_begin'], 5,2), substr($model['lock_begin'], 8,2), substr($model['lock_begin'], 0,4));
            $end = 1000 * mktime(0, 0, 0, substr($model['lock_end'], 5,2), substr($model['lock_end'], 8,2), substr($model['lock_end'], 0,4));           
            $sendAPP['cmd'] = 'lock'; 
        }

        $sendAPP['sn'] = $activeinfo['0']['hardware_id'];
        $sendAPP['lock_begin'] = $begin;
        $sendAPP['lock_end'] = $end;
        $sendAPP['bossid'] = $activeinfo['0']['uid'];
        $sendAPP['bossname'] = agroflyer::getNameByID(array('flyerid' => $activeinfo['0']['uid']));//老板也是一个飞手
        $djiUser = new DjiUser();     
        $goResult = $djiUser->runGoTimeLock($sendAPP);   

        if ($goResult && is_array($goResult) && $goResult['status'] == 0) {               
            $model['timelocked_notice'] = '1';         
        }         

        $update_result = Agroactiveinfo::updateTimeLocked($model);

        $record = array();
        $record['upper_uid'] = $record['uid'] = $userData['items']['0']['account_info']['user_id'];
        $record['team_id'] = 0;
        $record['operator'] = $userData['items']['0']['account_info']['email'];
        $record['type'] = '植保机拥有者';
        if (isset($model['timelocked']) && $model['timelocked'] == '0') {
            $record['content'] = '取消时间段锁定';
            $record['detail'] = '取消时间段锁定 <i>'.$activeinfo[0]['nickname'].'</i> 植保机';
        } else {
            $record['content'] = '时间段锁定';
            $record['detail'] = '设定 <i>'.$activeinfo[0]['nickname'].'</i> 植保机'.'在 <i>'.$model['lock_begin'].'</i> - <i>'.$model['lock_end'].'</i> 时间锁定';
        }
        $record['ip'] = $this->get_client_ip(); 
        Agrorecord::add($record); 

        if ($update_result) {
            return array('status' => 0, 'status_msg'=> 'ok','message'=>'操作成功');
        }
    }
    /*
    *  编辑飞行器增加飞机名称、锁定、团队等  /apiuser/editaerocraft 
    *  @parameter string id  飞行器id
    *  @parameter string nickname  飞机名称 
    *  @parameter string locked  0:解锁，1:锁定 
    *  @parameter string team_id 团队id
    *  参数用POST方式
    * {"status":0,"status_msg":"ok","id":1}
    *  "status":0 和 "status_msg":"ok" 表示创建成功
    * "status":1000 表示参数不合法；"status":1001 表示登录已经过期,请重新登录；"status":1004 表示5秒内重复提交,请稍后重试；
    *  "status":1007 表示权限不足；
    */
    public function actionEditaerocraft() {
        if (extension_loaded ('newrelic')) {
            newrelic_name_transaction ( 'user/editaerocraft');
        }

        $model = array();
        $model['id'] = intval(Yii::$app->request->post("id"));  
        $model['locked'] = Yii::$app->request->post("locked");
        $model['nickname'] = Yii::$app->request->post("nickname");
        $model['team_id'] = Yii::$app->request->post("team_id");
        $flyers = Yii::$app->request->post("flyers"); 

        $status_msg = 'failed';
        if (empty($model['id'])) {
           return array('status' => 1000, 'status_msg'=> $status_msg,'message'=>'参数不合法');
        }
        $actionName = 'actionEditaerocraft';
        $userData = $this->getUserInfo($actionName);
        if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
            return $userData;
        }
        if (empty($userData['items']['0']['account_info'])) { 
            return array('status' => 1007, 'status_msg'=> $status_msg,'message'=>'权限不足');
        }

        $data = array('data' => array() );
        $list = array();     
        $where = $newItems = $newFlyer = array();
        $where['uid'] = $userData['items']['0']['account_info']['user_id'];
        $where['deleted'] = '0';
        $where['id'] = $model['id'];
        $active = Agroactiveinfo::getAndEqualWhere($where);
        if (empty($active)) {
            return array('status' => 1007, 'status_msg'=> $status_msg,'message'=>'权限不足');
        }

        if (isset($model['locked']) && $model['locked'] == 0) { //实时解锁，还需要通知实时监控页面  
            $sendAPP['cmd'] = 'unlock';
        } else if (isset($model['locked']) && $model['locked'] == 1) { //实时锁定  还需要通知实时监控页面
            $sendAPP['cmd'] = 'lock';
        }

        //发送给app
        if (isset($model['locked'])) {
            $model['locked_notice'] = '0';

            $sendAPP['sn'] = $active['0']['hardware_id'];
            $sendAPP['bossid'] = $active['0']['uid'];
            $djiUser = new DjiUser();  
            $goResult = $djiUser->runGoRealTimeLock($sendAPP); 
            if ($goResult && is_array($goResult) && $goResult['status'] == 0) {               
                $model['locked_notice'] = '1';         
            } 

            //add to the record
            $record = array();
            $record['upper_uid'] = $record['uid'] = $userData['items']['0']['account_info']['user_id'];
            $record['team_id'] = 0;
            $record['operator'] = $userData['items']['0']['account_info']['email'];
            $record['type'] = '植保机拥有者';
            $name = Agroactiveinfo::findOne(array('id'=>$model['id']))->nickname;
            if ($model['locked'] == 0) {
                $record['content'] = '实时解锁飞行器';
                $record['detail'] = '实时解锁飞行器:'.'<i>'.$name.'</i>';
            } else {
                $record['content'] = '实时锁定飞行器';
                $record['detail'] = '实时锁定飞行器:'.'<i>'.$name.'</i>';
            }

            $record['ip'] = $this->get_client_ip();
            Agrorecord::add($record);
        }

        $model['uid'] = $where['uid'];
        $update_set = array();
        $activeInfo = Agroactiveinfo::updateNicknameLocked($model, $update_set);

        if (isset($update_set['nickname'])) {
            $record = array();
            $record['upper_uid'] = $record['uid'] = $userData['items']['0']['account_info']['user_id'];
            $record['team_id'] = 0;
            $record['operator'] = $userData['items']['0']['account_info']['email'];
            $record['type'] = '植保机拥有者';
            $record['content'] = '修改飞行器名称';
            $record['detail'] = '更改 "<i>'.$update_set['nickname'][0].'</i>" 植保机为 <i>'.$update_set['nickname'][1].'"</i>';
            $record['ip'] = $this->get_client_ip();
            Agrorecord::add($record);
        }

        if (isset($update_set['team_id']) && $update_set['team_id'][0] != $update_set['team_id'][1]) {
            $record = array();
            $record['upper_uid'] = $record['uid'] = $userData['items']['0']['account_info']['user_id'];
            $record['team_id'] = 0;
            $record['operator'] = $userData['items']['0']['account_info']['email'];
            $record['type'] = '植保机拥有者';
            $record['content'] = '修改飞行器绑定团队';
            $tmp = Agroteam::findOne(array('id'=>$update_set['team_id'][0]));
            $name1 = '';
            if ($tmp)
                $name1 = $tmp->name;
            $tmp = Agroteam::findOne(array('id'=>$update_set['team_id'][1]));
            $name = '';
            if ($tmp)
                $name2 = $tmp->name;
            $record['detail'] = '将植保机 "<i>'.$active['0']['nickname'].'</i>"'.' 绑定团队: "<i>'.$name1.'"</i> 修改为: <i>'.$name2.'</i>"';
            $record['ip'] = $this->get_client_ip();
            Agrorecord::add($record);
        }

        //添加这架飞机的操作人员 
        
        if (!isset($model['team_id'])) {
            $model['team_id'] = $active['0']['team_id'];
        }

        if($activeInfo && isset($flyers)) {
            $new_operation_accounts = [];

            //移动飞手到一个新的队伍里面去。
            $flyersInfo = agroflyer::findAll(array('upper_uid'=>$where['uid'], 'id'=>$flyers));
            foreach ($flyersInfo as $key => $value) {

                $new_operation_accounts[] = $value->account;

                if ($value->team_id == $model['team_id']) {
                    continue;
                } else {
                    $value->team_id = $model['team_id'];
                    $value->job_level = '0';

                    $value->save();
                }
            }
            $sn['deleted'] = '1';
            $sn['active_id'] = $model['id']; 
            Agroactiveflyer::deleteAllFlyers($sn); //删除所有与此飞机关联的飞手信息
            if($flyers) {
                $this->addaerocraftflyer($sn['active_id'], $flyers); //重新为这架飞机添加新的飞手
            }

            // operation log
            $record = array();
            $record['upper_uid'] = $record['uid'] = $userData['items']['0']['account_info']['user_id'];
            $record['team_id'] = 0;
            $record['operator'] = $userData['items']['0']['account_info']['email'];
            $record['type'] = '植保机拥有者';
            $record['content'] = '修改飞行器操作人员';
            $record['detail'] = '植保机 "<i>'.$active['0']['nickname'].'</i>" 新的操作人员账号为: "<i>'.join(', ', $new_operation_accounts).'</i>"';
            $record['ip'] = $this->get_client_ip();
            Agrorecord::add($record);

            return array('status' => 0, 'status_msg'=> 'ok','message'=>'编辑成功');
        }          
        $list = array();
        $list['status'] = 0;  
        $list['status_msg'] = 'ok';                           
        $list['id'] = $activeInfo;               
        return $list;          
    } 
   
    //添加飞手
    protected function addaerocraftflyer($active_id, $addflyers)
    {
      $model = array();
      $model['active_id'] = $active_id;
      $model['flyer_id'] = $addflyers;

      $status_msg = 'failed';
      $actionName = 'actionAddaerocraftflyer';
      $userData = $this->getUserInfo($actionName);
      if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
          return $userData;
      }
      if (empty($userData['items']['0']['account_info'])) { 
          return array('status' => 1007, 'status_msg'=> $status_msg,'message'=>'权限不足');
      }
      $data = array('data' => array() );
      $list = array();     
      $where = $newItems = $newFlyer = array();     
      $where['uid'] = $userData['items']['0']['account_info']['user_id'];
      $where['deleted'] = '0';
      $where['id'] = $model['active_id'];

      $activeInfo = Agroactiveinfo::getAndEqualWhere($where, 0,1);
      if (!$activeInfo) {
          return array('status' => 1007, 'status_msg'=> $status_msg,'message'=>'权限不足');
      }
      $where = array();
      $where['deleted'] = '0';
      $where['upper_uid'] = $userData['items']['0']['account_info']['user_id'];
      $where['id'] = $model['flyer_id'];
      $flyerInfo = Agroflyer::getAndEqualWhere($where, 0, 0);
      if (!$flyerInfo) {
          return array('status' => 1007, 'status_msg'=> $status_msg,'message'=>'');
      }
      $resultId = array();
      foreach ($flyerInfo as $key => $value) {
        $model['hardware_id'] = $activeInfo['0']['hardware_id'];
        $model['flyer_uid'] = $value['uid']; 
        $model['flyer_id'] = $value['id'];                               
        $activeflyerInfo = Agroactiveflyer::getAndEqualWhere($model, 0,1); 
        if ($activeflyerInfo && is_array($activeflyerInfo)) {
           if ($activeflyerInfo['0']['deleted'] != '0') {
               $where = array('id' => $activeflyerInfo['0']['id'],'deleted' => '0');
               if( Agroactiveflyer::updateDeletedInfo($where) ){
                  $resultId[] = $value['id'];
               }
           } else {
               $resultId[] = $value['id'];
           }
        } else {
          if(Agroactiveflyer::add($model)) {
              $resultId[] = $value['id'];
          }                    
        }           
      }                
      $list = array();
      $list['status'] = 0;  
      $list['status_msg'] = 'ok';                           
      $list['id'] = $resultId;               
      return $list;                             
    } 
  /*
    *  读取团队信息  /apiuser/teaminfo
    *  return {"status":0,"status_msg":"ok","data":{"status":0,"status_msg":"ok","data":{"id":"51","upper_teamid":"0","uid":"23617225210722647","name":"黄伟平的植保队","captain":null,"avatar":null,"showed":"0","deleted":"0","ip":"127.0.0.1","source":"0","ext1":"","ext2":"","updated_at":"2016-10-27 10:22:23","created_at":"2016-10-26 16:54:10"}}
    *  "status":0 和 "status_msg":"ok" 表示创建成功
    * "status":1000 表示参数不合法；"status":1001 表示登录已经过期,请重新登录；"status":1004 表示5秒内重复提交,请稍后重试；
    *  "status":1007 表示权限不足；
    *  "status":1008 表示获取信息失败；
    */
    public function actionTeaminfo()
    {
        if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'user/teaminfo');
        } 
        $model['id'] = Yii::$app->request->get("teamid");
        if(empty($model['id'])) {
            $data = array('status' => 1000, 'status_msg'=> 'failed','data'=> '参数不合法');
            return $data;
        }
        $actionName = 'actionTeaminfo';
        $userData = $this->getUserInfo($actionName);
        if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
            return $userData;
        }
        if (empty($userData['items']['0']['account_info'])) { 
            return array('status' => 1007, 'status_msg'=> 'failed','message'=>'权限不足');
        }
        $status_msg = 'failed';       
        $where = $newData =  $newItems = $newFlyer = array();
        $whereActive['uid'] = $userData['items']['0']['account_info']['user_id'];
        $whereActive['deleted'] = '0';
        $activeInfo = Agroactiveinfo::getAndEqualWhere($whereActive, 0,1);
        if(!$activeInfo) {
            return array('status' => 1007, 'status_msg'=> $status_msg,'message'=>'权限不足');
        }
        $where['team_id'] = $model['id'];
        $where['deleted'] = '0';
        $teaminfo = Agroteam::getFlyerWhere($where, '*'); 
        if(!$teaminfo) {
            return array('status' => 1008, 'status_msg'=> $status_msg,'data'=> '读取失败');
        }
        $teamData = array();
        foreach ($teaminfo as $key => $value) {
            if($value['deleted'] == '0') {
                $teamData[] = $value;
            }
        }
        return array('status' => 0, 'status_msg'=> 'ok','data'=> $teamData);
    }
    /*
    *  读取人员管理页面的团队列表信息  /apiuser/listteam
    *  return {"status":0,"status_msg":"ok","data":{"3":{"id":"3","name":"RM队1"},"2":{"id":"2","name":"RM_testwqew","flyer_info":[{"flyerid":"3","realname":"","job_level":"0","avatar":null},{"flyerid":"2","realname":"","job_level":"0","avatar":null}]},"1":{"id":"1","name":"23617225210722647"}}}
    *  "status":0 和 "status_msg":"ok" 表示创建成功
    * "status":1000 表示参数不合法；"status":1001 表示登录已经过期,请重新登录；"status":1004 表示5秒内重复提交,请稍后重试；
    *  "status":1007 表示权限不足；
    */
    public function actionListteam()
    {
      $actionName = 'actionListteam';
      $userData = $this->getUserInfo($actionName);

      if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
          return $userData;
      }

      $self_uid = $userData['items']['0']['account_info']['user_id'];

      $other_team_ids = Agroflyer::getAllTeamId($self_uid);
      $my_team_ids = Agroteam::getSelfTeamIds($self_uid);
      $all_team_ids = array_merge($other_team_ids, $my_team_ids);

      $unpack_all_team_ids = array();
      foreach ($all_team_ids as $pair) {
          $unpack_all_team_ids[] = $pair['team_id'];
      }

      $unique_all_team_ids = array_flip(array_flip($unpack_all_team_ids));

      $all_team_info = array();
      foreach ($unique_all_team_ids as $team_id) {
          $team_info = Agroteam::getIdData(['id' => $team_id], 'id,name,app_login_limit,uid as upper_uid');
          if (!$team_info || !is_array($team_info) || count($team_info) <= 0) {
              continue;
          }
          $team_info = $team_info[0];

          $flyers = Agroflyer::getTeamWhere2(['team_id' => $team_id, 'deleted' => 0], 'id as flyerid,realname,job_level,avatar,upper_uid,uid');
          for ($i = 0; $i < count($flyers); $i++) {
              $flyers[$i]['avatar'] = UserAvatar::getUserAvatar($flyers[$i]['uid']);
          }

          $team_info['flyer_info'] = $flyers;

          $all_team_info[] = $team_info;
      }

      return array(
          'status' => 0,
          'status_msg' => 'ok',
          'data' => $all_team_info
      );
    }
        /*
    *  读取人员管理页面的团队列表信息  /apiuser/listteam
    *  return {"status":0,"status_msg":"ok","data":{"3":{"id":"3","name":"RM队1"},"2":{"id":"2","name":"RM_testwqew","flyer_info":[{"flyerid":"3","realname":"","job_level":"0","avatar":null},{"flyerid":"2","realname":"","job_level":"0","avatar":null}]},"1":{"id":"1","name":"23617225210722647"}}}
    *  "status":0 和 "status_msg":"ok" 表示创建成功
    * "status":1000 表示参数不合法；"status":1001 表示登录已经过期,请重新登录；"status":1004 表示5秒内重复提交,请稍后重试；
    *  "status":1007 表示权限不足；
    */
    public function actionListmyteam()
    {
        $actionName = 'actionListmyteam';
        $userData = $this->getUserInfo($actionName);

        if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
            return $userData;
        }

        $self_uid = $userData['items']['0']['account_info']['user_id'];
        $my_team_info = array();
        $my_team_ids = Agroteam::getSelfTeamIds($self_uid);
        foreach ($my_team_ids as $team_id) {
            $team_info = Agroteam::getIdData(['id' => $team_id['team_id']], 'id,name');
            if (!$team_info || !is_array($team_info) || count($team_info) <= 0) {
                continue;
            }
            $team_info = $team_info[0];

            $flyers = Agroflyer::getTeamWhere2(['team_id' => $team_id['team_id'], 'deleted' => 0], 'id as flyerid,realname,job_level,avatar,uid');
            for ($i = 0; $i < count($flyers); $i++) {
                $flyers[$i]['avatar'] = UserAvatar::getUserAvatar($flyers[$i]['uid']);
            }

            $team_info['flyer_info'] = $flyers;

            $my_team_info[] = $team_info;
        }

        return array(
            'status' => 0,
            'status_msg' => 'ok',
            'data' => $my_team_info
        );
    }
    /*
    * 添加或者编辑团队  /apiuser/addteam
    *  @parameter string id  团队id  团队id为空表示是添加
    *  @parameter string teamname  团队名称
    *  return {"status":0,"status_msg":"ok","addid":2,"data":{"teamname":"RM_testwqew","id":"2"}}
    *  "status":0 和 "status_msg":"ok" 表示创建成功
    * "status":1000 表示参数不合法；"status":1001 表示登录已经过期,请重新登录；"status":1004 表示5秒内重复提交,请稍后重试；"status":1005 表示已经存在相同的名称；
    *  "status":1006 表示条数超过20条限制；"status":1007 表示权限不足；
    */
    public function actionAddteam()
    {
        $model = array();
        $model['teamname'] = Yii::$app->request->post("teamname");
        $model['app_login_limit'] = Yii::$app->request->post("app_login_limit");
        //如果是修改团队名称，需要传teamid。不传teamid则说明是新建一个team
        $model['id'] = Yii::$app->request->post("id");

        $userData = $this->get_user_info();
        if (!$userData) {
            return ErrorObj::$AUTH_FAILED;
        }

        $self_account = $userData['items']['0']['account_info']['email'];
        $self_uid = $userData['items']['0']['account_info']['user_id'];

        $redata = $model;
        $model['account'] = $self_account;
        $model['uid'] = $self_uid;
        $model['ip'] = $this->get_client_ip();

        // check teamname existing
        if (isset($model['teamname'])) {
            $team_info = Agroteam::findOne(['uid' => $model['uid'], 'deleted' => 0, 'name' => $model['teamname']]);
            if ($team_info) {
                return ['status' => 1005, 'status_msg' => 'failed', 'message' => '已经存在相同的名称'];
            }
        }

        if (isset($model['id'])) {
            // 修改团队信息

            $team_info = Agroteam::findOne(['id' => $model['id'], 'uid' => $model['uid'], 'deleted' => 0]);
            if (!$team_info) {
                return ['status' => 1007, 'status_msg' => 'failed', 'message' => '找不到相应团队'];
            }

            if (isset($model['app_login_limit'])) {
                $i_limit = intval($model['app_login_limit']);
                if ($model['app_login_limit'] < 1 || $model['app_login_limit'] > 7) {
                    $i_limit = 7;
                }
                $model['app_login_limit'] = $i_limit;
            }

            if (isset($model['teamname'])) {
                $model['name'] = $model['teamname'];
            }

            $result = Agroteam::updateInfo($model);
            if ($result <= 0) {
                return array('status' => 1007, 'status_msg' => 'failed', 'message' => '更新失败！');
            }

            //写入操作记录
            if (isset($model['app_login_limit'])) {
                $record['upper_uid'] = $record['uid'] = $model['uid'];
                $record['team_id'] = 0;
                $record['operator'] = $userData['items']['0']['account_info']['email'];
                $record['type'] = '植保机拥有者';
                $record['content'] = '修改团队app登录有效期';
                $oldlimit = $team_info['app_login_limit'];
                $newlimit = $model['app_login_limit'];
                $record['detail'] = "修改有效期\"<i>$oldlimit</i>\"为\"<i>$newlimit</i>\"";
                $record['ip'] = $this->get_client_ip();
                Agrorecord::add($record);
            }

            if (isset($model['name'])) {
                $record['upper_uid'] = $record['uid'] = $model['uid'];
                $record['team_id'] = 0;
                $record['operator'] = $userData['items']['0']['account_info']['email'];
                $record['type'] = '植保机拥有者';
                $record['content'] = '修改团队名称';
                $oldname = $team_info['name'];
                $newname = $model['teamname'];
                $record['detail'] = "修改\"<i>$oldname</i>\"为\"<i>$newname</i>\"";
                $record['ip'] = $this->get_client_ip();
                Agrorecord::add($record);
            }
        } else {
            // 新建团队
            $model['name'] = $model['teamname'];
            $result = Agroteam::add($model); //新建团队
            if ($result->id <= 0) {
                return array('status' => 1007,'status_msg'=> 'failed','message'=>'更新失败！');
            }

            //写入操作记录
            $record['upper_uid'] = $record['uid'] = $model['uid'];
            $record['team_id'] = 0;
            $record['operator'] = $userData['items']['0']['account_info']['email'];
            $record['type'] = '植保机拥有者';
            $record['content'] = '创建团队';
            $newname = $model['teamname'];
            $record['detail'] = "创建\"<i>$newname</i>\"团队";
            $record['ip'] = $this->get_client_ip();
            Agrorecord::add($record);
        }

        $list['status'] = 0;
        $list['status_msg'] = 'ok';
        $list['addid'] = isset($result['id']) ? $result['id'] : '0';
        $list['data'] = $redata;
        return $list;
    }

    /*
    * 删除团队  /apiuser/deletedteam
    *  @parameter string id  团队id  
    *  return {"status":0,"status_msg":"ok","addid":5,"data":{"id":"5"}}
    *  "status":0 和 "status_msg":"ok" 表示创建成功
    * "status":1000 表示参数不合法；"status":1001 表示登录已经过期,请重新登录；"status":1004 表示5秒内重复提交,请稍后重试；
    *  "status":1007 表示权限不足；
    */
    public function actionDeletedteam()
    {
      if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'user/deletedteam');
      }
      $model = array();     
      $model['id'] = Yii::$app->request->post("id");
      $status_msg = 'failed'; 
      if (empty($model['id']) ) {
         $data = array('status' => 1000,'status_msg'=> $status_msg,'message'=>'参数不合法');
         return $data;
      }
      $actionName = 'actionDeletedteam';
      $userData = $this->getUserInfo($actionName);
      if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
          return $userData;
      }
      $data = array('data' => array() );
      $list = array();        
      
      $redata = $model;
      $model['account'] = $userData['items']['0']['account_info']['email'];
      $model['uid'] = $userData['items']['0']['account_info']['user_id'];
      $model['register_phone'] = $userData['items']['0']['account_info']['register_phone'];
      $model['ip'] = $this->get_client_ip();    
     
      $where = array();
      $where['uid'] = $model['uid'];
      $where['id'] = $model['id'];
      $where['deleted'] = '0';
      $teamInfo = Agroteam::getAndEqualWhere($where); 
      if(!$teamInfo) { //被删除的团队是否属于登录的老板 
         $data = array('status' => 1007,'status_msg'=> $status_msg,'message'=>'权限不足！');
         return $data;
      }
      $model['deleted'] = '1';
      $result = Agroteam::updateInfo($model); 
      if($result > 0) { //写入操作记录
        $record['upper_uid'] = $record['uid'] = $model['uid'];
        $record['team_id'] = 0;
        $record['operator'] = $userData['items']['0']['account_info']['email'];
        $record['type'] = '植保机拥有者';
        $record['content'] = '删除团队';
        $teamname = $teamInfo['0']['name'];
        $record['detail'] = "删除\"<i>$teamname</i>\"团队";
        $record['ip'] = $this->get_client_ip();
        Agrorecord::add($record);
      }       
      if ($result && $result > 0) {
         $where = array('team_id' => $model['id']);
         $where['deleted'] = '1';
         $resultFlyer = Agroflyer::changeStatus($where); //需要再更新 agro_active_flyer里面飞手的信息？？
         $where['uid'] = $model['uid'];
         Agroactiveinfo::changeTeamid($where);
         //更新飞手工作信息表
         Agroflyerworkinfo::updateAll(array('deleted' => strip_tags($model['deleted']) ), ['team_id'=> strip_tags($model['id']) ] );
         $list['status'] = 0;
         $list['status_msg'] = 'ok';    
         $list['addid'] = $result;  
         $list['data'] = $redata;               
         return $list;         
      }
      return $userData;
    }
    /*
    * 添加团队里面的人员  /apiuser/addflyer
    *  @parameter string teamid  团队id
    *  @parameter string account dji账号邮箱
    *  @parameter string realname 飞手姓名
    *  return {"status":0,"status_msg":"ok","addid":1,"data":{"team_id":"2","account":"19193213@qq.com","uid":"22410717222020942"}}
    *  "status":0 和 "status_msg":"ok" 表示创建成功
    * "status":1000 表示参数不合法；"status":1001 表示登录已经过期,请重新登录；"status":1004 表示5秒内重复提交,请稍后重试；"status":1005 表示已经存在相同的名称；
    *  "status":1006 表示条数超过20条限制；"status":1007 表示权限不足；"status":1008 已经添加该用户；
    */
    public function actionAddflyer()
    {
      if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'user/addflyer');
      }
      $model = array();   
      $model['team_id'] = Yii::$app->request->post("teamid");  
      $model['account'] = Yii::$app->request->post("account");  
      $model['realname'] = Yii::$app->request->post("realname"); 
      $status_msg = 'failed'; 
      if (empty($model['team_id']) || empty($model['account'])) {
         $data = array('status' => 1000,'status_msg'=> $status_msg,'message'=>'参数不合法');
         return $data;
      }
      $actionName = 'actionAddflyer';
      $userData = $this->getUserInfo($actionName);
      if (!$userData || $userData['status'] != '0' && $userData['status_msg'] != 'ok') {
            return $userData;
      }
      $data = array('data' => array() );
      $list = array(); 
      $langCountry = $this->getCookieCountry('');
      if (file_exists(__DIR__ . '/../messages/'.$langCountry.'/lang.php')) {
         $LANGDATA = require(__DIR__ . '/../messages/'.$langCountry.'/lang.php');
      }
      $DjiAgentUser = new DjiAgentUser();  
      $model['account'] = trim($model['account']);
      if (!$DjiAgentUser->validate_is_email($model['account'])) {
            $data = array('status' => 1011,'status_msg'=> $status_msg,'message'=>$LANGDATA['iuav_incorrect_format']);
            return $data;
      }
      $djiUser = new DjiUser();
      $userInfo = $djiUser->direct_get_user($model['account']);
      $model['uid'] = 0;
      if ($userInfo && $userInfo['status'] == '0' && $userInfo['status_msg'] == 'ok' ) {
           $model['uid'] =  $userInfo['items']['0']['user_id'];
           $model['nickname'] = $userInfo['items']['0']['nickname'];
      }else{
            $data = array('status' => 1012,'status_msg'=> $status_msg,'message'=>$LANGDATA['iuav_not_account']);
            return $data;
      }       
      $redata = $model;   
      $model['upper_uid'] = $userData['items']['0']['account_info']['user_id'];
      $model['ip'] = $this->get_client_ip();   

      $team_info = Agroteam::findOne(array('id'=>$model['team_id']));
      if($team_info->uid != $model['upper_uid'] && $team_info->captain != $model['upper_uid']) { //这个队伍的老板，队长都不是登陆者
          return array('status' => 1007, 'status_msg' => $status_msg, 'message' => '权限不足！');
      }
      if ($team_info->uid == $model['upper_uid'] && $model['uid'] == $model['upper_uid']) {
          // 自己是这个队伍的老板且此时加入的人是自己
          $boss_phone = $this->get_boss_phone($model['upper_uid']);
          if ($boss_phone) {
              $model['phone'] = $boss_phone;
          }
      }

      //通过teamid找到bossid,进而判断该飞手是否已经被加过
      $bossid = Agroteam::findOne(['id'=>$model['team_id'], 'deleted'=>'0']);
      if ($bossid) {
          $model['upper_uid'] = $bossid->uid;
      }

      //判断是否已经添加
      $where = array();
      $where['upper_uid'] = $model['upper_uid'];
      $where['uid'] = $model['uid'];
      $where['deleted'] = '0';
      $teamInfo = Agroflyer::getAndEqualWhere($where, 0,1);   
      if ($teamInfo && is_array($teamInfo)) {
          return array('status' => 1008,'status_msg'=> $status_msg,'message'=>'老板名下已添加该用户');
      }

      $where = array();       
      $where['id'] = $model['team_id'];
      $where['deleted'] = '0';
      $activeInfo = Agroteam::getAndEqualWhere($where, 0,1); //判断这个team是否存在
      if (!$activeInfo ){
         $data = array('status' => 1007,'status_msg'=> $status_msg,'message'=>'权限不足！');
         return $data;
      }
      $where = array();
      $flyerInfo = array();       
      $where['uid'] = $model['upper_uid'];
      $where['deleted'] = '0';
      $activeInfo = Agroactiveinfo::getAndEqualWhere($where, 0,1);
      if (!$activeInfo ){
          $where['team_id'] = $model['team_id']; 
          $fields = 'agro_team.name,agro_team.id,agro_team.uid, agro_flyer.job_level, agro_flyer.realname';
          $flyerInfo = Agroflyer::getTeamWhere($where,$fields,0,-1); 
          if ( $flyerInfo && is_array( $flyerInfo)) {
              $model['upper_uid'] = $flyerInfo['0']['uid'];
              $model['team_id'] = $flyerInfo['0']['id'];             
          }                       
          if(!$flyerInfo || $flyerInfo['0']['job_level'] == 0) {  //队长也可以添加其他人
              $data = array('status' => 1007,'status_msg'=> $status_msg,'message'=>'权限不足,无法添加其他人员');
              return $data;
          }
          $record['upper_uid'] = $model['upper_uid'];
          $record['uid'] = $userData['items']['0']['account_info']['user_id'];
          $record['team_id'] = $model['team_id'];
          $teamname = $flyerInfo['0']['name'];
          $record['type'] = $teamname.'队长';
          $record['operator'] = $flyerInfo['0']['realname'];
      }
      $model['showed'] = '1';              
      $result = Agroflyer::add($model);
      if($result > 0) { //如果该飞手所在团队绑定了某一架飞机，则自动将此飞手绑定到这架飞机上。
          $activeTeamInfo = Agroactiveinfo::getAndEqualWhere(array('team_id' => $model['team_id']));
          if($activeTeamInfo) {
              foreach ($activeTeamInfo as $key => $value) {
                  $this->addaerocraftflyer($value['id'], $result);
              }
          }
      }
      if($result > 0) { //写入操作记录
          $record['content'] = '添加飞手';
          $flyer_count = $model['account'];
          $record['detail'] = "添加飞手账号: \"<i>$flyer_count</i>\"".'到 <i>'.$team_info->name.' 团队</i>';
          if(empty($flyerInfo)) { //如果是老板
              $record['upper_uid'] = $record['uid'] = $userData['items']['0']['account_info']['user_id'];
              $record['team_id'] = 0;
              $record['operator'] = $userData['items']['0']['account_info']['email'];
              $record['type'] = '植保机拥有者';
          }     
          $record['ip'] = $this->get_client_ip();
          Agrorecord::add($record);
      }  
      if ($result && $result > 0) {
         $list['status'] = 0;
         $list['status_msg'] = 'ok';     
         $list['addid'] = $result;  
         $list['data'] = $redata;               
         return $list;         
      }
    }
    /*
    * 删除团队里面的人员  /apiuser/deletedflyer
    *  @parameter string id  人员id  
    *  @parameter string teamid 该飞手所在的团队
    *  return {"status":0,"status_msg":"ok","addid":1,"data":{"id":"1"}}
    *  "status":0 和 "status_msg":"ok" 表示创建成功
    * "status":1000 表示参数不合法；"status":1001 表示登录已经过期,请重新登录；"status":1004 表示5秒内重复提交,请稍后重试；
    *  "status":1007 表示权限不足；
    */
    public function actionDeletedflyer() //需要传teamid,因为如果是队长，则需要判断在这个团队里面是否有删除飞手的权限。
    {
      if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'user/deletedflyer');
      }
      $params = array();
      $params['id'] = Yii::$app->request->post("id");
      $params['team_id'] = Yii::$app->request->post("teamid");
      
      $status_msg = 'failed';

      // check params
      if (empty($params['id']) || empty($params['team_id'])) {
         return array('status' => 1000,'status_msg'=> $status_msg,'message'=>'参数不合法');
      }

      // get user info
      $actionName = 'actionDeletedflyer';
      $userData = $this->getUserInfo($actionName);
      if (!$userData || $userData['status'] != '0' && $userData['status_msg'] != 'ok') {
          return $userData;
      }

      $self_uid = $userData['items']['0']['account_info']['user_id'];

      // check privilege
      $team_info = Agroteam::findOne(array('id'=>$params['team_id']));
      if($team_info->uid != $self_uid && $team_info->captain != $self_uid) {
          // not boss and captain either
          return array('status' => 1007,'status_msg'=> $status_msg,'message'=>'权限不足！');
      }

      $boss_uid = $team_info->uid;

      // check flyer exists
      $where = array();
      $where['id'] = $params['id'];
      $where['team_id'] = $params['team_id'];
      $where['deleted'] = '0';
      $deletedInfo = Agroflyer::getAndEqualWhere($where);
      if(!$deletedInfo) {
         return array('status' => 1007,'status_msg'=> $status_msg,'message'=>'权限不足！');
      }

      // check privilege and record operation log
      $where = array();
      $where['uid'] = $self_uid;
      $where['deleted'] = '0';
      $activeInfo = Agroactiveinfo::getAndEqualWhere($where, 0,1);
      if (!$activeInfo ) {
          $where['team_id'] = $params['team_id'];
          $fields = 'agro_team.name,agro_team.id,agro_team.uid, agro_flyer.job_level, agro_flyer.realname';
          $flyerInfo = Agroflyer::getTeamWhere($where,$fields,0,-1);       
          if (!$flyerInfo || $flyerInfo['0']['job_level'] == '0') {  //队长也可以删除
              return array('status' => 1007,'status_msg'=> $status_msg,'message'=>'权限不足,无法删除其他人员');
          } else if ($deletedInfo['0']['job_level'] == '1') {  //队长没有权限删除队长
              return array('status' => 1007,'status_msg'=> $status_msg,'message'=>'权限不足,无法删除其他人员');
          }

          $record['upper_uid'] = $boss_uid;
          $record['uid'] = $userData['items']['0']['account_info']['user_id'];
          $record['team_id'] = $params['team_id'];
          $teamname = $flyerInfo['0']['name'];
          $record['type'] = $teamname.'队长';
          $record['operator'] = $flyerInfo['0']['realname'];
      }

      // delete flyer and save operation log
      $result = Agroflyer::deletedFlyer(['id' => $params['id'], 'deleted' => '1']);

      if ($result > 0) { //写进操作记录
      	  $record['upper_uid'] = $boss_uid;
          $record['content'] = '删除飞手';
          $flyerid['id'] = $params['id'];
          $flyer_name = Agroflyer::getNameByID($flyerid);
          $team_name = Agroteam::findOne(['id'=>$params['team_id'], 'deleted'=>'0']);
          $record['detail'] = '删除 <i>'.$team_name->name.'</i> 团队 <i>'.$flyer_name.'</i> 飞手';
          if(empty($flyerInfo)) { //如果是老板
              $record['uid'] = $userData['items']['0']['account_info']['user_id'];
              $record['team_id'] = 0;
              $record['operator'] = $userData['items']['0']['account_info']['email'];
              $record['type'] = '植保机拥有者';
          }     
          $record['ip'] = $this->get_client_ip();
          Agrorecord::add($record);
      }
      if ($result && $result > 0) {
         Agroactiveflyer::changeStatus(['flyer_id' => $params['id'], 'deleted' => '1']);
         if($deletedInfo['0']['job_level'] == '1') { //如果删除的是队长，则需要修改team中captain字段
            Agroteam::updateInfo(array('id' => $params['team_id'], 'deleted' => '0', 'captain' => '0'));
         }
         $list['status'] = 0;
         $list['status_msg'] = 'ok';       
         $list['deleteid'] = $result;  
         $list['data'] = $params;
         return $list;       
      }

      return ['status' => 1008, 'status_msg' => 'failed', 'message' => '操作失败'];
    }

    /*
    * 完善团队里面的人员的资料  /apiuser/editflyer
    *  @parameter string id  人员id  
    *  @parameter string realname  真实用户名  
    *  @parameter string idcard  证件号码  
    *  @parameter string phone  手机号码  
    *  @parameter string job_level  工种 0:飞手,1:队长
    *  @parameter string address  地址 
    *  @parameter string teamid  团队id 
    *  return {"status":0,"status_msg":"ok","addid":1,"data":{"id":"1"}}
    *  "status":0 和 "status_msg":"ok" 表示创建成功
    * "status":1000 表示参数不合法；"status":1001 表示登录已经过期,请重新登录；"status":1004 表示5秒内重复提交,请稍后重试；
    *  "status":1007 表示权限不足；
    */
    public function actionEditflyer()
    {
        $model = array();
        $model['id'] = Yii::$app->request->post("id");
        $model['realname'] = Yii::$app->request->post("realname");
        $model['idcard'] = Yii::$app->request->post("idcard");
        $model['phone'] = Yii::$app->request->post("phone");
        $model['job_level'] = Yii::$app->request->post("job_level");
        $model['address'] = Yii::$app->request->post("address");
        $model['company_name'] = Yii::$app->request->post("company_name");

        $status_msg = 'failed';

        if (empty($model['id'])) {
            $data = array('status' => 1000,'status_msg'=> $status_msg,'message'=>'参数不合法');
            return $data;
        }

        // check if id -> db row exist
        $dest_flyer_info = Agroflyer::findOne(['id' => $model['id']]);
        if (!$dest_flyer_info) {
            return array('status' => 1000, 'status_msg' => $status_msg, 'message' => '参数不合法');
        }

        $actionName = 'actionEditflyer';
        $userData = $this->getUserInfo($actionName);
        if (!$userData || $userData['status'] != '0' && $userData['status_msg'] != 'ok') {
            return $userData;
        }

        $self_uid = $userData['items']['0']['account_info']['user_id'];
        $self_email = $userData['items']['0']['account_info']['email'];

        // check privilege
        //   0: normal user
        //   1: team leader
        //   2: boss
        $role = 0;
        $team_leader_info = Agroflyer::findOne([
            'team_id' => $dest_flyer_info['team_id'],
            'job_level' => 1
        ]);
        if ($dest_flyer_info['upper_uid'] === $self_uid) {
            // is boss
            $role = 2;
        } else if ($team_leader_info && ($team_leader_info['uid'] == $self_uid)) {
            // not boss
            // check if is a team leader
            $role = 1;
        }

        if ($role == 0 && ($dest_flyer_info['uid'] != $self_uid)) {
            // is normal user but modify other user's info
            return array('status' => 1007, 'status_msg' => $status_msg, 'message' => '权限不足！');
        }

        // 老板一个月只能修改一次自己的手机
        if (isset($model['phone']) && $role == 2 && $dest_flyer_info['uid'] == $self_uid) {
            $modify_status = $this->actionVerifymodifyphonepermonth();
            if ($modify_status['status'] == 0) {
                $last_modify_time = array();
                $last_modify_time['uid'] = $self_uid;
                $last_modify_time['last_modify_phone_time'] = time();
                LastModifyPhoneTime::add($last_modify_time);
            } else {
                unset($model['phone']);
            }
        }

        if (!isset($model['realname']) && !isset($model['idcard']) && !isset($model['phone'])
            && !isset($model['job_level']) && !isset($model['address']) && !isset($model['company_name'])) {

            // nothing change
            return ['status' => 0, 'status_msg' => 'ok', 'addid' => $model['id'], 'data' => $dest_flyer_info];
        }

        //一个队伍只有一个队长，如果把这个飞手设为队长，则取消另一个队长身份。
        if (isset($model['job_level']) && $model['job_level'] == '1' && $dest_flyer_info['job_level'] == '0') {
            $where = array();
            $where['upper_uid'] = $dest_flyer_info['upper_uid'];
            $where['team_id'] = $dest_flyer_info['team_id'];
            $where['deleted'] = '0';
            $where['job_level'] = '1';
            $jobInfo = Agroflyer::getAndEqualWhere($where);
            if ($jobInfo && is_array($jobInfo)) {
                Agroflyer::updateInfo(array('id' => $jobInfo['0']['id'],'job_level' => '0'));
            }
            // update agro_team captain
            Agroteam::updateInfo(array('id' => $where['team_id'], 'deleted' => '0', 'captain' => $dest_flyer_info['uid']));
        }

        // 取消队长身份
        if (isset($model['job_level']) && $model['job_level'] == '0') {
            //更新agro_team中captain字段
            Agroteam::updateInfo(array('id' => $dest_flyer_info['team_id'], 'deleted' => '0', 'captain' => '0'));
        }

        $result = Agroflyer::updateInfo($model);
        if ($result > 0) { //写入操作记录
            $record = array();
            $record['uid'] = $record['upper_uid'] = $dest_flyer_info['upper_uid'];
            $record['operator'] = $self_email;
            $record['team_id'] = $dest_flyer_info['team_id'];
            if ($role == 2) $record['type'] = '植保机拥有者';
            else if ($role == 1) $record['type'] = '队长';
            else $record['type'] = '飞手';
            $record['content'] = '编辑飞手';
            $details = '';
            if (isset($model['realname'])) { //编辑飞手名称
                $oldval = $dest_flyer_info['realname'];
                $newval = $model['realname'];
                $details = '将飞手名称"<i>'.$oldval.'</i>"修改为<i>"'.$newval.'"</i>';
            }
            if(isset($model['idcard'])) { //编辑飞手idcard
                $oldval = $dest_flyer_info['idcard'];
                $newval = $model['idcard'];
                $details .= '设置飞手身份证"<i>'.$oldval.'</i>"修改为<i>"'.$newval.'"</i>';
            }
            if(isset($model['phone'])) { //编辑飞手手机号
                $oldval = $dest_flyer_info['phone'];
                $newval = $model['phone'];
                $details .= '设置飞手手机号"<i>'.$oldval.'</i>"修改为<i>"'.$newval.'"</i>';
            }
            if (isset($model['job_level'])) {
                if ($model['job_level'] == $dest_flyer_info['job_level']) {
                    $details .= '工种没有发生变化';
                } else if ($model['job_level'] == '1') {
                    $details .= '将飞手设置为"<i>队长</i>"';
                } else {
                    $details .= '将队长设置为"<i>飞手</i>"';
                }
            }
            $record['detail'] = $details;
            $record['ip'] = $this->get_client_ip();
            Agrorecord::add($record);
        }

        return array(
            'status' => 0,
            'status_msg' => 'ok',
            'addid' => $result,
            'data' => $model
        );
    }

    /*
    *  查询飞手个人信息  /apiuser/flyerinfo
    *  @parameter string id  人员id  
    *  return {"status":0,"status_msg":"ok","data":{"id":"3","team_id":"2","upper_uid":"23617225210722647","account":"19193213@qq.com","uid":"22410717222020942","realname":"1121","idcard":"","phone":"","avatar":null,"job_level":null,"address":"","showed":"0","deleted":"0","operator":null,"ip":"127.0.0.1","source":"0","ext1":"","ext2":"","updated_at":"2016-09-07 17:00:39","created_at":"2016-09-05 19:04:37","all_time":"0","all_area":"0.00","all_times":"0"}}
    *  "status":0 和 "status_msg":"ok" 表示创建成功
    * "status":1000 表示参数不合法；"status":1001 表示登录已经过期,请重新登录；"status":1004 表示5秒内重复提交,请稍后重试；
    *  "status":1007 表示权限不足；
    */
    public function actionFlyerinfo()
    {
      $model = array();
      $model['id'] = Yii::$app->request->post("id");

      $status_msg = 'failed';

      if (empty($model['id']) ) {
         return array('status' => 1000,'status_msg'=> $status_msg,'message'=>'参数不合法');
      }

      $actionName = 'actionFlyerinfo';
      $userData = $this->getUserInfo($actionName);
      if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
          return $userData;
      }

      $whereInfo['deleted'] = '0';
      $userID = $userData['items']['0']['account_info']['user_id'];
      $flyerInfo = Agroflyer::findOne(array('id'=>$model['id']));
    
      $whereInfo['uid'] = $flyerInfo->uid;
      if($flyerInfo->uid != $userID) { //查看的对象不是自己
          if($userID == $flyerInfo->upper_uid) { //这个被查看的飞手老板是不是我？
              $whereInfo['upper_uid'] = $userID; 
          } else { //这个被查看的飞手的队长是不是我？
              $teamInfo = Agroteam::findOne(array('id'=>$flyerInfo->team_id));
              if($teamInfo && $teamInfo->captain == $userID) { //队长
                  $whereInfo['team_id'] = $flyerInfo->team_id; 
              } else { //普通飞手
                  return array('status' => 1007,'status_msg'=> $status_msg,'message'=>'权限不足！');
              }
          } 
      }
      $workInfo = Agroflyerworkinfo::getAndEqualWhere($whereInfo, 'sum(all_time) as time, sum(all_area) as area, sum(all_times) as times');
      $data = array('data' => array() );       
      $where = array();
      $where['id'] = $model['id'];
      $where['deleted'] = '0';
      $teamInfo = Agroflyer::getAndEqualWhere($where); 
      if(!$teamInfo) {
          return array('status'=>0, 'status_msg'=>'ok', 'data'=>'');
      }
      $list = array();
      $list['status'] = 0;
      $list['status_msg'] = 'ok';
      if($workInfo) {
          $teamInfo['0']['all_time'] = $workInfo['0']['time'];
          $teamInfo['0']['all_area'] = $workInfo['0']['area'];
          $teamInfo['0']['all_times'] = $workInfo['0']['times']; 
      } else {
          $teamInfo['0']['all_time'] = $teamInfo['0']['all_area'] = $teamInfo['0']['all_times'] = '0';
      }
      $teamInfo['0']['avatar'] = UserAvatar::getUserAvatar($teamInfo['0']['uid']);
      $list['data'] = $teamInfo['0'];  
      return $list;     
    }

    /*
    *  锁定或者解锁飞机  /apiuser/lockaerocraft 
    *  @parameter string hardware_id 硬件id   
    *  @parameter string locked  0:解锁，1:锁定    
    *  参数用POST方式
    * {"status":0,"status_msg":"ok"}
    *  "status":0 和 "status_msg":"ok" 表示创建成功
    * "status":1000 表示参数不合法；"status":1001 表示登录已经过期,请重新登录；"status":1004 表示5秒内重复提交,请稍后重试；
    *  "status":1007 表示权限不足；
    */
    public function actionLockaerocraft()
    {
      if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'user/editaerocraft');
      }
      $model = array();
      $model['hardware_id'] = Yii::$app->request->post("hardware_id");
      $model['locked'] = intval(Yii::$app->request->post("locked"));     
      $status_msg = 'failed';
      if (empty($model['hardware_id']) ) {
         $data = array('status' => 1000, 'status_msg'=> $status_msg,'message'=>'参数不合法');
         return $data;
      }
      $actionName = 'actionLockaerocraft';
      $userData = $this->getUserInfo($actionName);
      $data = array('data' => array() );
      $list = array();     
       if ($userData && $userData['status'] == '0' && $userData['status_msg'] == 'ok') {              
        $where = $newItems = $newFlyer = array();
        if (isset($userData['items']['0']['account_info'])) {
          $where['uid'] = $userData['items']['0']['account_info']['user_id'];
          $where['deleted'] = '0';
          $where['hardware_id'] = $model['hardware_id'];
          $activeInfo = Agroactiveinfo::getAndEqualWhere($where,0,1,'id',1,'id');
          if ($activeInfo && is_array($activeInfo) && $activeInfo['0']['id'] > 0) {
              $cmd = $model['locked']=='1' ? 'freeze' : 'unfreeze';
              $djiUser = new DjiUser(); 
              $goResult = $djiUser->runGoCommand($model['hardware_id'],$cmd);
              if ($goResult && is_array($goResult) && $goResult['status'] == 0 && $goResult['sn'] == $model['hardware_id']) {               
                 $model['locked_notice'] = 1;             
              }else{
                 $model['locked_notice'] = 0;
              }
              $model['uid'] = $where['uid'];
              $model['id'] = $activeInfo['0']['id'];             
              $activeInfo = Agroactiveinfo::updateNicknameLocked($model); 
              $list = array();
              $list['status'] = 0;  
              $list['status_msg'] = 'ok';                           
              $list['id'] = $activeInfo;
              $list['is_notice'] = $model['locked_notice'];              
              return $list;                   
             
          }                  
        }
        $data = array('status' => 1007, 'status_msg'=> $status_msg,'message'=>'权限不足');
        return $data;
      }
      return $userData;
    }  

    /*
    *  查看飞行记录  /apiuser/flight
    *  @parameter string page  页面 
    *  @parameter string size  每页数目 
    *  @parameter string start_date 开始日期 20160901
    *  @parameter string end_date 结束日期 20160902
    *  @parameter string flyer_name 飞手名称
    *  @parameter string hardware_name 飞行器名称
    *  @parameter string team_name 团队名称
    *  @parameter string location 作业地点
    *  @parameter string teamid 当前登录用户的团队id
    *  return {"status":0,"status_msg":"ok","data":[{"id":"9","flight_data_id":"3","upper_uid":"23617225210722647","uid":"23617225210722647","team_id":"0","version":"1","timestamp":"1473763277970","longi":"113.95890260613","lati":"22.542815535026","location":"广东省深圳市南山区粤海街道新村路大冲阮屋村","product_sn":"test26","session_num":"1473763277970","farm_delta_y":"0","flight_version":"3.2.10.255","plant":"0","work_area":"0","work_time":"0","start_end":"09:19:30-09:19:30","ext1":null,"ext2":null,"deleted":"0","create_date":"486710913","updated_at":"2016-09-14 08:55:27","created_at":"2016-09-14 08:55:27","nickname":"1232","team_name":"","flyer_name":""}]}
    *  "status":0 和 "status_msg":"ok" 表示成功
    * "status":1000 表示参数不合法；"status":1001 表示登录已经过期,请重新登录；"status":1004 表示5秒内重复提交,请稍后重试；
    *  "status":1007 表示权限不足；
    */
    public function actionFlight()
    {                                                     
      $start_date = $this->getPostValue("start_date");
      $end_date = $this->getPostValue("end_date"); 
      $flyer_name = $this->getPostValue("flyer_name"); 
      $hardware_name = $this->getPostValue("hardware_name"); 
      $team_name = $this->getPostValue("team_name");
      $location = $this->getPostValue("location"); 
      $page = intval($this->getPostValue("page"));
      $size = intval($this->getPostValue("size"));
      $order = $this->getPostValue("order"); 
      $updown = $this->getPostValue("updown"); 
      $selectAll = $this->getPostValue("selectAll");
      $fuzzy_string = $this->getPostValue("fuzzy_string");

      if (isset($order) && ($order == 'work_area')) {
          $order = 'new_work_area';
      }

      if (isset($fuzzy_string)) {
          // if set fuzzy string, extend it
          $flyer_name = $fuzzy_string;
          $hardware_name = $fuzzy_string;
          $team_name = $fuzzy_string;
          $location = $fuzzy_string;
      }

      if ($size < 1 || $size > 100 ) {
          $size = 30;
      }
      if ($page < 1 ) {
          $page = 1;
      }
      $start = ($page-1) * $size;
      $start = $start < 0 ? 0 : $start;
      if($selectAll) {
        $start = 0;
        $size = 0;
      }
      $status_msg = 'failed';     
      $actionName = 'actionFlight';
      $userData = $this->getUserInfo($actionName);
      if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
          return $userData;
      }
      if (empty($userData['items']['0']['account_info'])) { 
          return array('status' => 1007, 'status_msg'=> $status_msg,'message'=>'权限不足');
      } 
      if (empty(Yii::$app->request->cookies['_meta_key'])) {
          return array('status' => 1001,'status_msg'=> $status_msg,'message'=>'登录已经过期,请重新登录');
      }

      $model = array();
      $where = array();
      $where['uid'] = $userData['items']['0']['account_info']['user_id'];
      $where['deleted'] = '0';
      $model['upper_uid'] = $where['uid']; //作为老板的           
      $model['uid'] =  $where['uid'];//作为飞手的 
      $teamInfo = Agroteam::getAndEqualWhere(array('captain'=>$where['uid'], 'deleted'=>'0')); 
      if($teamInfo && is_array($teamInfo)) { //作为队长
          foreach ($teamInfo as $key => $value) {
              $model['team_id'][] = $value['id']; //团队可能有多个
          }
      }                        
      if ($flyer_name) {
          $model['flyer_name'] =  $flyer_name;
      }
      if ($hardware_name) {
          $model['nickname'] =  $hardware_name;
      }
      if($order == "start_end") { //如果是按照时间来排序，就将其转化成
          $order = "session_num";
      }
      if ($team_name) {
          $model['team_name'] =  $team_name;
      }
      if ($location) {
          $model['location'] =  $location;
      }
      if ($start_date) {
          $model['start_date'] =  $start_date;
      }
      if ($end_date) {
          $model['end_date'] =  $end_date;
      }
      $model['order'] = $order;
      $model['updown'] = $updown;
      $subQuery = Agroflight::getFlightSubQuery($model);
      $countSum = Agroflight::getWhereFlightCount($model, $subQuery);
      if (empty($countSum) || $countSum['0']['flight_count'] == 0) {
          return array('status'=>0, 'count'=>0, 'status_msg'=>'ok', 'data'=>array()); 
      }
      if ($selectAll) { //如果全选，返回所有选中的id
          $fields = "agro_flight.id";
          $flightData = Agroflight::getActiveWhere($model, $subQuery, $fields, $start, $size); //var_dump($flightData);die;
          return array('status' => 0, 'status_msg'=> 'ok', 'data' => $flightData);
      }
      $fields = "agro_flight.*";
      $flightData = Agroflight::getActiveWhere($model, $subQuery, $fields, $start, $size);
        $ret = [];
        foreach ($flightData as $item) {
            $_ti = $item;
            if ($_ti['fixed_tag'] == '1') {
                $_ti['work_area'] = $_ti['new_work_area'] > 0 ? $_ti['new_work_area'] : 0;
            } else {
                $_ti['work_area'] = 0;
            }

            $ret[] = $_ti;
        }

      $list = array();
      $list['status'] = 0;
      $list['count'] = $countSum['0']['flight_count'];
      $list['status_msg'] = 'ok';                
      $list['data'] = $ret;
      return $list;
    }

    /*
    *  查看飞行记录总面积，总时长，总次数  /apiuser/flightcount 
    *  @parameter string start_date 开始日期 20160901
    *  @parameter string end_date 结束日期 20160902
    *  @parameter string flyer_name 飞手名称
    *  @parameter string hardware_name 飞行器名称
    *  @parameter string team_name 团队名称
    *  @parameter string location 作业地点
    *  @parameter string teamid 当前登录用户的团队id
    *  return {"status":0,"status_msg":"ok","data":[]}
    *  "status":0 和 "status_msg":"ok" 表示成功
    * "status":1000 表示参数不合法；"status":1001 表示登录已经过期,请重新登录；"status":1004 表示5秒内重复提交,请稍后重试；
    *  "status":1007 表示权限不足；
    */
    public function actionFlightcount()
    {
      $start_date = $this->getPostValue("start_date");
      $end_date = $this->getPostValue("end_date"); 
      $flyer_name = $this->getPostValue("flyer_name"); 
      $hardware_name = $this->getPostValue("hardware_name"); 
      $team_name = $this->getPostValue("team_name");
      $location = $this->getPostValue("location");
      $fuzzy_string = $this->getPostValue('fuzzy_string');

      if (isset($fuzzy_string)) {
          // if fuzzy string set, extend it
          $flyer_name = $fuzzy_string;
          $hardware_name = $fuzzy_string;
          $team_name = $fuzzy_string;
          $location = $fuzzy_string;
      }
  
      $userData = $this->get_user_info();
      if (!$userData) {
          return ErrorObj::$AUTH_FAILED;
      }

      $model = array();
      $where = $newItems = $newFlyer = array();
      $where['uid'] = $userData['items']['0']['account_info']['user_id'];
      $where['deleted'] = '0';
      $model['upper_uid'] = $where['uid']; //作为老板的           
      $model['uid'] =  $where['uid'];//作为飞手的 
      $teamInfo = Agroteam::getAndEqualWhere(array('captain'=>$where['uid'], 'deleted'=>'0')); 
      if($teamInfo && is_array($teamInfo)) { //作为队长
          foreach ($teamInfo as $key => $value) {
              $model['team_id'][] = $value['id']; //团队可能有多个
          }
      }               
      if ($flyer_name) {
          $model['flyer_name'] = $flyer_name;
      }
      if ($hardware_name) {
          $model['nickname'] = $hardware_name;
      }
      if ($team_name) {
          $model['team_name'] = $team_name;
      }
      if ($location) {
          $model['location'] = $location;
      }
      if ($start_date) {
          $model['start_date'] = $start_date;
      }
      if ($end_date) {
          $model['end_date'] = $end_date;
      }
      $subQuery = Agroflight::getFlightSubQuery($model);
      $field = 'agro_flight.*';
      $countSum = Agroflight::getActiveWhere($model, $subQuery, $field);//, 'count(id) as flight_count,sum(work_area) as sum_area,sum(work_time) as sum_time');
      $data = [
          'count' => 0,
          'sum_area' => 0,
          'sum_time' => 0
      ];
      for ($i = 0; $i < count($countSum); $i++) {
          $data['count']++;
          if ($countSum[$i]['fixed_tag'] == '1') {
              $data['sum_area'] += $countSum[$i]['new_work_area'];
              $data['sum_time'] += $countSum[$i]['work_time'];
          } else {
              //$data['sum_area'] += $countSum[$i]['work_area'];
              $data['sum_time'] += $countSum[$i]['work_time'];
          }
      };

      $list = array();
      $list['status'] = 0;
      $list['count'] = $data['count'];
      $list['sum_area'] = $data['sum_area'];
      $list['sum_time'] = $data['sum_time'];
      $list['status_msg'] = 'ok';             
      return $list;
    }  

    /*
    *  查看单个飞行记录详情  /apiuser/flightinfo
    *  @parameter string session_num  iuav_flight_data表对应session_num
    *  @parameter string product_sn  iuav_flight_data表对应product_sn
    *  return {"status":0,"status_msg":"ok","data":[{"id":"1","user_id":"23617225210722647","team_id":"0","version":"1","timestamp":"1473763277970","longi":"113.95890260613","lati":"22.542815535026","alti":"0","product_sn":"test26","spray_flag":"0","motor_status":"0","radar_height":"0","velocity_x":"0","velocity_y":"0","farm_delta_y":"0","farm_mode":"2","pilot_num":"0","session_num":"1473763277970","frame_index":"1","flight_version":"3.2.10.255","plant":"0","create_time":"1473792419","work_area":"0","ext1":"","ext2":"0","upper_uid":"23617225210722647"},{"id":"2","user_id":"23617225210722647","team_id":"0","version":"1","timestamp":"1473763298880","longi":"113.95890260613","lati":"22.542815535026","alti":"0","product_sn":"test26","spray_flag":"0","motor_status":"0","radar_height":"0","velocity_x":"0","velocity_y":"0","farm_delta_y":"0","farm_mode":"2","pilot_num":"0","session_num":"1473763277970","frame_index":"1","flight_version":"3.2.10.255","plant":"0","create_time":"1473794414","work_area":"0","ext1":"","ext2":"0","upper_uid":"23617225210722647"}]}
    *  "status":0 和 "status_msg":"ok" 表示成功
    * "status":1000 表示参数不合法；"status":1001 表示登录已经过期,请重新登录；"status":1004 表示5秒内重复提交,请稍后重试；
    *  "status":1007 表示权限不足；
    */
    public function actionFlightinfo()
    {
      if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'user/flightinfo');
      }
      $session_num = $this->getPostValue("session_num");
      $product_sn = $this->getPostValue("product_sn");
      $status_msg = 'failed'; 
      if (empty($session_num) || empty($product_sn) ) {
         $data = array('status' => 1000,'status_msg'=> $status_msg,'message'=>'参数不合法');
         return $data;
      }   
      if (isset(Yii::$app->request->cookies['_meta_key'])) {
        $meta_key = Yii::$app->request->cookies['_meta_key']->value;   
        $returnKey = __CLASS__.__FUNCTION__."_flightinfo_".md5($session_num.$product_sn.$meta_key);   
        $returnData = Yii::$app->cache->get($returnKey);
        if ( $returnData ) {
            return $returnData;
        } 
      }     
      $model = array('session_num' => $session_num,'product_sn' => $product_sn);         
      $status_msg = 'failed';     
      $actionName = 'actionFlightinfo';
      $userData = $this->getUserInfo($actionName);
      if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
          return $userData;
      }
      if (empty($userData['items']['0']['account_info'])) { 
          return array('status' => 1007, 'status_msg'=> $status_msg,'message'=>'权限不足');
      }
      $data = array('data' => array() );
      $list = array();          
      $fields = "*";
      $flightData = Iuavflightdata::getAndEqualWhere($model,0,-1,'timestamp',-1);

      $list = array();
      $list['status'] = 0;  
      $list['status_msg'] = 'ok';                
      $list['data'] = $flightData;  
      if (isset($returnKey) && $returnKey) {
          Yii::$app->cache->set($returnKey, $list, 180);  
      }        
      return $list;           
    } 

    /*
    *  查看作业计划任务记录  /apiuser/listtask
    *  @parameter teamid 团队id
    *  @parameter string page  页面 
    *  @parameter string size  每页数目 
    *  @parameter string start_date 开始日期 20160901
    *  @parameter string end_date 结束日期 20160902
    *  @parameter string name 任务名称
    *  @parameter string order 排序字段
    *  @parameter string updown 升序还是降序
    *  return {"status":0,"status_msg":"ok","data":[{"id":"4","name":"Qv","date":null,"time":"0","area":null,"type":null,"crop":null,"crop_stage":null,"prevent":null,"lat":"0.000000","lng":"0.000000","location":null,"battery_times":"0","interval":null}]}
    *  "status":0 和 "status_msg":"ok" 表示成功
    * "status":1000 表示参数不合法；"status":1001 表示登录已经过期,请重新登录；"status":1004 表示5秒内重复提交,请稍后重试；
    *  "status":1007 表示权限不足；
    */
    public function actionListtask()
    {
      if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'user/listtask');
      }
      //$teamid = $this->getPostValue("teamid"); 
      $start_date = $this->getPostValue("start_date"); 
      $end_date = $this->getPostValue("end_date");   
      $name = $this->getPostValue("name"); 
      $location = $this->getPostValue("location");    
      $page = intval($this->getPostValue("page"));
      $size = intval($this->getPostValue("size"));
      $order = $this->getPostValue("order"); //$order = "area";//按照对应的参数来排序
      $updown = $this->getPostValue("updown"); //$updown = "1";
      $act = $this->getPostValue("act");
      $fuzzy_string = $this->getPostValue("fuzzy_string");

      if ($size < 1 || $size > 100 ) {
          $size = 30;
      }
      if ($page < 1 ) {
          $page = 1;
      }
      $start = ($page-1) * $size;
      $start = $start < 0 ? 0 : $start;
/*      if (isset(Yii::$app->request->cookies['_meta_key'])) {
        $meta_key = Yii::$app->request->cookies['_meta_key']->value;
        $paramsString = $start_date.$end_date.$name.$location.$page.$size.$order.$updown;
        $returnKey = __CLASS__.__FUNCTION__."_data_".md5($paramsString.$meta_key);   
        $returnData = Yii::$app->cache->get($returnKey);
        if ( $returnData ) {
              return $returnData;
        } 
      }*/

      if (isset($fuzzy_string)) {
          // if fuzzy string set, extend it
          $name = $fuzzy_string;
          $location = $fuzzy_string;
      }

      $model = array();         
      $status_msg = 'failed';     
      $actionName = 'actionListtask';
      $userData = $this->getUserInfo($actionName);
      if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
          return $userData;
      }
      if (empty($userData['items']['0']['account_info'])) { 
          return array('status' => 1007, 'status_msg'=> $status_msg,'message'=>'权限不足');
      }
      $data = array('data' => array() );   
      $where = array();
      $where['uid'] = $userData['items']['0']['account_info']['user_id'];
      $where['deleted'] = '0';
      $model['upper_uid'] = $where['uid']; //作为老板的           
      $model['uid'] =  $where['uid'];//作为飞手的 
      $teamInfo = Agroteam::getAndEqualWhere(array('captain'=>$where['uid'], 'deleted'=>'0')); 
      if($teamInfo && is_array($teamInfo)) { //作为队长的
          foreach ($teamInfo as $key => $value) {
              $model['team_id'][] = $value['id']; //团队可能有多个
          }
      }                  
      if ($name) {
        $model['name'] = $name;
      }
      if ($location) {
        $model['location'] = $location;
      }
      if ($start_date) {
        $model['starttime'] = strtotime(substr($start_date, 0,4).'-'.substr($start_date, 4,2).'-'.substr($start_date, 6,2)." 00:00:00");
      }
      if ($end_date) {
        $model['endtime'] = strtotime(substr($end_date, 0,4).'-'.substr($end_date, 4,2).'-'.substr($end_date, 6,2)." 23:59:59");
      }
      $model['deleted'] = '0';
      $model['order'] = $order;
      $model['updown'] = $updown;
      $fields = '*';
      $subQuery = Agrotask::getTaskSubQuery($model);
      $taskInfo = Agrotask::getTasksWhere($model, $subQuery, $fields, $start, $size);
      if(!$taskInfo) {
          return array('status' => 0, 'status_msg'=> 'ok', 'data' => []);
      } 
      foreach ($taskInfo as $k => $v) {
          $taskInfo[$k]['key_point'] = $this->stringToArray($v['key_point']);

          $edge_point = $this->stringTo2Array($v['edge_point']);
          $edge_result = array();
          if(!empty($edge_point) && is_array($edge_point)){
              foreach ($edge_point as $key => $value) {
                  if(empty($value)) continue;
                  $array_tmp = $value[0][1];
                  array_splice($value, 0, 1);
                  if(($array_tmp & 0x0f) == 1) { //边缘航点
                      $edge_result['border_point'] = $value;
                  } else if(($array_tmp & 0xf00) == 0x400) {  //圆形
                      $edge_result['obstacle_circle'] = $value;
                  } else {  //多边形
                      $edge_result['obstacle_polygon'] = $value;
                  }
              }
          }
          $taskInfo[$k]['edge_point'] = $edge_result;

          $way_point = $this->stringTo2Array($v['way_point']);
          $way_result = array();
          if(!empty($way_point) && is_array($way_point)){
              foreach ($way_point as $key => $value) {
                  if(empty($value)) continue;
                  $array_tmp = $value[0][1];
                  array_splice($value, 0, 1);
                  if(($array_tmp & 0x0f) == 1) { //边缘航点
                      $way_result['border_point'] = $value;
                  } else if(($array_tmp & 0xf00) == 0x400) {  //圆形
                      $way_result['obstacle_circle'] = $value;
                  } else {  //多边形
                      $way_result['obstacle_polygon'] = $value;
                  }
              }
          }
          $taskInfo[$k]['way_point'] = $way_result;

          //add the calibrate_point
          $taskInfo[$k]['calibrate_point'] = $this->stringToArray($v['calibrate_point']);
          //last_spraying_break_point
          $taskInfo[$k]['last_spraying_break_point'] = $this->stringToArray($v['last_spraying_break_point']);
      
          $taskInfo[$k]['plan_edge_poit'] = $this->stringToArray($v['plan_edge_poit']);
          $taskInfo[$k]['obstacle_point'] = $this->stringTo2Array($v['obstacle_point']);
      }
      $list = array();
      $list['status'] = 0;  
      $list['status_msg'] = 'ok';  
      $list['count'] = Agrotask::getTasksWhereCount($model, $subQuery, $fields, $start, $size);             
      $list['data'] = $taskInfo;
/*          if (isset($returnKey) && $returnKey) {
        Yii::$app->cache->set($returnKey, $list, 120);  //
      } */ 
      return $list;                   
    }

    public function actionDeletetask() {
        $userData = $this->getUserInfo('actionDeleteTask');
        if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
            return $userData;
        }

        $self_uid = $userData['items']['0']['account_info']['user_id'];

        $task_id = $this->getPostValue('task_id');
        if (!isset($task_id)) {
            return ['status' => 1001, 'status_msg' => 'failed', 'message' => '参数错误'];
        }

        // check if this task belong to self
        // ONLY boss can delete task
        $row = Agrotask::findOne(['id' => $task_id, 'upper_uid' => $self_uid]);
        if (!$row) {
            return ['status' => 1002, 'status_msg' => 'failed', 'message' => '权限不足'];
        }

        $row->deleted = 1;
        $row->save();

        return ['status' => 1001, 'status_msg' => 'ok'];
    }

    protected function stringToArray($s) {
        if($s == null)
            return null;
        $a = array();
        $str = ltrim($s, "L");
        $a = explode("#", $str);
        $result = array();
        foreach($a as $k => $v) {
          if($v == null) continue;
          $data = explode(",", $v);
          if (count($data) < 2 ) continue; 
          $result[$k][] = floatval($data[0]);
          $result[$k][] = floatval($data[1]); 
        }
        return $result;
    }
    protected function stringTo2Array($s) {
        if($s == null)
            return null;
        $a = array();
        $str = ltrim($s, "LL");
        $a = explode("##", $str);
        $result = array();
        foreach($a as $k => $v) {
            if($v == null)
                continue;
            $result[] = $this->stringToArray($v);  
        }
        return $result;
    }
    /*
    *  查看单个作业计划任务详情  /apiuser/taskinfo
    *  @parameter teamid 团队id
    *  @parameter taskid task表id
    *  @parameter act 如果是下载down
    *  return {"status":0,"status_msg":"ok","data":[{"id":"4","upper_uid":"23617225210722647","uid":"772985517407567872","team_id":"2","name":"Qv","date":null,"time":"0","type":null,"crop":null,"crop_stage":null,"prevent":null,"setting":null,"key_point":null,"home":null,"obstacle_point":null,"plan_edge_poit":null,"edge_poit":null,"way_point":null,"lat":"0.000000","lng":"0.000000","location":null,"battery_times":"0","interval":null,"app_type":null,"deleted":"0","operator":null,"ip":"","source":"0","ext1":"","ext2":"","updated_at":"2016-09-09 19:28:41","created_at":"2016-09-09 19:28:41","area":null}]}
    *  "status":0 和 "status_msg":"ok" 表示成功
    * "status":1000 表示参数不合法；"status":1001 表示登录已经过期,请重新登录；"status":1004 表示5秒内重复提交,请稍后重试；
    *  "status":1007 表示权限不足；
    */
    public function actionTaskinfo()
    {
      //$teamid = $this->getPostValue("teamid");
      $taskid = $this->getPostValue("taskid");  
      $act = $this->getPostValue("act");
      $status_msg = 'failed'; 
      if (empty($taskid)) {
         $data = array('status' => 1000,'status_msg'=> $status_msg,'message'=>'参数不合法');
         return $data;
      }      
      if (isset(Yii::$app->request->cookies['_meta_key'])) {
        $meta_key = Yii::$app->request->cookies['_meta_key']->value;
        $paramsString = 'taskinfo'.$taskid;
        $returnKey = __CLASS__.__FUNCTION__."_data_".md5($paramsString.$meta_key);   
        $returnData = Yii::$app->cache->get($returnKey); 
        if ( $returnData ) {
            if ($act == 'down' && $returnData['status'] == '0' && $returnData['status_msg'] == 'ok') {
                $task_name = Agrotask::getNameByID(['taskid' => $taskid]);
                $this->downTask($task_name, $returnData['data']);
            }
            return $returnData;
        } 
      }

      $model = array();      
      $actionName = 'actionTaskinfo';
      $userData = $this->getUserInfo($actionName);
      if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
          return $userData;
      }
      if (empty($userData['items']['0']['account_info'])) { 
          return array('status' => 1007, 'status_msg'=> $status_msg,'message'=>'权限不足');
      }
      $model['id'] = $taskid;
      $model['deleted'] = '0';
      $taskInfo = Agrotask::findOne($model);

      // query operator
      $flyer_info = Agroflyer::findOne(['team_id' => $taskInfo->team_id, 'uid' => $taskInfo->uid, 'deleted' => 0]);
      if ($flyer_info) {
          if (!empty($flyer_info->realname)) {
              $taskInfo['operator'] = $flyer_info->realname;
          } else {
              $taskInfo['operator'] = $flyer_info->account;
          }
      }

      $list = array();
      $list['status'] = 0;  
      $list['status_msg'] = 'ok';  
      //如果act为down时，则不用转换
      if($taskInfo && $act != 'down'){
          $taskInfo['key_point'] = $this->stringToArray($taskInfo['key_point']);

          $edge_point = $this->stringTo2Array($taskInfo['edge_point']);
          $edge_result = array();
          if(!empty($edge_point) && is_array($edge_point)) {
              foreach ($edge_point as $key => $value) {
                  $array_tmp = $value[0][1]; 
                  array_splice($value, 0, 1);
                  if(($array_tmp & 0x0f) == 1) { //边缘航点
                      $edge_result['border_point'] = $value;
                  } else if(($array_tmp & 0xf00) == 0x400) {  //圆形
                      $edge_result['obstacle_circle'] = $value;
                  } else {  //多边形
                      $edge_result['obstacle_polygon'] = $value;
                  }
              }
          }
          $taskInfo['edge_point'] = $edge_result;

          $way_point = $this->stringTo2Array($taskInfo['way_point']);
          $way_result = array();
          if(!empty($way_point) && is_array($way_point)) {
              foreach ($way_point as $key => $value) {
                  $array_tmp = $value[0][1]; 
                  array_splice($value, 0, 1);
                  if(($array_tmp & 0x0f) == 1) { //边缘航点
                      $way_result['border_point'] = $value;
                  } else if(($array_tmp & 0xf00) == 0x400) {  //圆形
                      $way_result['obstacle_circle'] = $value;
                  } else {  //多边形
                      $way_result['obstacle_polygon'] = $value;
                  }
              }
          }
          $taskInfo['way_point'] = $way_result;

          //add the calibrate_point
          $taskInfo['calibrate_point'] = $this->stringToArray($taskInfo['calibrate_point']);
          //add last_spraying_break_point
          $taskInfo['last_spraying_break_point'] = $this->stringToArray($taskInfo['last_spraying_break_point']);

          $taskInfo['plan_edge_poit'] = $this->stringToArray($taskInfo['plan_edge_poit']);
          $taskInfo['obstacle_point'] = $this->stringTo2Array($taskInfo['obstacle_point']);
      }
    
      $list['data'] = $taskInfo->toArray();
      if (isset($returnKey) && $returnKey ) {
          Yii::$app->cache->set($returnKey, $list, 120);  
      }
      if ($act == 'down' && $list['status'] == '0' && $list['status_msg'] == 'ok') {
          $task_name = Agrotask::getNameByID(['taskid' => $taskid]);

          $record = array();
          $record['upper_uid'] = $record['uid'] = $userData['items']['0']['account_info']['user_id'];
          $record['team_id'] = 0;
          $record['operator'] = $userData['items']['0']['account_info']['email'];
          $record['type'] = '植保机拥有者';
          $record['content'] = '下载任务';
          $record['detail'] = '下载: "<i>'.$task_name.'</i>"任务规划文件';
          $record['ip'] = $this->get_client_ip();
          Agrorecord::add($record);

          $this->downTask($task_name,$list['data']);
      }          
      return $list;                  
    }
    /*
    *  任务分享，将自己规划的任务分享同一老板下的其他队伍  /apiuser/taskshare
    *  @parameter taskid 需要分享的任务id
    *  @parameter teamname 分享的目标teamname
    *  return {"status":0,"status_msg":"ok","data":[{"id":"4","upper_uid":"23617225210722647","uid":"772985517407567872","team_id":"2","name":"Qv","date":null,"time":"0","type":null,"crop":null,"crop_stage":null,"prevent":null,"setting":null,"key_point":null,"home":null,"obstacle_point":null,"plan_edge_poit":null,"edge_poit":null,"way_point":null,"lat":"0.000000","lng":"0.000000","location":null,"battery_times":"0","interval":null,"app_type":null,"deleted":"0","operator":null,"ip":"","source":"0","ext1":"","ext2":"","updated_at":"2016-09-09 19:28:41","created_at":"2016-09-09 19:28:41","area":null}]}
    *  "status":0 和 "status_msg":"ok" 表示成功
    * "status":1000 表示参数不合法；"status":1001 表示登录已经过期,请重新登录；
    *  "status":1007 表示权限不足；
    */
   public function actionTaskshare() 
   {
      if (extension_loaded ('newrelic')) {
          newrelic_name_transaction ( 'user/taskshare');
      }
      $model['task_id'] = $this->getPostValue('taskid');  
      $model['team_id'] = $this->getPostValue('teamid');  

      $status_msg = 'failed';  
      if (!isset($model['task_id']) || !isset($model['team_id'])) {
         $data = array('status' => 1000,'status_msg'=> $status_msg,'message'=>'参数不合法');
         return $data;
      }
      $actionName = 'actionTaskshare';
      $userData = $this->getUserInfo($actionName);
      if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
          return $userData;
      }
      if (empty($userData['items']['0']['account_info'])) { 
          return array('status' => 1007, 'status_msg'=> $status_msg,'message'=>'权限不足');
      } 
      $where = array();
      $where['uid'] = $userData['items']['0']['account_info']['user_id'];
      $where['deleted'] = '0';
      $activeCount = Agroactiveinfo::getAndEqualWhereCount($where);
      if ($activeCount <= 0) {
          return array('status' => 1007, 'status_msg'=> $status_msg,'message'=>'权限不足');
      }  
      $model['uid'] = $userData['items']['0']['account_info']['user_id']; 
      $result = Agroteamtask::add($model);
      if($result <= 0) {
          return array('status' => 1008, 'status_msg'=> $status_msg,'message'=>'分享失败');
      }
      $team_name = Agroteam::getNameByID(['teamid' => $model['team_id']]);
      $task_name = Agrotask::getNameByID(['taskid' => $model['task_id']]);

      $record = array();
      $record['upper_uid'] = $record['uid'] = $userData['items']['0']['account_info']['user_id'];
      $record['team_id'] = 0;
      $record['operator'] = $userData['items']['0']['account_info']['email'];
      $record['type'] = '植保机拥有者';
      $record['content'] = '分享任务';
      $record['detail'] = '分享: "<i>'.$task_name.'</i>"给"<i>'.$team_name.'</i>"团队';
      $record['ip'] = $this->get_client_ip();
      Agrorecord::add($record);

      return array('status' => 0, 'status_msg'=> 'ok','message'=>'分享成功');
   }

     //下载文件
    protected function downTask($task_name,$result) {    
      if (empty($result)) {
         return false;
      }
      $filename = $task_name.'.dat';
      header('Expires: 0');//过期时间
      header('Cache-Control: must-revalidate');//缓存策略，强制页面不缓存，作用与no-cache相同，但更严格，强制意味更明显
      header('Pragma: public');
      //文件的类型 
      header('Content-type: text/plain'); 
      //下载显示的名字 
      header('Content-Disposition: attachment; filename="'.$filename.'"'); 
      $Djihmac = new Djihmac();
      $result = json_encode($result);
      echo $Djihmac->getAesEncrypt($result,1);
      exit;  
    }   

    //优先读取post，然后get
    protected function getPostValue($key) {
         $value = Yii::$app->request->post($key);
         return $value !==null ? $value : Yii::$app->request->get($key); 
    }

    protected function getCookieCountry($country) {
        if (empty($country)) {           
           if (isset(Yii::$app->request->cookies['country'])) {
                return strtolower(Yii::$app->request->cookies['country']);
           } else {
            return 'cn';
           }
        }
        return strtolower($country);
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
    
   /* /apiuser/export
    * 导出选中的飞行记录
    * @parameter arrayID 选中的飞行记录的ID数组
    * @parameter price  单价
    * @parameter total_price 总价格
    * @parameter  teamid  如果是飞手登陆则需要teamid

    * "status":0 和 "status_msg":"ok" 表示成功
    * "status":1000 表示参数不合法；
    * "status":1001 表示登录已经过期,请重新登录；
    * "status":1007 表示权限不足；
    */ 
  public  function actionExport()
  {
      if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'user/export');
      }
      $arr_id = explode(",", $this->getPostValue("arrayID"));//将字符串转成数组
      $price = $this->getPostValue("price"); //$price = 12.3;
      $total_price = $this->getPostValue("total_price"); 
      $status_msg = 'failed'; 
      if (empty($arr_id)) {
         return array('status' => 1000,'status_msg'=> $status_msg,'message'=>'参数不合法');
      }    
      $actionName = 'actionTaskinfo';
      $userData = $this->getUserInfo($actionName);
      if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
          return $userData;
      }
      if (empty($userData['items']['0']['account_info'])) { 
          return array('status' => 1007, 'status_msg'=> $status_msg,'message'=>'权限不足');
      }
      $where = array();
      $where['uid'] = $userData['items']['0']['account_info']['user_id'];
      $where['deleted'] = '0';
      $objectPHPExcel = new PHPExcel();
      $objectPHPExcel->setActiveSheetIndex(0);
  
      $objectPHPExcel->getActiveSheet()->mergeCells('B1:J1');
      $objectPHPExcel->getActiveSheet()->setCellValue('B1','飞行记录表');

      $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('B2','飞行记录表');
      $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('B2','飞行记录表');
      $objectPHPExcel->setActiveSheetIndex(0)->getStyle('B1')->getFont()->setSize(20);
      $objectPHPExcel->setActiveSheetIndex(0)->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

      $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('B2','日期：'.date("Y年m月j日")."， "."单价：".$price."元， "."总计：".$total_price."元");
      //$objectPHPExcel->setActiveSheetIndex(0)->setCellValue('G2','第'.1.'/'.8.'页');
      $objectPHPExcel->setActiveSheetIndex(0)->getStyle('G2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

      //表格头的输出
      $objectPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
      $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('B3','起落时间');
      $objectPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
      $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('C3','作业地点');
      $objectPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(45);
      $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('D3','植保机名称');
      $objectPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
      $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('E3','作业面积');
      $objectPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
      $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('F3','飞行时长');
      $objectPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
      $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('G3','作业对象');
      $objectPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
      $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('H3','间距');
      $objectPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
      $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('I3','飞手名称');
      $objectPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
      $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('J3','团队名称');
      $objectPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);

      //设置居中
      $objectPHPExcel->getActiveSheet()->getStyle('B3:J3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

      //设置边框
      $objectPHPExcel->getActiveSheet()->getStyle('B3:J3' )->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
      $objectPHPExcel->getActiveSheet()->getStyle('B3:J3' )->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
      $objectPHPExcel->getActiveSheet()->getStyle('B3:J3' )->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
      $objectPHPExcel->getActiveSheet()->getStyle('B3:J3' )->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
      $objectPHPExcel->getActiveSheet()->getStyle('B3:J3' )->getBorders()->getVertical()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

      //设置颜色
      $objectPHPExcel->getActiveSheet()->getStyle('B3:J3')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FF66CCCC');
      $model['deleted'] = 0;
      $result = Agroflight::getRecordByID($model, $arr_id);//一次性把飞行记录查出来 
      if(empty($result)) {
          return array('status'=>0, 'status_msg'=>'ok', 'data'=>[]);
      }
      $n = 0;
      foreach (array_reverse($result) as $key => $value) {
          $where['uid'] = $value['uid'];
          $where['product_sn'] = $value['product_sn'];
          $where['deleted'] = 0;
          $productname = $value['nickname'];//Agroactiveinfo::getNameByID($where);
          $where['teamid'] = $value['team_id'];
          $where['bossid'] = $value['upper_uid'];
          $teamname = $value['team_name'];//Agroteam::getNameByID($where);
          $where['flyerid'] = $value['uid'];
          $flyername = $value['flyer_name'];//Agroflyer::getNameByID($where);

          $objectPHPExcel->getActiveSheet()->setCellValue('B'.($n+4), substr($value['create_date'], 0, 4)."年".substr($value['create_date'], 4, 2)."月".substr($value['create_date'], 6, 2)."日".",".$value['start_end']);//起落时间
          $objectPHPExcel->getActiveSheet()->setCellValue('C'.($n+4), $value['location']);//作业地点
          $objectPHPExcel->getActiveSheet()->setCellValue('D'.($n+4), empty($productname) ? '未命名' : $productname);//飞行器名字
          $objectPHPExcel->getActiveSheet()->setCellValue('E'.($n+4), number_format($value['new_work_area'] * 0.01, 2)."亩");//作业面积
          $min = floor($value['work_time']/1000/60);
          $sec = floor($value['work_time']/ 1000 % 60);
          $result = '';
          if($min > 0) {
              $result = $min.'分'.$sec.'秒';
          } else {
              $result = $sec.'秒';
          }
          $objectPHPExcel->getActiveSheet()->setCellValue('F'.($n+4), $result);//作业时间
          switch($value['plant']) {
              case 0x01:
                  $value['plant'] = '水稻';
              break;
              case 0x02:
                  $value['plant'] = '玉米';
              break;
              case 0x03:
                  $value['plant'] = '小麦';
              break;
              case 0x04:
                  $value['plant'] = '高粱';
              break;
              case 0x05:
                  $value['plant'] = '棉花';
              break;
              case 0x06:
                  $value['plant'] = '马铃薯';
              break;
              case 0x07:
                  $value['plant'] = '果树';
              break;
              case 0x00:
                  $value['plant'] = '其他';
              break;
          }
          $objectPHPExcel->getActiveSheet()->setCellValue('G'.($n+4), $value['plant']);//作业对象
          $objectPHPExcel->getActiveSheet()->setCellValue('H'.($n+4), $value['farm_delta_y']."米");//喷幅
          $objectPHPExcel->getActiveSheet()->setCellValue('I'.($n+4), $flyername);//飞手名字
          $objectPHPExcel->getActiveSheet()->setCellValue('J'.($n+4), $teamname);//队伍名字
          $objectPHPExcel->setActiveSheetIndex(0)->getStyle('B'.($n+4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
          $objectPHPExcel->setActiveSheetIndex(0)->getStyle('C'.($n+4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
          $objectPHPExcel->setActiveSheetIndex(0)->getStyle('D'.($n+4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
          $objectPHPExcel->setActiveSheetIndex(0)->getStyle('E'.($n+4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
          $objectPHPExcel->setActiveSheetIndex(0)->getStyle('F'.($n+4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
          $objectPHPExcel->setActiveSheetIndex(0)->getStyle('G'.($n+4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
          $objectPHPExcel->setActiveSheetIndex(0)->getStyle('H'.($n+4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
          $objectPHPExcel->setActiveSheetIndex(0)->getStyle('I'.($n+4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
          $objectPHPExcel->setActiveSheetIndex(0)->getStyle('J'.($n+4))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

          $objectPHPExcel->getActiveSheet()->getColumnDimension()-> setAutoSize(true);
          $n = $n +1; 
      }
      //设置分页显示
      $objectPHPExcel->getActiveSheet()->getPageSetup()->setHorizontalCentered(true);
      $objectPHPExcel->getActiveSheet()->getPageSetup()->setVerticalCentered(false);
  
      ob_end_clean();
      ob_start();
  
      header('Content-Type : application/vnd.ms-excel');
      header('Content-Disposition:attachment;filename="'.'飞行记录表-'.date("Y年m月j日").'.xls"');
      $objWriter= PHPExcel_IOFactory::createWriter($objectPHPExcel,'Excel5');
      $objWriter->save('php://output');
      
      //操作记录
      $record = array();
      $record['upper_uid'] = $record['uid'] = $userData['items']['0']['account_info']['user_id'];
      $record['team_id'] = 0;
      $record['operator'] = $userData['items']['0']['account_info']['email'];
      $record['type'] = '植保机拥有者';
      $record['content'] = '飞行记录';
      $record['detail'] = '全选/勾选 下载飞行记录';
      $record['ip'] = $this->get_client_ip();
      Agrorecord::add($record);

      return array('status'=>0, 'status_msg'=>'ok', 'data'=>'');    
  }
   /* /apiuser/sendmsg
    * 实时给在飞飞机发送消息，只有老板才可以发送消息，给具体的某一台飞机发送消息，需要参数：1，飞机编号sn，2，消息内容。
    * @parameter hardware_id 飞机编号
    * @parameter message  消息内容
    *  return 参数
        array('status' => $goResult['status'],'status_msg'=> 'ok','message'=>'发送成功')
        array('status' => $goResult['status'],'status_msg'=> 'failed','message'=>'发送失败')
    *  其中 status 为以下内容：
    *  ERR_NOT_AUTH                = 1001
    *  ERR_DB_FAILED               = 1002
    *  ERR_PARAMS_ERR              = 1003
    *  ERR_NOT_ONLINE              = 1004
    *  ERR_REQ_REALTIME_SVR_FAILED = 1005
    *  ERR_SEND_APP_FAILED         = 1006
    *  ERR_APP_RESPONE_TIMEOUT     = 1007
    *
    */
    public function actionSendmsg()
    {
        if (extension_loaded ('newrelic')) {
            newrelic_name_transaction ( 'user/sendmsg');
        }
        $model = array();
        $model['hardware_id'] = Yii::$app->request->post("hardware_id");    
        $model['message'] = Yii::$app->request->post("message");   

        $status_msg = 'failed'; 
        if (!isset($model['hardware_id']) ) {
           $data = array('status' => 1000,'status_msg'=> $status_msg,'message'=>'参数不合法');
           return $data;
        }
        $actionName = 'actionSendmessage';
        $userData = $this->getUserInfo($actionName);
        if ($userData && $userData['status'] == '0' && $userData['status_msg'] == 'ok') {
            if (isset($userData['items']['0']['account_info'])) {
                $where['uid'] = $userData['items']['0']['account_info']['user_id'];
                $where['deleted'] = '0';
                $where['hardware_id'] = $model['hardware_id'];
                $activeInfo = Agroactiveinfo::getAndEqualWhere($where,0,1,'id',1,'id');
                if ($activeInfo && is_array($activeInfo) && $activeInfo['0']['id'] > 0) {
                    $djiUser = new DjiUser(); 
                    $goResult = $djiUser->runGoSendmsg($model['hardware_id'], $model['message']); 
                    if ($goResult && is_array($goResult) && $goResult['status'] == 0 && $goResult['sn'] == $model['hardware_id']) {           
                      //发送成功
                        $data = array('status' => $goResult['status'],'status_msg'=> 'ok','message'=>'发送成功');
                        return $data;    
                                  
                    }else{
                       //发送失败
                        $data = array('status' => $goResult['status'],'status_msg'=> 'failed','message'=>'发送失败');
                        return $data; 
                    }                  
                } else {
                    $data = array('status' => 1007,'status_msg'=> 'failed','message'=>'权限不足');
                    return $data; 
                }                 
            }

        }
    }

    protected function Islogin($token) {
        $appId = Yii::$app->params['GWServer']['GWAPIAPPID']; 
        $gwapi = Yii::$app->params['GWServer']['GWAPIURL']; 
        $postData = array(); 

        $url = $gwapi."/gwapi/api/accounts/get_account_info_by_key?token=$token";
        $data = (new DjiUser)->postGateway($url, $postData); 
        return $data;
    }
    /* /apiuser/checkhardwareid
    * @parameter string hardware_id  飞控id
    * @parameter token 登录令牌
    * return 参数
    * "status":0 和 "status_msg":"ok" 表示激活
    * "status":1000 表示参数不合法；
    * "status":1001 表示登录已经过期,请重新登录；
    * "status":1007 表示权限不足；
    * "status":1008 "status_msg":"fail" 表示未激活
    */
    public function actionCheckhardwareid()
    { 
        if (extension_loaded ('newrelic')) {
            newrelic_name_transaction ( 'user/checkhardwareid');
        }
        $where = array();
        $where['hardware_id'] = Yii::$app->request->post("hardware_id"); 
        $token = Yii::$app->request->post('token');
        if (empty($where['hardware_id']) || empty($token)) {
           return array('status' => 1000,'status_msg'=> 'failed','message'=>'参数不合法');
        }
        $userData = $this->Islogin($token);
        if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
            return $userData;
        }
        if (empty($userData['items']['0']['account_info'])) { 
            return array('status' => 1007, 'status_msg'=> 'failed','message'=>'权限不足');
        }
        $where['deleted'] = '0';
        $where['is_active'] = '1';
        $activeCount = Agroactiveinfo::getAndEqualWhereCount($where); //查看hardware_id是否存在，存在说明激活了。
        if ($activeCount > 0) {
            return array('status' => 0, 'status_msg'=> 'ok','message'=>'已经激活');
        } else {
            return array('status' => 1008, 'status_msg'=> 'failed','message'=>'未激活');          
        }
    }

   /* /apiuser/checksn
    * @parameter string body_code 飞机机身码
    * @parameter string idcard 用户身份证
    * return 参数
    * "status":0 和 "status_msg":"ok" 表示成功
    * "status":1000 表示参数不合法；
    * "status":1001 表示登录已经过期,请重新登录；
    * "status":1004 表示5秒内重复提交,请稍后重试；
    * "status":1007 表示权限不足；
    * "status":1008 "status_msg":"fail" 表示校验失败
    */
    public function actionChecksn()
    { 
        if (extension_loaded ('newrelic')) {
            newrelic_name_transaction ( 'user/checksnd');
        }
        $model['body_code'] = Yii::$app->request->post("body_code");  
        $token = Yii::$app->request->headers['AG-Token'];

        $status_msg = 'failed'; 
        if (!isset($model['body_code'])) {
           $data = array('status' => 1000,'status_msg'=> $status_msg,'message'=>'参数不合法');
           return $data;
        }
        $userData = $this->Islogin($token);
        if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
            return $userData;
        }
        if (empty($userData['items']['0']['account_info'])) {
            return array('status' => 1007, 'status_msg'=> $status_msg,'message'=>'权限不足');
        }

        $model['is_active'] = '0';
        $model['deleted'] = '0';
        if ($this->isInChina()) {
            // 只有国内才检查检查sn
            $activeCount = Agroactiveinfo::getAndEqualWhereCount($model);
            if ($activeCount <= 0) {
                return array('status' => 3001, 'status_msg' => $status_msg, 'message' => '校验失败');
            }
        }
        $session = Yii::$app->session;
        if ($session->isActive) {
            $session->open(); // 开启session
        }
        if(isset($model['body_code'])) {
            $session->set('body_code', $model['body_code']);
        }
        $status_msg = 'ok';
        return array('status' => 0, 'status_msg'=> $status_msg,'message'=>'校验成功');            
    }
    public function actionCheckidcard()
    {
        $model['idcard'] = Yii::$app->request->post("idcard");
        $model['hardware_id'] = Yii::$app->request->post('hardware_id');
        $token = Yii::$app->request->headers['AG-Token'];

        $status_msg = 'failed';
        if (!isset($model['idcard'])) {
           $data = array('status' => 1000,'status_msg'=> $status_msg,'message'=>'参数不合法');
           return $data;
        }

        // check token
        $userData = $this->Islogin($token);
        if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
            return $userData;
        }

        // check user info
        if (empty($userData['items']['0']['account_info'])) { 
            return array('status' => 1007, 'status_msg'=> $status_msg,'message'=>'权限不足');
        }

        // open session
        $session = Yii::$app->session;
        if (!$session->isActive) {
            $session->open();
        }

        if ($this->isInChina()) {
            $active_info = Agroactiveinfo::findOne([
                    'hardware_id' => $model['hardware_id'],
                    'is_active' => '0',
                    'deleted' => '0']
            );
            if(!$active_info) {
                return array('status' => 3003, 'status_msg' => $status_msg, 'message' => '请先在代理商平台激活飞机');
            }

            // if in china, check the id card
            if (strtolower($active_info->idcard) != strtolower($model['idcard'])) {
                return ['status' => 3002, 'status_msg' => $status_msg, 'message' => '身份证号码不匹配'];
            }

            $session->set('body_code', $active_info->body_code);
        } else {
            $session->set('idcard', $model['idcard']);
        }

        return array('status' => 0, 'status_msg'=> 'ok','message'=>'校验成功');
    }
    
    protected function send($ch,$data){
        curl_setopt ($ch, CURLOPT_URL, 'https://sms.yunpian.com/v2/sms/single_send.json');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        return curl_exec($ch);
    }
   /* /apiuser/sendcode
    * @parameter string phone 手机号
    * @parameter string body_code 飞机机身编号
    * return 参数
    * "status":0 和 "status_msg":"ok" 表示成功
    * "status":1000 表示参数不合法；
    * "status":1001 表示登录已经过期,请重新登录；
    * "status":1004 表示5秒内重复提交,请稍后重试；
    * "status":1007 表示权限不足；
    * "status":1008 "status_msg":"fail"表示发送验证码失败
    */
    public function actionSendcode()
    {
        $model['phone'] = Yii::$app->request->post("phone");
        $model['body_code'] = Yii::$app->session['body_code'];
        $token = Yii::$app->request->headers['AG-Token'];

        $status_msg = 'failed'; 
        if (empty($model['phone']) || empty($model['body_code']) || empty($token)) {
           $data = array('status' => 1000,'status_msg'=> $status_msg,'message'=>'参数不合法');
           return $data;
        }

        $userData = $this->Islogin($token);
        if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
            return $userData;
        }
        if (empty($userData['items']['0']['account_info'])) { 
            return array('status' => 1007, 'status_msg'=> $status_msg,'message'=>'权限不足');
        }
        /*
        $where['body_code'] = $model['body_code'];
        $where['deleted'] = '0';
        $where['phone'] = $model['phone'];
        $activeCount = Agroactiveinfo::getAndEqualWhereCount($where);
        if($activeCount <= 0) {
            return array('status' => 1007, 'status_msg'=> $status_msg,'message'=>'权限不足');
        }
        */

        $code = rand(100000,999999);
        $apikey = "b010bf811f9266567ddda76e4cd81fb1";
        $mobile = $model['phone'];
        $text = "【大疆农业管理平台】验证码是$code 。如非本人操作，请忽略本短信";
        $ch = curl_init();
        //设置验证方式 
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept:text/plain;charset=utf-8', 'Content-Type:application/x-www-form-urlencoded','charset=utf-8'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);// 设置返回结果为流
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);//设置超时时间
        curl_setopt($ch, CURLOPT_POST, 1);// 设置通信方式 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // 发送短信
        $data=array('text'=>$text,'apikey'=>$apikey,'mobile'=>$mobile);
        $json_data = $this->send($ch,$data);
        (new DjiUser)->add_log($json_data, 'active_sendmssg');
        $array = json_decode($json_data, true);
        if(isset($array['http_status_code']) && $array['http_status_code'] != 0) {
            $data = array('status' => 1008, 'status_msg'=> $status_msg,'message'=>$array['detail']);
            curl_close($ch);
            return $data;
        }
        curl_close($ch);
        $paramsString = $model['phone'].$code;
        $returnKey = "_code_".md5($paramsString);
        Yii::$app->cache->set($returnKey, $code, 300); //验证码有效期为5分钟
        $data = array('status' => 0, 'status_msdfg'=> 'ok','message'=> '发送验证码成功');
        return $data;
    }
    /* /apiuser/checkcode
    * @parameter string body_code 飞机机身码
    * @parameter string phone 手机号
    * @parameter string code 验证码
    * return 参数
    * "status":0 和 "status_msg":"ok" 表示成功
    * "status":1000 表示参数不合法；
    * "status":1001 表示登录已经过期,请重新登录；
    * "status":1004 表示5秒内重复提交,请稍后重试；
    * "status":1007 表示权限不足；
    * "status":1008 "status_msg":"fail" 表示校验失败
    */
    public function actionCheckcode()
    {
        $body_code = Yii::$app->session['body_code'];
        $phone = Yii::$app->request->post("phone"); 
        $code = Yii::$app->request->post("code");  
        $token = Yii::$app->request->headers['AG-Token'];

        $status_msg = 'failed';
        if (empty($body_code) || empty($phone) || empty($code)) {
           $data = array('status' => 1000,'status_msg'=> $status_msg,'message'=>'参数不合法');
           return $data;
        }

        $userData = $this->Islogin($token);
        if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
            return $userData;
        }
        if (empty($userData['items']['0']['account_info'])) { 
            return array('status' => 1007, 'status_msg'=> $status_msg,'message'=>'权限不足');
        }

        $where = array();
        $where['body_code'] = $body_code;
        $where['deleted'] = '0';
        $active_info = Agroactiveinfo::findOne($where);
        if(!$active_info) {
            return array('status' => 1007, 'status_msg'=> $status_msg,'message'=>'找不到相应飞行器记录');
        }

        // check code
        $paramsString = $phone.$code;
        $luckkey = "_code_".md5($paramsString);
        $luckdata = Yii::$app->cache->get($luckkey);
        if(!$luckdata || $luckdata != $code) {
            // check code failed
            return array('status' => 1008, 'status_msg'=> 'failed','message'=>'验证码错误');
        }

        // write the new phone num to db
        $active_info->phone = $phone;
        $active_info->save();

        Yii::$app->cache->delete($luckkey);
        return ['status' => 0, 'status_msg' => 'ok', 'message'=>'验证码正确'];
    }

    protected function notify_iuav_and_getback_body_code($uid, $hardware_id) {
        $url_base = $this->isInTestEnv() ? 'http://agras-api-staging.aasky.net' : 'https://iuav-api.dji.com';

        $key = 'qK92VymMVojYPuYqK92VymMVojYPuYW47ZLVnQXVnQX';
        $ts = time();
        $signature = strtoupper(hash_hmac("sha1", $ts.$uid.$hardware_id, $key));

        $client = new Client(['base_uri' => $url_base]);
        try {
            $res = $client->request('POST', '/api/agnotice/activeinfo/', [
                'form_params' => [
                    'datetime' => $ts,
                    'uid' => $uid,
                    'hardware_id' => $hardware_id,
                    'signature' => $signature
                ]
            ]);

            $res_data = json_decode($res->getBody(), true);

            if (!$res_data || $res_data['status'] != 200) {
                return null;
            }

            return $res_data['data']['body_code'];
        } catch (RequestException $e) {
            return null;
        }
    }

    /* /apiuser/setnickname   激活流程的最后一步，设置飞机名称，同时设置飞控id，飞控id可从app端获取
    * @parameter string nickname   飞机名称
    * @parameter string hardware_id 飞控id,飞控id可从app端获取
    * @parameter string body_code  飞机机身编号
    * @parameter string latitude  坐标纬度
    * @parameter string longitude  坐标经度
    * return 参数
    * "status":0 和 "status_msg":"ok" 表示成功
    * "status":1000 表示参数不合法；
    * "status":1001 表示登录已经过期,请重新登录；
    * "status":1004 表示5秒内重复提交,请稍后重试；
    * "status":1007 表示权限不足；
    * "status":1008 "status_msg":"fail" 表示设置失败
    *
    */
    public function actionSetnickname()
    {
        $model = array();
        $model['nickname'] = Yii::$app->request->post("nickname");
        $model['hardware_id'] = Yii::$app->request->post("hardware_id"); 
        $model['body_code'] = Yii::$app->session['body_code'];
        $model['idcard'] = Yii::$app->session['idcard'];
        $model['latitude'] = Yii::$app->request->post("latitude");
        $model['longitude'] = Yii::$app->request->post("longitude");
        $token = Yii::$app->request->headers['AG-Token'];

        if (empty($model['nickname']) || empty($model['hardware_id'])) {
           return ['status' => 1000, 'status_msg' => 'failed', 'message' => '参数不合法'];
        }

        $userData = $this->Islogin($token);
        if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
            return $userData;
        }
        if (empty($userData['items']['0']['account_info'])) {
            return ['status' => 1007, 'status_msg' => 'failed', 'message' => '权限不足'];
        }

        $self_uid = $userData['items']['0']['account_info']['user_id'];
        $self_account = $userData['items']['0']['account_info']['email'];
        $self_nickname = $userData['items']['0']['account_info']['nick_name'];

        if ($this->startsWith($_SERVER['HTTP_HOST'], 'kr-ag.dji.com')
            && !in_array($self_account, ['activem@aw-dji.com', 'actives@aw-dji.com'])) {

            return ['status' => 3004, 'status_msg' => 'failed', 'message' => '不是指定的韩国激活账号'];
        }

        if (!isset($model['body_code'])) {
            if ($this->isInChina()) {
                $where = array();
                $where['hardware_id'] = $model['hardware_id'];
                $where['deleted'] = '0';
                $active_info = Agroactiveinfo::findOne($where);

                if ($active_info) $model['body_code'] = $active_info->body_code;
            } else {
                $bc = $this->notify_iuav_and_getback_body_code($self_uid, $model['hardware_id']);
                if ($bc) $model['body_code'] = $bc;
            }
        }
        if (!isset($model['body_code'])) {
            return ['status' => 1008, 'status_msg' => 'failed', 'message' => '找不到对应飞行器'];
        }

        if (!$this->isInChina()) {
            // 如果是海外平台, 那么此时没有代理商平台预写入的记录, 需要新创建
            if (!Agroactiveinfo::findOne(['hardware_id' => $model['hardware_id']])) {
                $active_info = [];
                $active_info['order_id'] = '';
                $active_info['hardware_id'] = $model['hardware_id'];
                $active_info['body_code'] = $model['body_code'];
                $active_info['idcard'] = $model['idcard'];
                $active_info['phone'] = '';
                $active_info['type'] = 'MG-1S';
                $active_info['ip'] = $this->get_client_ip();
                $active_info['deleted'] = '0';
                $active_info['is_active'] = '0';

                Agroactiveinfo::add($active_info);
            }
        }

        $model['uid'] = $userData['items']['0']['account_info']['user_id'];
        $model['email'] = $userData['items']['0']['account_info']['email'];

        $model['is_active'] = '1'; //标志已经激活了  
        $model['active_location'] = (new DjiUser)->regeo($model['longitude'], $model['latitude']); //增加激活地理位置信息

        //激活完成以后，立刻创建一个老板的团队，并把自己加到这个团队中。
        $where = array();
        $where['uid'] = $userData['items']['0']['account_info']['user_id'];
        $where['deleted'] = '0';
        $self_team_info = Agroteam::findOne($where);
        if ($self_team_info) {
            // 如果老板有自己的团队, 那么把团队绑到飞机操作团队上
            $model['team_id'] = $self_team_info->id;
        } else {
            $new_team_info = array();
            $new_team_info['uid'] = $self_uid;
            $new_team_info['captain'] = $self_uid;
            $new_team_info['name'] = $userData['items']['0']['account_info']['nickname'];
            $new_team_info['ip'] = $this->get_client_ip();
            $new_team_info['upper_teamid'] = '0';
            $new_team_info['showed'] = '1';
            $teamid = Agroteam::add($new_team_info); //添加团队

            $new_flyer_info = array();
            $new_flyer_info['nickname'] = $userData['items']['0']['account_info']['nickname'];
            $new_flyer_info['team_id'] = $teamid->id;
            $new_flyer_info['upper_uid'] = $new_flyer_info['uid'] = $self_uid;
            $new_flyer_info['account'] = $userData['items']['0']['account_info']['email'];
            $new_flyer_info['job_level'] = '1'; //将自己设为队长
            $new_flyer_info['ip'] = $this->get_client_ip();
            Agroflyer::add($new_flyer_info); //添加自己为飞手

            $model['team_id'] = $teamid->id;
        }

        $active_info = Agroactiveinfo::findOne(['hardware_id' => $model['hardware_id'], 'deleted' => 0]);
        if ($active_info) {
            $this->make_boss_controllable($active_info['id'], $model['hardware_id'], $self_account, $self_uid, $self_nickname);
        }

        $result = Agroactiveinfo::updatePhoneNickname($model);
        if($result <= 0) {
            return array('status' => 1009, 'status_msg' => 'failed', 'message' => '设置失败');
        }

        Yii::$app->session->remove('body_code'); //可以删除session了
        Yii::$app->session->close();
        Yii::$app->session->destroy();
        $result = array('status' => 0, 'status_msg'=>'ok', 'message' => '设置成功');
        return $result; 
    }
 
    /* /apiuser/records   查看或搜索操作记录
    *  @parameter string start_date 开始日期 20160901
    *  @parameter string end_date 结束日期 20160902
    *  @parameter string operator 操作者
    *  @parameter string content  操作内容
    *  @parameter string type     工种
    * return 参数
    * "status":0 和 "status_msg":"ok" 表示成功
    * "status":1000 表示参数不合法；
    * "status":1001 表示登录已经过期,请重新登录；
    * "status":1004 表示5秒内重复提交,请稍后重试；
    * "status":1007 表示权限不足；
    * "status":1008 "status_msg":"fail" 表示操作失败
    */
    public function actionRecords()
    {
        if (extension_loaded ('newrelic')) {
            newrelic_name_transaction ( 'user/records');
        }
        $model['start_date'] = $this->getPostValue("start_date"); 
        $model['end_date'] = $this->getPostValue("end_date"); 
        $model['operator'] = $this->getPostValue("operator");  
        $model['content'] = $this->getPostValue("content"); 
        $model['type'] = $this->getPostValue("type"); 
        $page = intval($this->getPostValue("page"));
        $size = intval($this->getPostValue("size"));

        if ($size < 1 || $size > 100 ) {
          $size = 30;
        }
        if ($page < 1 ) {
            $page = 1;
        }
        $start = ($page-1) * $size;
        $start = $start < 0 ? 0 : $start;
        if (isset(Yii::$app->request->cookies['_meta_key'])) {
          $meta_key = Yii::$app->request->cookies['_meta_key']->value;
          $paramsString = $model['start_date'].$model['end_date'].$model['operator'].$model['content'].$page.$size.$model['type'];
          $returnKey = __CLASS__.__FUNCTION__."_data_".md5($paramsString.$meta_key);   
          $returnData = Yii::$app->cache->get($returnKey);
          if ( $returnData ) {
                return $returnData;
          } 
        }
        $status_msg = 'failed'; 
        $actionName = 'actionRecords';
        $userData = $this->getUserInfo($actionName);
        if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
            return $userData;
        }
        if (empty($userData['items']['0']['account_info'])) { 
            return array('status' => 1007, 'status_msg'=> $status_msg,'message'=>'权限不足');
        }
        $where['uid'] = $userData['items']['0']['account_info']['user_id'];
        $where['deleted'] = '0';
        $activeCount = Agroactiveinfo::getAndEqualWhereCount($where);
        if ($activeCount > 0) {
            $model['upper_uid'] = $userData['items']['0']['account_info']['user_id'];            
        } else {
            $data = array('status' => 1007,'status_msg'=> $status_msg,'message'=>'权限不足');
            return $data; 
        }
        $count = Agrorecord::getWhereRecordsCount($model); 
        $fields = 'operator,type,content,detail,created_at';
        $recordData = Agrorecord::getAndWhere($model, $fields, $start, $size); 
        $data = array('status' => 0,'status_msg'=> 'ok','data'=>$recordData, 'count' => $count['records_count']);
        if (isset($returnKey) && $returnKey ) {
            Yii::$app->cache->set($returnKey, $data, 5);  
        }
        return $data; 
    }
    //解禁
    /* /apiuser/ban   申请解禁
    *  @parameter string start_date 开始日期 20160901
    *  @parameter string end_date 结束日期 20160902
    *  @parameter string operator 操作者
    *  @parameter string content  操作内容
    *  @parameter string type     工种
    * return 参数
    * "status":0 和 "status_msg":"ok" 表示成功
    * "status":1000 表示参数不合法；
    * "status":1001 表示登录已经过期,请重新登录；
    * "status":1004 表示5秒内重复提交,请稍后重试；
    * "status":1007 表示权限不足；
    * "status":1008 "status_msg":"fail" 表示操作失败
    */
    public function actionSendcapcha() {
        $actionName = 'actionSendcapcha';
        $userData = $this->getUserInfo($actionName);

        if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
            $data = array('status' => 1001, 'status_msg'=> 'failed','message'=>'登录已经过期,请重新登录');
            return $data;
        }

        if (!isset($userData['items']['0']['account_info'])) {
            return array('status' => 1001, 'status_msg'=> 'failed','message'=>'登录已经过期,请重新登录');
        }

        $self_uid = $userData['items']['0']['account_info']['user_id'];

        $boss_phone = $this->getPostValue('phone');
        if (empty($boss_phone)) {
            $boss_phone = $this->get_boss_phone($self_uid);
        }

        if (empty($boss_phone)) {
            return ['status' => 1002, 'status_msg' => 'failed', 'message' => '找不到对应手机号'];
        }

        $code = rand(100000,999999); //生成6位验证码

        $apikey = "b010bf811f9266567ddda76e4cd81fb1"; //修改为您的apikey(https://www.yunpian.com)登陆官网后获取
        $mobile = $boss_phone; //请用自己的手机号代替
        if ($this->startsWith($boss_phone, '+') && !$this->endsWith($boss_phone, '+86')) {
            // starts with + but not +86, this an international phone call

            $text = "【DJI】Your verification code is $code";
        } else {
            $text = "【大疆农业管理平台】验证码是$code 。如非本人操作，请忽略本短信";
        }
        $ch = curl_init();
        //设置验证方式 
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept:text/plain;charset=utf-8', 'Content-Type:application/x-www-form-urlencoded','charset=utf-8'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);// 设置返回结果为流
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);//设置超时时间
        curl_setopt($ch, CURLOPT_POST, 1);// 设置通信方式 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // 发送短信
        $data=array('text'=>$text,'apikey'=>$apikey,'mobile'=>$mobile);
        $json_data = $this->send($ch,$data);
        (new DjiUser)->add_log($json_data, 'active_sendmssg');

        // check send result
        $array = json_decode($json_data, true);
        if(isset($array['http_status_code']) && $array['http_status_code'] != 0) {
            $data = array('status' => 1003, 'status_msg'=> 'failed','message'=>$array['detail']);
            curl_close($ch);
            return $data;
        }
        curl_close($ch);

        // write cache
        $paramsString = $boss_phone.$code;
        $returnKey = "_code_".md5($paramsString); 
        Yii::$app->cache->set($returnKey, $code, 300); //验证码有效期为5分钟
        $data = array('status' => 0, 'status_msg'=> 'ok','message'=> '发送验证码成功');
        return $data;
    }

    public function actionVerifycapcha() {
        $capcha = $this->getPostValue('capcha');

        $action_name = 'actionVerifycapcha';
        $user_data = $this->getUserInfo($action_name);

        if (!$user_data || $user_data['status'] != 0 || $user_data['status_msg'] != 'ok') {
            return array('status' => 1001, 'status_msg' => 'failed', 'message' => '登录已经过期, 请重新登录');
        }

        if (!isset($user_data['items']['0']['account_info'])) {
            return array('status' => 1001, 'status_msg' => 'failed', 'message' => '登录已经过期, 请重新登录');
        }

        if (!isset($capcha)) {
            return array('status' => 1002, 'status_msg' => 'failed', 'message' => '参数错误');
        }

        $current_self_uid = $user_data['items']['0']['account_info']['user_id'];

        $boss_phone = $this->getPostValue('phone');
        if (empty($boss_phone)) {
            $boss_phone = $this->get_boss_phone($current_self_uid);
        }
        if (empty($boss_phone)) {
            return ['status' => 1003, 'status_msg' => 'failed', 'message' => '找不到老板手机号'];
        }

        // check code
        $paramsString = $boss_phone.$capcha;
        $luckkey = "_code_".md5($paramsString);
        $luckdata = Yii::$app->cache->get($luckkey);
        if(!$luckdata || $luckdata != $capcha) {
            // check code failed
            $data = array('status' => 1004, 'status_msg'=> 'failed','message'=>'验证码错误');
            return $data;
        }
        Yii::$app->cache->delete($luckkey);

        return array('status' => 0, 'status_msg' => 'ok');
    }

    public function actionCanapplyban() {
        $action_name = 'actionCanapplyban';
        $user_data = $this->getUserInfo($action_name);

        if (!$user_data || $user_data['status'] != '0' || $user_data['status_msg'] != 'ok') {
            return ['status' => 1001, 'status_msg'=> 'failed','message'=>'登录已经过期,请重新登录'];
        }

        if (!isset($user_data['items']['0']['account_info'])) {
            return ['status' => 1001, 'status_msg'=> 'failed','message'=>'登录已经过期,请重新登录'];
        }

        $self_uid = $user_data['items']['0']['account_info']['user_id'];

        // 先检查最早激活时间有没有超过30天
        $row = Agroactiveinfo::getFirstActiveRecord($self_uid);
        if (!$row) {
            return ['status' => 1002, 'status_msg' => 'failed', 'message' => '找不到激活信息'];
        }

        $last_time = new DateTime($row['active_tm']);
        $now = new DateTime();
        $diff = $now->diff($last_time, true);
        if (!$diff) {
            return ['status' => 1003, 'status_msg' => 'failed', 'message' => '错误的时间格式'];
        }
        if (($now->getTimestamp() - $last_time->getTimestamp()) <= (30 * 24 * 60 * 60)) {
            return ['status' => 0, 'status_msg' => 'ok', 'data' => false, 'message' => '激活时间没有超过30天'];
        }

        // 检查作业时间是否超过100小时
        $work_time = Bossviewworkinfo::get_work_time($self_uid);
        if (!$work_time) {
            return ['status' => 1004, 'status_msg' => 'failed', 'message' => '获取作业时间失败'];
        }
        if (($work_time / 1000 / 60 / 60) <= 100) {
            return ['status' => 0, 'status_msg' => 'ok', 'message' => '累计作业时间没有超过100小时', 'data' => false];
        }

        return ['status' => 0, 'status_msg' => 'ok', 'data' => true];
    }

    public function actionApplyban() {
        $actionName = 'actionApplyban';
        $userData = $this->getUserInfo($actionName);

        if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
            return ['status' => 1001, 'status_msg'=> 'failed','message'=>'登录已经过期,请重新登录'];
        }

        if (!isset($userData['items']['0']['account_info'])) {
            return ['status' => 1001, 'status_msg'=> 'failed','message'=>'登录已经过期,请重新登录'];
        }

        $self_uid = $userData['items']['0']['account_info']['user_id'];
        $self_account = $userData['items']['0']['account_info']['email'];

        // enum all actived hardware id
        $where = array();
        $where['uid'] = $self_uid;
        $where['deleted'] = '0';
        $where['is_active'] = '1';
        $active_info = Agroactiveinfo::getAndEqualWhere($where);

        if (empty($active_info)) {
            return array('status' => 1001, 'status_msg'=> 'failed','message'=>'操作失败');
        }

        $all_hardware_id = [];
        foreach ($active_info as $ai) {
            $hi = $ai['hardware_id'];
            if (isset($hi) && !empty($hi)) {
                $all_hardware_id[] = $hi;
            }
        }
        if (empty($all_hardware_id)) {
            return ['status' => 1002, 'status_msg' => 'failed', 'message' => '没有可供解禁的序列号'];
        }

        // from active info get boss phone
        $bossPhone = $this->get_boss_phone($self_uid);

        //$model = json_decode(Yii::$app->request->getRawBody(), true);
        $model['taskid'] = $this->getPostValue("taskid");
        $model['start'] = $this->getPostValue("start");
        $model['end'] = $this->getPostValue("end");
        $model['capcha'] = $this->getPostValue("capcha");

        if (empty($model['taskid']) || empty($model['start']) || empty($model['end']) || empty($model['capcha'])) {
           return ['status' => 1003, 'status_msg' => 'failed', 'message' => '参数不合法'];
        }

        // check code
        $paramsString = $bossPhone.$model['capcha'];
        $luckkey = "_code_".md5($paramsString);  
        $luckdata = Yii::$app->cache->get($luckkey); 
        if(!$luckdata || $luckdata != $model['capcha']) {
            // check code failed
            $data = array('status' => 1004, 'status_msg'=> 'failed','message'=>'验证码错误');
            return $data;
        }
        Yii::$app->cache->delete($luckkey);

        // get task info
        $taskInfo = Agrotask::findOne(['id' => $model['taskid'], 'deleted' => 0]);
        if (!$taskInfo) {
            return array('status' => 1005, 'status_msg'=> 'no task info','message'=>'操作失败');
        }

        Agrotask::updateInfo(array(
            'id' => $model['taskid'],
            'geoStartTime' => $model['start'],
            'geoEndTime' => $model['end'],
        ));

        $edge_point = $this->stringTo2Array($taskInfo['edge_point']);
        $post_data = null;
        foreach ($edge_point as $edge) {
            if (($edge[0][0] == 85 || $edge[0][0] == 86) && ($edge[0][1] & 0x1)) {
                array_shift($edge);
                $post_data = ["border_point" => $edge];
                break;
            }
        }

        $post_data['task_id'] = $model['taskid'];
        $post_data['start'] = $model['start'];
        $post_data['end'] = $model['end'];
        $post_data['account'] = $self_account;
        $post_data['hardware_ids'] = $all_hardware_id;

        $data_string = json_encode($post_data);

        $ch = curl_init('http://localhost:8811/unlock/area');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );

        $result = curl_exec($ch);

        $unlock_result = json_decode($result, true);

        if (!$unlock_result || $unlock_result['result'] != 0) {
            return array('status' => 1006, 'status_msg'=> 'failed','message'=>'操作失败');
        }

        Agrotask::updateInfo(array(
            'id' => $model['taskid'],
            'isInGeo' => '0'
        ));

        return array('status' => 0, 'status_msg'=> 'ok','message'=> '解禁成功');
    }

    // web获取作业结果确认书列表
    public function actionQuerymissioncompletelist() {
        $actionName = 'actionQuerymissioncompletelist';
        $userData = $this->getUserInfo($actionName);

        if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
            $data = array('status' => 1001, 'status_msg'=> 'failed','message'=>'登录已经过期,请重新登录');
            return $data;
        }

        if (!isset($userData['items']['0']['account_info'])) {
            return array('status' => 1001, 'status_msg'=> 'failed','message'=>'登录已经过期,请重新登录');
        }

        $taskid = $this->getPostValue("taskid");
        if (!$taskid) {
            return array('status' => 1000,'status_msg'=> 'failed','message'=>'参数不合法');
        }

        $where = array();
        $where['task_id'] = $taskid;
        $missionList = AgroMissionComplete::getAndEqualWhere($where);

        if (!$missionList) {
            $missionList = [];
        }

        return array('status' => 0,'status_msg'=> 'ok','data'=>$missionList);
    }

    // web获取作业结果确认书详情
    public function actionMissioncompletedetail() {
        $actionName = 'actionMissioncompletedetail';
        $userData = $this->getUserInfo($actionName);

        if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
            $data = array('status' => 1001, 'status_msg'=> 'failed','message'=>'登录已经过期,请重新登录');
            return $data;
        }

        if (!isset($userData['items']['0']['account_info'])) {
            return array('status' => 1001, 'status_msg'=> 'failed','message'=>'登录已经过期,请重新登录');
        }

        $missionCompleteId = $this->getPostValue("id");
        if (!$missionCompleteId) {
            return array('status' => 1000,'status_msg'=> 'failed','message'=>'参数不合法');
        }

        $missionInfo = AgroMissionComplete::findOne(['id' => $missionCompleteId]);

        if (!$missionInfo) {
            return array('status' => 1008, 'status_msg'=> 'failed','message'=>'操作失败');
        }

        return array('status' => 0,'status_msg'=> 'ok','data'=>$missionInfo);
    }

    public function actionGetunreadcount() {
        $actionName = 'actionGetunreadcount';
        $userData = $this->getUserInfo($actionName);

        if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
            $data = array('status' => 1001, 'status_msg'=> 'failed','message'=>'登录已经过期,请重新登录');
            return $data;
        }

        if (!isset($userData['items']['0']['account_info'])) {
            return array('status' => 1002, 'status_msg'=> 'failed','message'=>'登录已经过期,请重新登录');
        }

        $uid = $userData['items']['0']['account_info']['user_id'];

        // get last read id
        $last_read_info = NotificationLastReadIdx::findOne(['uid' => $uid]);
        $last_read_id = 0;
        if ($last_read_info) {
            $last_read_id = intval($last_read_info['last_read_idx']);
        }

        // get public notification max id
        $r = PublicNotification::find()->orderBy(['id' => SORT_DESC])->one();
        $public_notification_max_id = $r['id'];
        if (!$public_notification_max_id) {
            $public_notification_max_id = 0;
        }

        // get unread count
        $unread_count = PublicNotification::find()
            ->where(['between', 'id', $last_read_id, $public_notification_max_id])
            ->andWhere(['or', 'uid="*"', 'uid='.$uid])
            ->count();

        return array('status' => 0, 'status_msg' => 'ok', 'data' => (int)$unread_count);
    }

    public function actionGetunread()
    {
        $actionName = 'actionGetunread';
        $userData = $this->getUserInfo($actionName);

        if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
            $data = array('status' => 1001, 'status_msg' => 'failed', 'message' => '登录已经过期,请重新登录');
            return $data;
        }

        if (!isset($userData['items']['0']['account_info'])) {
            return array('status' => 1002, 'status_msg' => 'failed', 'message' => '登录已经过期,请重新登录');
        }

        $uid = $userData['items']['0']['account_info']['user_id'];

        // get last read id
        $last_read_info = NotificationLastReadIdx::findOne(['uid' => $uid]);
        $last_read_id = 0;
        if ($last_read_info) {
            $last_read_id = intval($last_read_info['last_read_idx']);
        }

        // get public notification max id
        $r = PublicNotification::find()->orderBy(['id' => SORT_DESC])->one();
        $public_notification_max_id = intval($r['id']);
        if (!$public_notification_max_id) {
            $public_notification_max_id = 0;
        }

        // update last read id
        NotificationLastReadIdx::upsert($uid, $public_notification_max_id + 1);

        // get unread count
        $unread_content = PublicNotification::find()
            ->where(['between', 'id', $last_read_id, $public_notification_max_id])
            ->andWhere(['or', 'uid="*"', 'uid='.$uid])
            ->all();

        return array('status' => 0, 'status_msg' => 'ok', 'data' => $unread_content);
    }

    public function actionGetreaded() {
        $page = intval(Yii::$app->request->get("page"));
        $size = intval(Yii::$app->request->get("size"));
        if ($size < 1 || $size > 100 ) {
            $size = 10;
        }
        if ($page < 1 ) {
            $page = 1;
        }
        $start = ($page-1) * $size;
        $start = $start < 0 ? 0 : $start ;

        $actionName = 'actionGetunread';
        $userData = $this->getUserInfo($actionName);

        if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
            $data = array('status' => 1001, 'status_msg' => 'failed', 'message' => '登录已经过期,请重新登录');
            return $data;
        }

        if (!isset($userData['items']['0']['account_info'])) {
            return array('status' => 1002, 'status_msg' => 'failed', 'message' => '登录已经过期,请重新登录');
        }

        $uid = $userData['items']['0']['account_info']['user_id'];

        // get last read id
        $last_read_info = NotificationLastReadIdx::findOne(['uid' => $uid]);
        $last_read_id = 0;
        if ($last_read_info) {
            $last_read_id = intval($last_read_info['last_read_idx']);
        }

        $condition = [
            'and',
            [
                'between',
                'id',
                0,
                $last_read_id == 0 ? $last_read_id : $last_read_id - 1
            ],
            [
                'or',
                'uid="*"',
                'uid='.$uid
            ]
        ];

        $rows = PublicNotification::getAndEqualWhere($condition, $start, $size);

        return [
            'status' => 0,
            'status_msg' => 'ok',
            'data' => $rows,
            'count' => PublicNotification::find()->where($condition)->count()
        ];
    }

    public function actionVerifymodifyphonepermonth()
    {
        // user center auth failed
        $actionName = 'actionVerifymodifyphonepermonth';
        $userData = $this->getUserInfo($actionName);
        if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
            return array('status' => 1001, 'status_msg' => 'failed', 'message' => '登录已经过期,请重新登录');
        }

        $self_uid = $userData['items']['0']['account_info']['user_id'];

        // not boss
        $where = array();
        $where['uid'] = $self_uid;
        $where['deleted'] = '0';
        $activeCount = Agroactiveinfo::getAndEqualWhereCount($where);
        if ($activeCount <= 0) {
            return array('status' => 1002, 'status_msg' => 'failed', 'message' => '不是老板');
        }

        // get last modify time

        $last_modify_time = '0000-00';
        $last_modify_record = LastModifyPhoneTime::getLastModifyTs(['uid' => $self_uid]);
        if ($last_modify_record) {
            $last_modify_time = date('Y-m', $last_modify_record['last_modify_phone_time']);
        }
        $current_time = date('Y-m', time());

        if ($last_modify_time == $current_time) {
            return array('status' => 1003, 'status_msg' => 'failed', 'message' => '每月只能修改一次手机号');
        }

        return array('status' => 0, 'status_msg' => 'ok', 'message' => '当月无修改');
    }

    public function actionTransformowner() {
        $action_name = 'actionTransfromowner';
        $user_data = $this->getUserInfo($action_name);
        if (!$user_data || $user_data['status'] != '0' || $user_data['status_msg'] != 'ok') {
            return array('status' => 1001, 'status_msg' => 'failed', 'message' => '登录已经过期, 请重新登录');
        }

        $current_self_uid = $user_data['items']['0']['account_info']['user_id'];

        // pack all post params
        $params = array();
        $params['id'] = $this->getPostValue('id');
        $params['account'] = $this->getPostValue('account');
        $params['name'] = $this->getPostValue('name');
        $params['idcard'] = $this->getPostValue('idcard');
        $params['capcha'] = $this->getPostValue('capcha');

        if (!isset($params['id']) || !isset($params['account']) || !isset($params['name']) || !isset($params['idcard'])) {
            return array('status' => 1002,'status_msg'=> 'failed','message'=>'参数不合法');
        }

        // get active info
        $current_active_info = Agroactiveinfo::findOne([
            'id' => $params['id'],
            'uid' => $current_self_uid,
            'deleted' => '0'
        ]);
        if (!$current_active_info) {
            return array('status' => 1003, 'status_msg' => 'failed', 'message' => '找不到需要转让的飞机信息');
        }

        // check account is valid
        $dji_user = new DjiUser();
        $user_info = $dji_user->direct_get_user($params['account']);
        if ($user_info && $user_info['status'] == '0' && $user_info['status_msg'] == 'ok' ) {
            $params['uid'] =  $user_info['items']['0']['user_id'];
            //$model['nickname'] = $userInfo['items']['0']['nickname'];
        } else {
            return array('status' => 1004,'status_msg'=> 'failed','message'=>'对方的账号不正确');
        }

        // get phone & check code
        if ($this->isInChina()) {
            $boss_phone = $this->get_boss_phone($current_self_uid);
            if (!$boss_phone) {
                return ['status' => 1005, 'status_msg' => 'failed', 'message' => '找不到老板信息'];
            }

            $paramsString = $boss_phone . $params['capcha'];
            $luckkey = "_code_" . md5($paramsString);
            $luckdata = Yii::$app->cache->get($luckkey);
            if (!$luckdata || $luckdata != $params['capcha']) {
                // check code failed
                $data = array('status' => 1008, 'status_msg' => 'failed', 'message' => '验证码错误');
                return $data;
            }
            Yii::$app->cache->delete($luckkey);
        }

        // modify active info
        $current_active_info->account = $params['account'];
        $current_active_info->uid = $params['uid'];
        $current_active_info->team_id = 0;
        $current_active_info->idcard = $params['idcard'];
        $current_active_info->phone = '';
        $current_active_info->updated_at = date('Y-m-d H:i:s', time());
        $current_active_info->save();

        // clear flyer control info
        Agroactiveflyer::deleteAll(['active_id' => $params['id']]);

        $this->make_boss_controllable(
            $current_active_info['id'],
            $current_active_info['hardware_id'],
            $user_info['items']['0']['email'],
            $user_info['items']['0']['user_id'],
            $user_info['items']['0']['nickname']
        );

        // add operation record
        // sender
        $record = array();
        $record['upper_uid'] = $record['uid'] = $current_self_uid;
        $record['team_id'] = 0;
        $record['operator'] = $user_data['items']['0']['account_info']['email'];
        $record['type'] = '植保机拥有者';
        $record['content'] = '飞行器转让';
        $record['detail'] = '转让: <i>'.$current_active_info['nickname'].'</i> 给 账号：<i>'.$params['account'].'</i>'.' 身份证：<i>'.$params['idcard'].'</i>';
        $record['ip'] = $this->get_client_ip();
        Agrorecord::add($record);

        // receiver
        $record = array();
        $record['upper_uid'] = $record['uid'] = $params['uid'];
        $record['team_id'] = 0;
        $record['operator'] = $user_data['items']['0']['account_info']['email'];
        $record['type'] = '植保机拥有者';
        $record['content'] = ' 飞行器接收';
        $record['detail'] = '接收: <i>'.$user_data['items']['0']['account_info']['email'].'</i> 的 <i>'.$current_active_info['nickname'].'</i>';
        $record['ip'] = $this->get_client_ip();
        Agrorecord::add($record);

        // push notification to user
        $notification = array();
        $notification['uid'] = $params['uid'];
        $notification['date'] = time() * 1000;
        if ($this->isInChina()) {
            $notification['content'] =
                "您已经成功接受机身序列号为 {$current_active_info['body_code']} 的植保机。转让植保机同时需转让无人机驾驶飞行器责任保险，否则因操作导致发生事故的，保险公司不承担保险责任。<br>".
                '请您联系此植保机的上一个所有者确认保险转让完毕。请注意此台植保机的用户关怀计划到期日期。';
        } else if ($this->startsWith($_SERVER['HTTP_HOST'], 'ja-ag.dji.com')) {
            $notification['content'] = "SNナンバー {$current_active_info['body_code']} の機体を受け入れました。";
        } else if ($this->startsWith($_SERVER['HTTP_HOST'], 'kr-ag.dji.com')) {
            $notification['content'] = "당신은 성공적으로 수락 한 몸 일련 번호 {$current_active_info['body_code']} 안개 기계";
        } else {
            $notification['content'] = "You have accepted the MG-1S product serial number {$current_active_info['body_code']}.";
        }
        PublicNotification::add($notification);

        return array('status' => 0,'status_msg'=> 'ok','message'=>'操作完成');
    }

    protected function get_boss_phone($uid, $only_flyer_info = false) {
        // get phone from flyer info
        $flyer_info = Agroflyer::find()->where([
            'uid' => $uid,
            'upper_uid' => $uid,
            'deleted' => '0'
        ])->andWhere([
            '!=',
            'phone',
            'NULL'
        ])->one();
        if ($flyer_info) {
            return $flyer_info->phone;
        }

        if ($only_flyer_info) {
            return null;
        }

        // get phone from active info
        $active_info = Agroactiveinfo::find()->where([
            'uid' => $uid,
            'deleted' => '0'
        ])->andWhere([
            '!=',
            'phone',
            'NULL'
        ])->one();
        if ($active_info) {
            return $active_info->phone;
        }

        return null;
    }

    public function actionIsfirstvisit()
    {
        $actionName = 'actionIsFirstVisit';
        $userData = $this->getUserInfo($actionName);
        if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
            return array('status' => 1001, 'status_msg' => 'failed', 'message' => '登录已经过期,请重新登录');
        }

        $self_uid = $userData['items']['0']['account_info']['user_id'];
        $self_account = $userData['items']['0']['account_info']['email'];

        $is_first_visit = FirstTimeVisit::isFirstTimeVisit($self_uid);

        return ['status' => 0, 'status_msg' => 'ok', 'data' => $is_first_visit];
    }

    public function actionMarkfirstvisited()
    {
        $actionName = 'actionIsFirstVisit';
        $userData = $this->getUserInfo($actionName);
        if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
            return array('status' => 1001, 'status_msg' => 'failed', 'message' => '登录已经过期,请重新登录');
        }

        $self_uid = $userData['items']['0']['account_info']['user_id'];
        $self_account = $userData['items']['0']['account_info']['email'];

        FirstTimeVisit::markVisited($self_uid, $self_account);

        return ['status' => 0, 'status_msg' => 'ok'];
    }

    public function actionPhonecallcountrycode() {
        return [
            'status' => 0,
            'status_msg' => 'ok',
            'data' => [
                ['name' => 'China',         'CountryCode' => 'CN', 'CallCode' => '+86'],
                ['name' => 'Hong Kong',     'CountryCode' => 'HK', 'CallCode' => '+852'],
                ['name' => 'Macau',         'CountryCode' => 'MO', 'CallCode' => '+853'],
                ['name' => 'Taiwan',        'CountryCode' => 'TW', 'CallCode' => '+886'],
                ['name' => 'Japan',         'CountryCode' => 'JP', 'CallCode' => '+81'],
                ['name' => 'South Korea',   'CountryCode' => 'KR', 'CallCode' => '+82'],
            ]
        ];
    }

    protected function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    protected function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }

    protected function putObjectToOSS($name, $data) {
        if ($this->isInChina()) {
            try {
                $oss_client = new OssClient(
                    Yii::$app->params['OSS_ENV']['access_key_id'],
                    Yii::$app->params['OSS_ENV']['access_key_secret'],
                    Yii::$app->params['OSS_ENV']['endpoint'],
                    false);

                $options = [
                    OssClient::OSS_HEADERS => [
                        'Content-Type' => 'image/jpeg'
                    ]
                ];

                $oss_client->putObject(Yii::$app->params['OSS_ENV']['bucket'], $name, $data, $options);

                return ['result' => 0];
            } catch (OssException $e) {
                return ['result' => 1, 'msg' => $e->getMessage()];
            }
        } else {
            try {
                $s3 = new S3Client([
                    'version' => 'latest',
                    'region' => 'ap-northeast-2',
                    'credentials' => [
                        'key' => Yii::$app->params['OSS_ENV']['access_key_id'],
                        'secret' => Yii::$app->params['OSS_ENV']['access_key_secret'],
                    ]
                ]);
                $s3->putObject([
                    'Bucket' => Yii::$app->params['OSS_ENV']['bucket'],
                    'Key' => $name,
                    'Body' => $data,
                    'ACL' => 'public-read',
                    'ContentType' => 'image/jpeg'
                ]);
            } catch (\Exception $e) {
                return ['result' => 1, 'msg' => $e->getMessage()];
            }
        }
    }

    protected function delOssObject($name) {
        if ($this->isInChina()) {
            try {
                $oss_client = new OssClient(
                    Yii::$app->params['OSS_ENV']['access_key_id'],
                    Yii::$app->params['OSS_ENV']['access_key_secret'],
                    Yii::$app->params['OSS_ENV']['endpoint'],
                    false);
                $oss_client->deleteObject(Yii::$app->params['OSS_ENV']['bucket'], $name);

                return ['result' => 0];
            } catch (OssException $e) {
                return ['result' => 1, 'msg' => $e->getMessage()];
            }
        } else {
            try {
                $s3 = new S3Client([
                    'version' => 'latest',
                    'region' => 'ap-northeast-2',
                    'credentials' => [
                        'key' => Yii::$app->params['OSS_ENV']['access_key_id'],
                        'secret' => Yii::$app->params['OSS_ENV']['access_key_secret'],
                    ]
                ]);
                $s3->deleteObject([
                    'Bucket' => Yii::$app->params['OSS_ENV']['bucket'],
                    'Key' => $name
                ]);

                return ['result' => 0];
            } catch (\Exception $e) {
                return ['result' => 1, 'msg' => $e->getMessage()];
            }
        }
    }

    protected function isInChina() {
        return (isset(Yii::$app->params['ENV_COUNTRY']) && Yii::$app->params['ENV_COUNTRY'] == 'CN');
    }

    protected function isInTestEnv() {
        return (isset(Yii::$app->params['CURRENT_ENV']) && Yii::$app->params['CURRENT_ENV'] == 'test');
    }

    public function actionSetuseravatar() {
        $actionName = 'actionSetuseravatar';
        $userData = $this->getUserInfo($actionName);
        if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
            return array('status' => 1001, 'status_msg' => 'failed', 'message' => '登录已经过期,请重新登录');
        }

        $self_uid = $userData['items']['0']['account_info']['user_id'];
        $self_account = $userData['items']['0']['account_info']['email'];

        $avatar_base64_data = Yii::$app->request->post('data', '');
        // cut base64 image header
        $avatar_base64_data = preg_replace('/^data.*,/', '', $avatar_base64_data, 1);
        $avatar_raw_data = base64_decode($avatar_base64_data, true);
        $avatar_size = strlen($avatar_raw_data);

        // avatar data check
        if ($avatar_raw_data === false || $avatar_size === 0) {
            return ['status' => 1002, 'status_msg' => 'failed', 'message' => '错误的图片数据'];
        }

        if ($avatar_size > (5 * 1024 * 1024)) {
            return ['status' => 1003, 'status_msg' => 'failed', 'message' => '图片大小超过限制'];
        }

        // gen file name
        $avatar_filename = md5($self_uid.date('Y-m-d H:i:s'));
        $avatar_filename = 'user_avatar/'.$avatar_filename;

        $result = $this->putObjectToOSS($avatar_filename, $avatar_raw_data);
        if ($result['result'] != 0) {
            return ['status' => 1004, 'status_msg' => 'failed', 'message' => $result['msg']];
        }

        // save to db
        $old_filename = UserAvatar::setUserAvatar($self_account, $self_uid, $avatar_filename);
        if ($old_filename) {
            // del old oss object
            $this->delOssObject($old_filename);
        }

        // the whole new path
        $whole_new_path = Yii::$app->params['OSS_ENV']['bind_domain'].$avatar_filename;

        return ['status' => 0, 'status_msg' => 'ok', 'path' => $whole_new_path];
    }

    public function actionI18n() {
        $lang = Yii::$app->request->get('lang', 'cn');

        return isset(Yii::$app->params['I18N_DATA'][$lang])
            ? Yii::$app->params['I18N_DATA'][$lang] : Yii::$app->params['I18N_DATA']['cn'];
    }

    public function actionCheckandsetphone() {
        $action_name = 'actionCheckandsetphone';
        $user_data = $this->getUserInfo($action_name);
        if (!$user_data || $user_data['status'] != '0' || $user_data['status_msg'] != 'ok') {
            return array('status' => 1001, 'status_msg' => 'failed', 'message' => '登录已经过期,请重新登录');
        }

        $self_uid = $user_data['items']['0']['account_info']['user_id'];

        $id = $this->getPostValue('id');
        $phone = $this->getPostValue('phone');
        $code = $this->getPostValue('code');

        // check params
        if (empty($id) || empty($phone) || empty($code)) {
            return ['status' => 1002, 'status_msg' => 'failed', 'message' => '参数错误'];
        }

        // check code
        $cache_key = "_code_".md5($phone.$code);
        $cache_data = Yii::$app->cache->get($cache_key);
        if (!$cache_data || $code != $cache_data) {
            return ['status' => 1003, 'status_msg' => 'failed', 'message' => '验证码错误'];
        }

        // get boss flyer info
        $flyer_info = Agroflyer::findOne(['id' => $id]);
        if (!$flyer_info || $flyer_info->upper_uid != $self_uid || $flyer_info->uid != $self_uid) {
            return ['status' => 1004, 'status_msg' => 'failed', 'message' => '权限不足'];
        }

        // write the new phone num to db
        $flyer_info->phone = $phone;
        $flyer_info->save();

        return ['status' => 0, 'status_msg' => 'ok'];
    }

    public function actionCheckuserworkinfo() {
        $account = $this->getPostValue('account');

        if (empty($account)) {
            return ['status' => 1001, 'msg' => '参数错误'];
        }

        $row = Agroflyer::findOne(['account' => $account]);
        if (!$row) {
            return ['status' => 1002, 'msg' => '找不到账号'];
        }

        $area = Viewworkinfo::get_work_info($row->uid);
        if (!$area) {
            return ['status' => 1003, 'msg' => '无对应值'];
        }

        return ['status' => 0, 'data' => intval($area)];
    }

    protected function query_or_create_boss_team($boss_account, $boss_uid, $boss_nickname) {
        $team = Agroteam::findOne(['uid' => $boss_uid, 'deleted' => '0']);
        if ($team) {
            return $team;
        }

        // if not exists, create one
        $model = array();
        $model['uid'] = $model['captain'] = $boss_uid;
        $model['name'] = $boss_nickname;
        $model['ip'] = $this->get_client_ip();
        $model['upper_teamid'] = '0';
        $model['showed'] = '1';
        $team = Agroteam::add($model);

        if ($team->id <= 0) {
            return null;
        }

        $model = array();
        $model['nickname'] = $boss_nickname;
        $model['team_id'] = $team->id;
        $model['upper_uid'] = $model['uid'] = $boss_uid;
        $model['account'] = $boss_account;
        $model['job_level'] = '1';
        $model['ip'] = $this->get_client_ip();
        Agroflyer::add($model);

        return $team;
    }

    public function actionIuavsendbackup() {
        $params['account'] = Yii::$app->request->post('account', '');
        $params['body_code'] = Yii::$app->request->post('body_code', '');
        $params['hardware_id'] = Yii::$app->request->post('hardware_id', '');
        $params['signature'] = Yii::$app->request->post('signature', '');

        (new DjiUser)->add_log(json_encode($params), 'iuav_send_backup');

        if (empty($params['account']) || empty($params['body_code'])
            || empty($params['hardware_id']) || empty($params['signature'])) {

            return ['status' => 1001, 'status_msg' => 'failed', 'message' => '参数不足'];
        }

        $hmackey = 'xTgF747f9QDbymeayKiV';
        $params_string = $params['account'].$params['body_code'].$params['hardware_id'];
        $now_sign = strtoupper(hash_hmac('sha256', $params_string, $hmackey));

        if ($params['signature'] != $now_sign) {
            return ['status' => 1005, 'status_msg' => 'failed', 'message' => '签名错误'];
        }

        // check account is valid
        $dji_user = new DjiUser();
        $user_info = $dji_user->direct_get_user($params['account']);
        if ($user_info && $user_info['status'] == '0' && $user_info['status_msg'] == 'ok' ) {
            $params['uid'] =  $user_info['items']['0']['user_id'];
            $params['nickname'] = $user_info['items']['0']['nickname'];
        } else {
            return ['status' => 1010, 'status_msg' => 'failed', 'message' => '目标账号不正确'];
        }

        $team = $this->query_or_create_boss_team($params['account'], $params['uid'], $params['nickname']);
        if (!$team) {
            return ['status' => 1015, 'status_msg' => 'failed', 'message' => '创建老板团队失败'];
        }

        $row = Agroactiveinfo::findOne(['body_code' => $params['body_code'], 'deleted' => '0']);
        if (!$row) {
            //return ['status' => 1015, 'status_msg' => 'failed', 'message' => '无相关飞机信息'];
            $row = new Agroactiveinfo;
        }

        $row->order_id = '';
        $row->pol_no = '';
        $row->exp_tm = '';
        $row->query_flag = 1;
        $row->account = $params['account'];
        $row->uid = $params['uid'];
        $row->team_id = $team->id;
        $row->idcard = '';
        $row->phone = '';
        $row->body_code = $params['body_code'];
        $row->hardware_id = $params['hardware_id'];
        $row->type = 'mg-1s';
        $row->is_active = 1;
        $row->deleted = 0;
        $row->nickname = '备用机';
        $row->locked = 0;
        $row->locked_notice = 0;
        $row->timelocked = 0;
        $row->timelocked_notice = 0;
        $row->lock_begin = '';
        $row->lock_end = '';
        $row->is_online = 0;
        $row->msg_sum = 100;
        $row->ip = $this->get_client_ip();
        $row->updated_at = date('Y-m-d H:i:s', time());
        $row->save();

        // clear flyer control info
        Agroactiveflyer::deleteAll(['hardware_id' => $row->hardware_id]);

        return ['status' => 0, 'status_msg' => 'ok'];
    }

    protected function restore_drone($body_code) {
        // clear active info
        $row = Agroactiveinfo::findOne(['body_code' => $body_code]);
        if ($row) {
            $row->order_id = '';
            $row->pol_no = '';
            $row->exp_tm = '';
            $row->account = null;
            $row->uid = null;
            $row->team_id = 0;
            $row->idcard = '111111111111111111';
            $row->phone = '11111111111';
            $row->is_active = 1;
            $row->deleted = 0;
            $row->nickname = '备用机';
            $row->locked = 0;
            $row->locked_notice = 0;
            $row->timelocked = 0;
            $row->timelocked_notice = 0;
            $row->lock_begin = null;
            $row->lock_end = null;
            $row->msg_sum = 100;
            $row->ip = $this->get_client_ip();
            $row->updated_at = date('Y-m-d H:i:s', time());
            $row->save();

            // clear flyer control info
            Agroactiveflyer::deleteAll(['hardware_id' => $row->hardware_id]);
        }
    }

    protected function exchange_insurance($pre_body_code, $new_body_code) {
        $pre_active_info = Agroactiveinfo::findOne(['body_code' => $pre_body_code]);
        $new_active_info = Agroactiveinfo::findOne(['body_code' => $new_body_code]);
        if ($pre_active_info && $new_active_info) {
            $new_active_info->pol_no = $pre_active_info->pol_no;
            $new_active_info->exp_tm = $pre_active_info->exp_tm;
        }
    }

    public function actionAftersalerecvbackup() {
        $params['pre_body_code'] = Yii::$app->request->post('pre_body_code', '');
        $params['new_body_code'] = Yii::$app->request->post('new_body_code', '');

        $params['signature'] = Yii::$app->request->post('signature', '');

        (new DjiUser)->add_log(json_encode($params), 'aftersale_recv_backup');

        if (empty($params['pre_body_code']) || empty($params['new_body_code']) || empty($params['signature'])) {
            return ['status' => 1001, 'status_msg' => 'failed', 'message' => '参数不足'];
        }

        $hmackey = 'xTgF747f9QDbymeayKiV';
        $params_string = $params['pre_body_code'].$params['new_body_code'];
        $now_sign = strtoupper(hash_hmac('sha256', $params_string, $hmackey));

        if ($params['signature'] != $now_sign) {
            return ['status' => 1005, 'status_msg' => 'failed', 'message' => '签名错误'];
        }

        $this->exchange_insurance($params['pre_body_code'], $params['new_body_code']);
        $this->restore_drone($params['pre_body_code'], $params['new_body_code']);

        return ['status' => 0, 'status_msg' => 'ok'];
    }

    public function actionListmanager() {
        $actionName = 'actionListmanager';
        $userData = $this->getUserInfo($actionName);
        if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
            return array('status' => 1001, 'status_msg' => 'failed', 'message' => '登录已经过期,请重新登录');
        }

        $self_uid = $userData['items']['0']['account_info']['user_id'];

        $rows = Manager::get_all_manager($self_uid);

        $data = [];
        foreach ($rows as $row) {
            $tmp = $row->toArray();

            $ids = (new \yii\db\Query())
                ->select('hardware_id')
                ->from(ManagerDrone::tableName())
                ->where(['account' => $row->account])
                ->all();

            $drone_info = (new \yii\db\Query())
                ->select('hardware_id,nickname')
                ->from(Agroactiveinfo::tableName())
                ->where(['in', 'hardware_id', $ids])
                ->all();

            $tmp['drone_info'] = $drone_info;

            $data[] = $tmp;
        }

        return ['status' => 0, 'status_msg' => 'ok', 'data' => $data];
    }

    public function actionAddmanager() {
        $actionName = 'actionAddmanager';
        $userData = $this->getUserInfo($actionName);
        if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
            return array('status' => 1001, 'status_msg' => 'failed', 'message' => '登录已经过期,请重新登录');
        }

        $self_uid = $userData['items']['0']['account_info']['user_id'];
        $self_account = $userData['items']['0']['account_info']['email'];

        $manager_account = Yii::$app->request->post('account', '');
        $level = Yii::$app->request->post('level', '');

        Manager::add_manager($self_account, $self_uid, $manager_account, $level);

        return ['status' => 0, 'status_msg' => 'ok'];
    }

    public function actionDelmanager() {
        $actionName = 'actionDelmanager';
        $userData = $this->getUserInfo($actionName);
        if (!$userData || $userData['status'] != '0' || $userData['status_msg'] != 'ok') {
            return array('status' => 1001, 'status_msg' => 'failed', 'message' => '登录已经过期,请重新登录');
        }

        $self_uid = $userData['items']['0']['account_info']['user_id'];

        $id = Yii::$app->request->post('id', '');

        Manager::del_manager($id);

        return ['status' => 0, 'status_msg' => 'ok'];
    }

    public function actionBindmanagedrone() {
        $user_data = $this->get_user_info();
        if (!$user_data) {
            return ['status' => 1001, 'status_msg' => 'failed', 'message' => '登录已经过期, 请重新登录'];
        }

        $account = $this->getPostValue('account');
        $hardware_ids = $this->getPostValue('hardware_ids');

        if (!isset($account) || !isset($hardware_ids)) {
            return ['status' => 1005, 'status_msg' => 'failed', 'message' => '参数错误'];
        }

        // check hardware ids
        $hardware_ids = explode(',', $hardware_ids);
        $clean_hardware_ids = [];
        foreach ($hardware_ids as $hardware_id) {
            $hardware_id = trim($hardware_id);
            if (!empty($hardware_id) && !in_array($hardware_id, $clean_hardware_ids)) {
                $clean_hardware_ids[] = $hardware_id;
            }
        }

        if (count($clean_hardware_ids) <= 0) {
            return ['status' => 1010, 'status_msg' => 'failed', 'message' => 'hardware_ids错误'];
        }

        ManagerDrone::deleteAll(['account' => $account]);
        foreach ($clean_hardware_ids as $clean_hardware_id) {
            ManagerDrone::add_control($account, $clean_hardware_id);
        }

        return ['status' => 0, 'status_msg' => 'ok'];
    }

    public function actionQuerybossteam() {
        $user_data = $this->get_user_info();
        if (!$user_data) {
            return ['status' => 1001, 'status_msg' => 'failed', 'message' => '登录失效'];
        }

        $boss_uid = $this->getPostValue('uid');
        if (!isset($boss_uid)) {
            return ['status' => 1005, 'status_msg' => 'failed', 'message' => '参数错误'];
        }

        $rows = Agroteam::findAll(['uid' => $boss_uid, 'deleted' => 0]);

        return ['status' => 0, 'status_msg' => 'ok', 'data' => $rows];
    }

    protected function make_boss_controllable($active_id, $hardware_id, $boss_account, $boss_uid, $boss_nickname) {
        $active_info = Agroactiveinfo::findOne(['id' => $active_id, 'deleted' => 0]);
        if (!$active_info) {
            return;
        }

        // make sure boss team exists
        $team_info = Agroteam::findOne(['uid' => $boss_uid, 'deleted' => 0]);
        if (!$team_info) {
            $model = array();
            $model['uid'] = $boss_uid;
            $model['name'] = $boss_nickname;
            $model['ip'] = $this->get_client_ip();
            $model['upper_teamid'] = '0';
            $model['showed'] = '1';
            $team_info = Agroteam::add($model);
        }

        // make sure boss flyer exists
        $flyer_info = Agroflyer::findOne(['upper_uid' => $boss_uid, 'uid' => $boss_uid, 'deleted' => 0]);
        if ($flyer_info) {
            // fix team info
            if ($flyer_info['team_id'] != $team_info['id']) {
                $team_info = Agroteam::findOne(['id' => $flyer_info['team_id']]);
            }
        } else {
            $model = array();
            $model['nickname'] = $boss_nickname;
            $model['team_id'] = $team_info['id'];
            $model['upper_uid'] = $model['uid'] = $boss_uid;
            $model['account'] = $boss_account;
            $model['job_level'] = '1'; //将自己设为队长
            $model['ip'] = $this->get_client_ip();
            $flyer_info = Agroflyer::add2($model);
        }

        // make sure active flyer exists
        $active_flyer_info = Agroactiveflyer::findOne([
            'active_id' => $active_id,
            'hardware_id' => $hardware_id,
            'flyer_id' => $flyer_info['id'],
            'flyer_uid' => $flyer_info['uid'],
            'deleted' => 0
        ]);
        if (!$active_flyer_info) {
            $model = [];
            $model['active_id'] = $active_id;
            $model['hardware_id'] = $hardware_id;
            $model['flyer_id'] = $flyer_info['id'];
            $model['flyer_uid'] = $flyer_info['uid'];
            $model['showed'] = 1;
            $model['deleted'] = 0;
            $model['ip'] = $this->get_client_ip();
            Agroactiveflyer::add($model);
        }

        // at last, set control team
        $active_info['team_id'] = $team_info['id'];
        $active_info->save();
    }

    public function actionNoticerepairdrones() {
        $params['repaired_cassidy_sn'] = Yii::$app->request->post('repaired_cassidy_sn', '');
        $params['repaired_hardware_id'] = Yii::$app->request->post('repaired_hardware_id', '');
        $params['repairing_cassidy_sn'] = Yii::$app->request->post('repairing_cassidy_sn', '');
        $params['repairing_hardware_id'] = Yii::$app->request->post('repairing_hardware_id', '');
        $params['signature'] = Yii::$app->request->post('signature', '');

        (new DjiUser)->add_log(json_encode($params), 'notice_repair_drones');

        $hmackey = 'xTgF747f9QDbymeayKiV';
        $params_string = $params['repaired_cassidy_sn'].$params['repaired_hardware_id'].$params['repairing_cassidy_sn'].$params['repairing_hardware_id'];
        $now_sign = strtoupper(hash_hmac('sha256', $params_string, $hmackey));

        if ($params['signature'] != $now_sign) {
            return ['status' => 1005, 'status_msg' => 'failed', 'message' => '签名错误'];
        }

        $active_info = Agroactiveinfo::findOne([
            'body_code' => $params['repaired_cassidy_sn'],
            'hardware_id' => $params['repaired_hardware_id'],
            'deleted' => 0
        ]);
        if (!$active_info) {
            return ['status' => 1010, 'status_msg' => 'failed', 'message' => '飞机不存在'];
        }

        $active_info->body_code = $params['repairing_cassidy_sn'];
        $active_info->hardware_id = $params['repairing_hardware_id'];
        $active_info->save();

        return ['status' => 0, 'status_msg' => 'ok'];
    }
}
