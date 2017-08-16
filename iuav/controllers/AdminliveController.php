<?php

namespace app\controllers;

use app\components\DjiController;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;

use app\models\Area;
use app\models\DraftArea;
use app\models\OperationLog;
use app\models\Email;
use app\models\Ddslivevideo;
use app\models\Ddsliveroom;
use app\models\Apply;


class AdminliveController extends DjiController
{
	public $enableCsrfValidation = false;
		
    public function behaviors()
    {
        return [
            'access' => [
                'class'  => AccessControl::className(),
                'rules'  => [
                    [
						            'actions' => ['using','fznqmchhmhndsdewerezxgy'], 
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
						            'actions' => ['fznqmchhmhndsdewerezxgy','index','video','edit','del','view','submit','close','audit','destroy'], 
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
     $request = Yii::$app->getRequest();
     //var_dump($request,$request->getCsrfToken(),$request->csrfParam);
     //$toke = $request->getCsrfToken();
     return $this->renderPartial('index.tpl', ['videos' => json_encode($this->getVideoSources()),'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);
	   exit;

  }

  public function actionFznqmchhmhndsdewerezxgy()
  {
      $namekey = 'actionFznqmchhmhndsdewerezxgy';

      $prizedata = Yii::$app->cache->get($namekey);
      if (empty($prizedata)) {
         $where = array();
         $where['activity_id'] = array('2','3','4');                   
         $prizedata = apply::getAndEqualWhere($where,0,-1,'id',1,array("count(if(prize_id>1,true,null)) AS prizeSum" ,"count(if(prize_id<1,true,null)) AS notPrizeSum" ) ); 
     
      }
      echo "prizeSum:". $prizedata['0']['prizeSum']." <br /> notPrizeSum:". $prizedata['0']['notPrizeSum']."<br /> Sum:". ($prizedata['0']['prizeSum']+$prizedata['0']['notPrizeSum']);
      //var_dump($prizedata) ;   
      exit;

  }


  public function actionVideo()
  {
     $where = array();
     $where['activity_id'] = array('2','3','4');                   
     $prizedata = apply::getAndEqualWhere($where,0,-1,'id',1,array("count(if(prize_id>1,true,null)) AS prizeSum" ,"count(if(prize_id<1,true,null)) AS notPrizeSum" ) ); 
     echo "prizeSum:". $prizedata['0']['prizeSum']." <br /> notPrizeSum:". $prizedata['0']['notPrizeSum']."<br /> Sum:". ($prizedata['0']['prizeSum']+$prizedata['0']['notPrizeSum']);
     //var_dump($prizedata) ;   
     exit;
      $rooms = Ddsliveroom::getJoinAndEqualWhere([], 0, 20,'dds_live_room.id','asc');
	  //var_dump( $rooms);
	  $newRooms = array();
	  foreach ($rooms as $key => $value) {
          $tmp = array();
          $tmp['id'] = $value['room_id'];
          $tmp['mainShot'] = $value['room_id'] == '1' ? true : false;
          $tmp['title'] = $value['name'];
          $tmp['source'] = $value['id'];
          $tmp['closed'] = $value['disable'] == '1' ? true : false;
          $newRooms[] =  $tmp;
	  }

  	  //$videos = array();
	  $request = Yii::$app->getRequest();
	  return $this->renderPartial('video.tpl', ['rooms' => json_encode($newRooms),'videosUrl' => json_encode($this->getVideoSources()),'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);
  }

  // HttpMethod = POST
  public function actionSubmit()
  {
      $room_id = Yii::$app->request->post('id', '');
      $video_id = Yii::$app->request->post('video_id', '');

	  if (empty($video_id) ) {
          return $this->fail('video_id 不能为空');
	  }

      if (empty($room_id)) {
          $model = array();
          $model['video_id'] = $video_id + 0;
          $update = Ddsliveroom::add($model);

          if ($update > 0) {
              $room_id = $this->success($update);
          } else {
              return $this->fail('增加room出错', $update);
          }
      }

	  $idlist = explode(',', $room_id);
	  foreach ($idlist as $key => $value) {
          $model = array();
		  $model['id'] = $value + 0;
		  $model['video_id'] = $video_id + 0;
	      $update = Ddsliveroom::updateInfoVideo($model);
      }

      if ($update > 0) {
          return $this->success($update);
      } else {
          return $this->fail('切换出错', $update);
      }
  }
   public function actionClose()
  {
	  
	   $ids = Yii::$app->request->get('ids', '');
		 $disable = Yii::$app->request->get('disable', '');
		  if (empty($ids)) {
		 	   $ids = Yii::$app->request->post('ids', '');
		 	   $disable = Yii::$app->request->post('disable', '');
		 }

		 if (empty($ids)) {
		 	  echo "id is empty ";exit;
		 }		
		// $idlist = explode(',', $id);
		 foreach ($ids as $key => $value) {
		 	   $model = array();
		     $model['id'] = $value + 0;
		     $model['disable'] = $disable + 0;
	       $update = Ddsliveroom::updateInfoDisable($model);
		 }		
	   if ($update > 0) {
	   	    $result = array('status' => 200, 'id' =>  $update );
          die(json_encode($result));
	   }else{
	   	    $result = array('status' => 500, 'id' =>  $update );
          die(json_encode($result));
	   }   

  }


  public function actionEdit()
  {	 
     $id = Yii::$app->request->get('id', ''); 
		 $url = Yii::$app->request->get('url', '');
		 $low_url = Yii::$app->request->get('low_url', '');
		 $screenshot = Yii::$app->request->get('screenshot', '');
		 $type = Yii::$app->request->get('type', '0');
		 if (empty($id)) {
		 	    $id = Yii::$app->request->post('id', ''); 
		      $url = Yii::$app->request->post('url', '');
		      $low_url = Yii::$app->request->post('low_url', '');
		      $screenshot = Yii::$app->request->post('screenshot', '');
		      $type = Yii::$app->request->post('type', '');
		 }
		 if (empty($url) || empty($screenshot)) {
		 	  echo "url or screenshot is empty ";exit;
		 }
		 $model = array();
		 $model['id'] = $id + 0;
		 $model['url'] = $url;
		 $model['low_url'] = $low_url;
		 $model['screenshot'] = $screenshot;
		 $model['type'] = $type;
		 if (empty($model['id'])) {
		 	  $update = Ddslivevideo::add($model);
		 }else{
		 	  $update = Ddslivevideo::updateInfo($model);
		 }	   
	   if ($update > 0) {
	   	    $result = array('status' => 200, 'id' =>  $update );
          die(json_encode($result));
	   }else{
	   	    $result = array('status' => 500, 'id' =>  $update );
          die(json_encode($result));
	   }   

  }

  public function actionView()
  {
  	 $id = Yii::$app->request->get('id', ''); 
  	 $where = array();
		 $where['id'] = $id + 0;
  	 $videos = Ddslivevideo::getAndEqualWhere($where, 0, 20,'id','asc');
	   var_dump( $videos);	
     $request = Yii::$app->getRequest();
	   return $this->renderPartial('view.tpl', ['id' => $id,'videos' => $videos,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);
	   exit;

	   exit;  

  }

  private function getVideoSources()
  {
      $videos = Ddslivevideo::getAndEqualWhere([], 0, 20,'id','asc');
      $videosData = array();
      foreach ($videos  as $key => $value) {
          $tmp = array();
          $tmp['id'] = $value['id'];
          $tmp['url'] = $value['url'];
          $tmp['low_url'] = $value['low_url'];
          $tmp['screenshot'] = $value['screenshot'];
          $tmp['type'] = $value['type'];

          $videosData[] = $tmp;
      }

      return $videosData;
  }
}
