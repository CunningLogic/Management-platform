<?php

namespace app\controllers;

use app\models\AgroMissionComplete;
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
use app\components\Djihmac;
use PHPExcel;
use PHPExcel_Writer_Excel2007;
use PHPExcel_Style_Border;
use PHPExcel_Style_Alignment;
use PHPExcel_IOFactory;
use PHPExcel_Style_Fill;
use  yii\web\Session;
class AdministratorController extends Controller
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
          if(($newItems['email'] = $userData['items']['0']['account_info']['email']) == 'cassie.deng@dji.com') {
              return $userData;
          } else {
             $data = array('status' => 1001,'status_msg'=> $status_msg,'message'=>'账号不合法');
             return $data;
          }
          
        }else{
           $data = array('status' => 1001,'status_msg'=> $status_msg,'message'=>'登录已经过期,请重新登录');
           return $data;
        }
    }
    //只有boss才可以修改
    /*
    * 修改老板的名称  /apiuser/editboss
    * @parameter string bossname  老板名称
    * "status":0 和 "status_msg":"ok" 表示修改成功
    * "status":1000 表示参数不合法；"status":1001 表示登录已经过期,请重新登录；"status":1004 表示5秒内重复提交,请稍后重试；
    * "status":1007 表示权限不足；
    */
    public function actionEditboss()
    {

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
      if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'user/userinfo');
      } 
      $actionName = 'actionUserinfo';
      $userData = $this->getUserInfo($actionName); 
      $data = array('data' => array() );
      $list = array();        
      if ($userData && $userData['status'] == '0' && $userData['status_msg'] == 'ok') { 
           $where = $newItems = array();
           $newItems['user_type'] = 1;
           $where['deleted'] = '0';       
           if (isset($userData['items']['0']['account_info'])) {
              $where['uid'] = $userData['items']['0']['account_info']['user_id'];
              $newItems['email'] = $userData['items']['0']['account_info']['email'];            
              $newItems['user_id'] = $userData['items']['0']['account_info']['user_id'];
              $newItems['country'] = '';
              $newItems['gender'] = '';
              $newItems['avatar'] = '';       
           }
           $userData['items'] = $newItems; 
          return $userData; 
      }
      return $userData;
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
      if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'user/aerocraft');
      }
      $model = array();
      $page = intval(Yii::$app->request->get("page"));
      $size = intval(Yii::$app->request->get("size"));
      if ($size < 1 || $size > 100 ) {
          $size = 30;
      }
      if ($page < 1 ) {
          $page = 1;
      }
      $start = ($page-1) * $size;
      $start = $start < 0 ? 0 : $start ;
      $status_msg = 'failed';    
      $actionName = 'actionAerocraft';
      $userData = $this->getUserInfo($actionName);
      $data = array('data' => array() );
      $list = array();     
      if ($userData && $userData['status'] == '0' && $userData['status_msg'] == 'ok') {
      $where = $newItems = $newFlyer = array();
      if (isset($userData['items']['0']['account_info'])) {
            $where['deleted'] = '0';
            $activeCount = Agroactiveinfo::getAndEqualWhereCount($where);
            $fields = 'pol_no,exp_tm,query_flag,id,body_code,hardware_id,type,nickname,team_id,locked,lock_begin,lock_end,created_at, is_online';
            $activeInfo = Agroactiveinfo::getPoliciesWhereOrderByIsOnline($where,$fields,$start,$size); 
            if ( $activeInfo && is_array( $activeInfo)) {
               foreach ($activeInfo as $key => $value) {
                  $value['pol_str'] =  Agropolicies::getPolNoStr($value['pol_no'],$value['query_flag'],$value['exp_tm']);              
                  if ($value['team_id'] > 0) {  //说明这架飞机属于这个teamid                    
                      $where['id'] = $value['team_id'];                        
                      $teamInfo = Agroteam::getAndEqualWhere($where, 0,1);
                      $newTeamInfo = array();
                      if ($teamInfo && is_array($teamInfo)) {
                          $flyerWhere = array();
                          $flyerWhere['deleted'] = '0';
                          //$flyerWhere['team_id'] = $value['team_id'];
                          $flyerWhere['active_id'] = $value['id'];
                          $flyerWhere['hardware_id'] = $value['hardware_id'];
                          $fields = 'agro_flyer.id as flyerid,agro_flyer.uid,agro_flyer.realname,agro_flyer.account,agro_flyer.idcard,agro_flyer.phone,agro_flyer.avatar,agro_flyer.job_level,agro_flyer.address';
                          $flyerInfo = Agroactiveflyer::getFlyerWhere($flyerWhere,$fields,0, -1);//var_dump($flyerWhere); var_dump($flyerInfo);die;//飞手信息是通过agroactiveflyer这张表查出来的
                          $newTeamInfo = array('id' => $teamInfo['0']['id'] ,'name' => $teamInfo['0']['name']);
                          if(empty($flyerInfo)){
                              $newTeamInfo['flyer_info'] = null;
                          }else{
                              $newTeamInfo['flyer_info'] = $flyerInfo;
                          }
                          $newTeamInfo['flyer_count'] = count($flyerInfo);
                          $value['team_info'] = $newTeamInfo; 
                      }else{
                          $value['team_info'] = null;
                      }                        
                  }
                  $value['exp_tm'] = substr($value['exp_tm'], 0,4).'-'.substr($value['exp_tm'], 4,2).'-'.substr($value['exp_tm'], 6,2);
                  $value['aircraft_status'] = $value['is_online']; //aircraft_status ：0 未联网 ；1 正在作业
                  $activeInfo[$key] = $value;
               }
            }
            $list = array();
            $list['status'] = 0;  
            $list['status_msg'] = 'ok';  
            $list['count'] = $activeCount;                
            $list['data'] = $activeInfo;               
            return $list;            
      }
      $data = array('status' => 1007, 'status_msg'=> $status_msg,'message'=>'权限不足');
      return $data;
    }
    return $userData;
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
    public function actionEditaerocraft()
    {

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
       if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'user/teamname');
      }    
      $actionName = 'actionListteam';
      $userData = $this->getUserInfo($actionName);
      $data = array('data' => array() );
      $list = array();
      $status_msg = 'failed';       
      if ($userData && $userData['status'] == '0' && $userData['status_msg'] == 'ok') {
        $where = $newData =  $newItems = $newFlyer = array();
        if (isset($userData['items']['0']['account_info'])) {
          //$where['uid'] = $userData['items']['0']['account_info']['user_id'];
          $where['deleted'] = '0';
          $activeInfo = Agroactiveinfo::getAndEqualWhere($where, 0,1);
          foreach ($activeInfo as $k => $v) {
              if ($v && is_array($v)) {
                $fields = 'agro_team.name,agro_team.id,agro_flyer.id as flyerid,agro_flyer.realname,agro_flyer.job_level,agro_flyer.avatar,agro_flyer.deleted as flyer_deleted';
                //$where['showed'] = '1';
                $teamInfo = Agroteam::getFlyerWhere($where,$fields,0,-1);
                if ( $teamInfo && is_array( $teamInfo)) {
                   foreach ($teamInfo as $key => $value) {
                       $newItems[$value['id']] =  array('id' => $value['id'],'name' => $value['name']);
                       if ($value['flyerid'] > 0 && $value['flyer_deleted'] == '0') {
                           $newFlyer[$value['id']][] = array('flyerid' => $value['flyerid'],'realname' => $value['realname'],'job_level' => $value['job_level'] ,'avatar' => $value['avatar']);                        
                       }
                       
                   }
                   foreach ($newItems as $key => $value) {
                       $value['flyer_info'] = array();
                       if (isset($newFlyer[$value['id']])) {
                          $value['flyer_info'] = $newFlyer[$value['id']];
                       }                    
                       $newData[] = $value;
                   }                 
                   
                }
                $list['status'] = 0;  
                $list['status_msg'] = 'ok';                 
                $list['data'] = $newData;                         
              } 
          }
          return $list;         
        }
        $data = array('status' => 1007, 'status_msg'=> $status_msg,'message'=>'权限不足');
        return $data;
      }
      return $userData;
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
      
    }
    /*
    * 添加团队里面的人员  /apiuser/addflyer
    *  @parameter string teamid  团队id
    *  @parameter string account dji账号邮箱
    *  return {"status":0,"status_msg":"ok","addid":1,"data":{"team_id":"2","account":"19193213@qq.com","uid":"22410717222020942"}}
    *  "status":0 和 "status_msg":"ok" 表示创建成功
    * "status":1000 表示参数不合法；"status":1001 表示登录已经过期,请重新登录；"status":1004 表示5秒内重复提交,请稍后重试；"status":1005 表示已经存在相同的名称；
    *  "status":1006 表示条数超过20条限制；"status":1007 表示权限不足；"status":1008 已经添加该用户；
    */
    public function actionAddflyer()
    {
     
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
    public function actionDeletedflyer() //需要传teamid
    {
      
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
      if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'user/flyerinfo');
      }
      $model = array();    
      $model['id'] = Yii::$app->request->post("id"); //只要给了flyerid，任何都可以看这个飞手的信息
      $status_msg = 'failed'; 
      if (empty($model['id']) ) {
         $data = array('status' => 1000,'status_msg'=> $status_msg,'message'=>'参数不合法');
         return $data;
      }
      $actionName = 'actionFlyerinfo';
      $userData = $this->getUserInfo($actionName);
      $data = array('data' => array() );
      $list = array();     
      if ($userData && $userData['status'] == '0' && $userData['status_msg'] == 'ok') {
        $redata = $model;         
        $where = array();
        $where['id'] = $model['id'];
        $where['deleted'] = '0';
        $teamInfo = Agroflyer::getAndEqualWhere($where); 
        if ($teamInfo) {        
          $list = array();
          $list['status'] = 0;
          $list['status_msg'] = 'ok';         
          $list['data'] = $teamInfo['0'];  
          return $list;         
        }
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

      if (isset($order) && ($order == 'work_area')) {
          $order = 'new_work_area';
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

      //$meta_key = Yii::$app->request->cookies['_meta_key']->value;
      $paramsString = '';
      if(isset($start_date) && isset($end_date)) {
          $paramsString .= 'start_end'.$start_date.$end_date;
      }
      if(isset($flyer_name)) {
          $paramsString .= 'flyer_name'.$flyer_name;
      }
      if(isset($hardware_name)) {
          $paramsString .= 'hardware_name'.$hardware_name;
      }
      if(isset($team_name)) {
          $paramsString .= 'team_name'.$team_name;
      }
      if(isset($location)){
          $paramsString .= 'location'.$location;
      }
      if(isset($order)) {
          $paramsString .= 'order'.$order;
      }
      if(isset($selectAll)) {
          $paramsString .= 'selectAll'.$selectAll;
      }
      //$paramsString = $paramsString.$page.$size.$updown.$selectAll;
      //$returnKey = __CLASS__.__FUNCTION__."_flight_".md5($paramsString.$meta_key);
      //$returnData = Yii::$app->cache->get($returnKey);
      //if ( $returnData ) {
      //    return $returnData;
      //}
      $model = array();         
      $data = array('data' => array() );      
                        
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
      $model['order'] =  $order;
      $model['updown'] =  $updown;
      $model['deleted'] = '0';        
      $subQuery = Agroflight::getFlightSubQuery($model);
      $countSum = Agroflight::getWhereFlightCount($model, $subQuery); 
      if (empty($countSum) || $countSum['0']['flight_count'] == 0) {                       
          return array('status'=>0, 'count'=>0, 'status_msg'=>'ok', 'data'=>array()); 
      }
      if($selectAll) { //如果全选，返回所有选中的id
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
                $ret[] = $_ti;
            } else {
                $ret[] = $_ti;
            }
        }

      $list = array();
      $list['status'] = 0;
      $list['count'] = $countSum['0']['flight_count'];           
      $list['status_msg'] = 'ok';                
      $list['data'] = $ret;
      //if (isset($returnKey) && $returnKey  ) {
      //    Yii::$app->cache->set($returnKey, $list, 180);
      //}
      return $list;  
    }

    /*
    *  查看飞行记录总面积，总时长，总次数  /apiuser/flightcount 
    *  @parameter string start_date 开始日期 20160901
    *  @parameter string end_date 结束日期 20160902
    *  @parameter string flyer_name 飞手名称
    *  @parameter string hardware_name 飞行器名称
    *  @parameter string team_name 团队名称
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
  
      $model = array();         
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

      $meta_key = Yii::$app->request->cookies['_meta_key']->value;
      $paramsString = '';
      if(isset($start_date) && isset($end_date)) {
          $paramsString .= 'start_end'.$start_date.$end_date;
      }
      if(isset($flyer_name)) {
          $paramsString .= 'flyer_name'.$flyer_name;
      }
      if(isset($hardware_name)) {
          $paramsString .= 'hardware_name'.$hardware_name;
      }
      if(isset($team_name)) {
          $paramsString .= 'team_name'.$team_name;
      }
      if(isset($location)) {
          $paramsString .= 'location'.$location;
      }
      $returnKey = __CLASS__.__FUNCTION__."_flightCount_".md5($paramsString.$meta_key);   
      //$returnData = Yii::$app->cache->get($returnKey);
      //if ( $returnData ) {
      //    return $returnData;
      //}

      $data = array('data' => array() );
      $list = array();             
      $where = $newItems = $newFlyer = array();
      $where['uid'] = $userData['items']['0']['account_info']['user_id'];
      $where['deleted'] = '0';             
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
      $model['deleted'] = '0';
      $subQuery = Agroflight::getFlightSubQuery($model);
      $countSum = Agroflight::getWhereFlightCount($model, $subQuery, 'count(id) as flight_count,sum(new_work_area) as sum_area,sum(work_time) as sum_time');
      if (empty($countSum) || $countSum['0']['flight_count'] == 0) {
        return array('status'=>0, 'count'=>0, 'status_msg'=>'ok', 'sum_area'=>0, 'sum_time'=>0);
      }           
      $list = array();
      $list['status'] = 0;
      $list['count'] = $countSum['0']['flight_count'];
      $list['sum_area'] = $countSum['0']['sum_area']; 
      $list['sum_time'] = $countSum['0']['sum_time']; 
      $list['status_msg'] = 'ok';             
      return $list;

      /*
        $subQuery = Agroflight::getFlightSubQuery($model);
        $field = 'agro_flight.*';
        $countSum = Agroflight::getActiveWhere($model, $subQuery, $field);
        $data = [
            'count' => 0,
            'sum_area' => 0,
            'sum_time' => 0
        ];
        for ($i = 0; $i < count($countSum); $i++) {
            $data['count']++;
            if ($countSum[$i]['fixed_tag'] == '1') {
                //$flightData[$i]['work_are'] = UserAvatar::getUserAvatar($flightData[$i]['new_work_area']);
                $data['sum_area'] += $countSum[$i]['new_work_area'];
                $data['sum_time'] += $countSum[$i]['work_time'];
            } else {
                $data['sum_area'] += $countSum[$i]['work_area'];
                $data['sum_time'] += $countSum[$i]['work_time'];
            }
        };

        //if (empty($countSum) || $countSum['0']['flight_count'] == 0) {
        //if (isset($returnKey) && $returnKey ) {
        //    Yii::$app->cache->set($returnKey, $list, 180);
        //}
        //return array('status'=>0, 'count'=>0, 'status_msg'=>'ok', 'sum_area'=>0, 'sum_time'=>0);
        //}
        $list = array();
        $list['status'] = 0;
        $list['count'] = $data['count'];
        $list['sum_area'] = $data['sum_area'];
        $list['sum_time'] = $data['sum_time'];
        $list['status_msg'] = 'ok';
        //if (isset($returnKey) && $returnKey ) {
        //    Yii::$app->cache->set($returnKey, $list, 180);
        //}
        return $list;
      */
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
      $data = array('data' => array() );
      $list = array();     
      if ($userData && $userData['status'] == '0' && $userData['status_msg'] == 'ok') { 
        $where = $newItems = $newFlyer = array();
        if (isset($userData['items']['0']['account_info'])) {     
          $fields = "*";
          $flightData = Iuavflightdata::getAndEqualWhere($model,0,-1,'id',-1);
          $list = array();
          $list['status'] = 0;  
          $list['status_msg'] = 'ok';                
          $list['data'] = $flightData;  
          if (isset($returnKey) && $returnKey) {
            Yii::$app->cache->set($returnKey, $list, 120);  
          }        
          return $list;           
        }
        $data = array('status' => 1007, 'status_msg'=> $status_msg,'message'=>'权限不足');
        return $data;
      }
      return $userData;
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
          if(is_array($edge_point)){
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
          $taskInfo[$k]['edge_point'] = $edge_result;

          $way_point = $this->stringTo2Array($v['way_point']);
          $way_result = array();
          if(is_array($way_point)){
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
          $taskInfo[$k]['way_point'] = $way_result;

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

    protected function stringToArray($s)
    {
        if($s == null)
          return null;
        $a=array();
        $str = ltrim($s, "L");
        //echo $str;
        $a = explode("#", $str);
        $result = array();
        foreach($a as $k => $v)
        {
          //echo $v;echo "\n";
          if($v == null)
          {
              continue;
          }
          $data = explode(",", $v);
          $result[$k][] = floatval($data[0]);
          $result[$k][] = floatval($data[1]);
          
        }
        return $result;
    }
    protected function stringTo2Array($s)
    {
        if($s == null)
          return null;
        $a = array();
        $str = ltrim($s, "LL");
        $a = explode("##", $str);
        $result = array();
        foreach($a as $k => $v)
        {
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
      if (extension_loaded ('newrelic')) {
             newrelic_name_transaction ( 'user/taskinfo');
      }
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
               $this->downTask($taskid,$returnData['data']);
            }
            return $returnData;
        } 
      }

      $model = array();      
      $actionName = 'actionTaskinfo';
      $userData = $this->getUserInfo($actionName);
      $data = array('data' => array() );
      $list = array();     
      if ($userData && $userData['status'] == '0' && $userData['status_msg'] == 'ok') {
        $where = $newItems = $newFlyer = array();
        if (isset($userData['items']['0']['account_info'])) {               
          $model['id'] =  $taskid;          
          $model['deleted'] = '0';           
          $taskInfo = Agrotask::getAndEqualWhere($model,0,1); 
          $list = array();
          $list['status'] = 0;  
          $list['status_msg'] = 'ok';  
          //如果act为down时，则不用转换
          if($taskInfo && $act != 'down')
          {
              $taskInfo[0]['key_point'] = $this->stringToArray($taskInfo[0]['key_point']); 

              //$taskInfo[0]['edge_point'] = $this->stringTo2Array($taskInfo[0]['edge_point']);
              $edge_point = $this->stringTo2Array($taskInfo[0]['edge_point']); 
              $edge_result = array();
              if(is_array($edge_point)) {
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
              $taskInfo[0]['edge_point'] = $edge_result;  

              //$taskInfo[0]['way_point'] = $this->stringTo2Array($taskInfo[0]['way_point']);
              $way_point = $this->stringTo2Array($taskInfo[0]['way_point']);
              $way_result = array();
              if(is_array($way_point)) {
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
              $taskInfo[0]['way_point'] = $way_result;  

              $taskInfo[0]['plan_edge_poit'] = $this->stringToArray($taskInfo[0]['plan_edge_poit']);
              //var_dump($taskInfo[0]['key_point']); echo 111;die; 
              $taskInfo[0]['obstacle_point'] = $this->stringTo2Array($taskInfo[0]['obstacle_point']); 
          }
        
          $list['data'] = $taskInfo;  
          if (isset($returnKey) && $returnKey ) {
            Yii::$app->cache->set($returnKey, $list, 120);  
          }
          if ($act == 'down' && $list['status'] == '0' && $list['status_msg'] == 'ok') {
               $this->downTask($taskid,$list['data']);
          }          
          return $list;                  
        }
        $data = array('status' => 1007, 'status_msg'=> $status_msg,'message'=>'权限不足');
        return $data;
      }
      return $userData;
    }

     //下载文件
    protected function downTask($taskid,$result) {    
      if (empty($result)) {
         return false;
      }
      $filename = $taskid.'.txt'; 
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
        $total_price = $this->getPostValue("total_price"); //$total_price = 23893;
        $status_msg = 'failed'; 
        if (empty($arr_id)) {
           $data = array('status' => 1000,'status_msg'=> $status_msg,'message'=>'参数不合法');
           return $data;
        }    
        $actionName = 'actionTaskinfo';
        $userData = $this->getUserInfo($actionName);

        if ($userData && $userData['status'] == '0' && $userData['status_msg'] == 'ok') 
        {
            $where = $newItems = $newFlyer = array();
            if (isset($userData['items']['0']['account_info'])) 
            {

            }

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
            $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('D3','飞行器名字');
            $objectPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
            $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('E3','作业面积');
            $objectPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
            $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('F3','飞行时长');
            $objectPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
            $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('G3','作业对象');
            $objectPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
            $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('H3','喷幅');
            $objectPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
            $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('I3','飞手');
            $objectPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
            $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('J3','队伍');
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
            if (isset($model['upper_uid']) && $model['upper_uid'] ) { 
                $model['deleted'] = 0;
                $result = Agroflight::getRecordByID($model, $arr_id);//一次性把飞行记录查出来  但传进来的是id数组啊，怎么一次性查完呢？in
            }
            
            if($result && is_array($result))
            {
                $n = 0;
                foreach (array_reverse($result) as $key => $value) 
                {
                  $where['uid'] = $value['uid'];
                  $where['product_sn'] = $value['product_sn'];
                  $where['deleted'] = 0;
                  $productname = Agroactiveinfo::getNameByID($where);
                  $where['teamid'] = $value['team_id'];
                  $where['bossid'] = $model['upper_uid'];
                  $teamname = Agroteam::getNameByID($where);
                  $where['flyerid'] = $value['uid'];
                  $flyername = Agroflyer::getNameByID($where);

                  $objectPHPExcel->getActiveSheet()->setCellValue('B'.($n+4), substr($value['create_date'], 0, 4)."年".substr($value['create_date'], 4, 2)."月".substr($value['create_date'], 6, 2)."日".",".$value['start_end']);//起落时间
                  $objectPHPExcel->getActiveSheet()->setCellValue('C'.($n+4), $value['location']);//作业地点
                  $objectPHPExcel->getActiveSheet()->setCellValue('D'.($n+4), $productname);//飞行器名字
                  $objectPHPExcel->getActiveSheet()->setCellValue('E'.($n+4), round($value['work_area'] * 0.01, 2)."亩");//作业面积
                  $min = floor($value['work_time']/1000/60);
                  $sec = floor($value['work_time']/ 1000 % 60);
                  $result = '';
                  if($min > 0) {
                      $result = $min.'分'.$sec.'秒';
                  } else {
                      $result = $sec.'秒';
                  }
                  $objectPHPExcel->getActiveSheet()->setCellValue('F'.($n+4), $result);//作业时间
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
            
            $data['status'] = 0;
            $data['status_msg'] = 'ok';
            return $data;
        } 
        else
        {
            return $userData;
        }      
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
        if ($userData && $userData['status'] == '0' && $userData['status_msg'] == 'ok') {
            if (isset($userData['items']['0']['account_info'])) {
                $count = Agrorecord::getWhereRecordsCount($model); 
                $fields = 'operator,type,content,detail,created_at';
                $recordData = Agrorecord::getAndWhere($model, $fields, $start, $size); 
                $data = array('status' => 0,'status_msg'=> 'ok','data'=>$recordData, 'count' => $count['records_count']);
                if (isset($returnKey) && $returnKey ) {
                  Yii::$app->cache->set($returnKey, $data, 5);  
                }
                return $data; 
            }
        }
        $data = array('status' => 1001,'status_msg'=> $status_msg,'message'=>'登录已经过期,请重新登录');
        return $data; 
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

    public function actionListmyteam() {
        // dummy function for fixing newrelic error report
        return array('status' => 0, 'status_msg' => 'ok');
    }
}
