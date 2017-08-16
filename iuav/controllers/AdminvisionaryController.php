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

class AdminvisionaryController extends Controller
{
    public $enableCsrfValidation = false;
        
    public function behaviors()
    {
        return [
            'access' => [
                'class'  => AccessControl::className(),
                'rules'  => [
                    [
                        'actions' => ['upload','videoresult','videofinish'], 
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['index','video','edit','del','view','submit','close','image','destroy','upload','info','uploadimage','uploadinfo','uploadvideo','videoresult','videofinish','uploadinitvideo','imageindex','videoindex','infostatus','imagestatus'], 
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
    
    public function actionVideoresult()
    {
        $get_str = json_encode($_REQUEST);
       
        $get_str .= "SERVER=".json_encode($_SERVER);
        $this->add_log($get_str, 'Videoresult');
        $timestamp = Yii::$app->request->post('timestamp', ''); 
        $nonceStr = Yii::$app->request->post('nonceStr', ''); 
        $signature = Yii::$app->request->post('signature', ''); 
        $secret = 'RIubDHN8Ktt[hd8UusiLvG<IVtxZ?x1Zj]a?[@;G0AGCupTEA2==c56EJzPc8`RF';
        $nowsignature = sha1('nonceStr='.$nonceStr.'&secret='.$secret.'&timestamp='.$timestamp);
        if ($nowsignature != $signature ) {
             $this->add_log($get_str."status=400", 'Videoresult');
             $result = array('status' => 400,'msg' => 'signature is not right');
             die(json_encode($result));
        }  
        $model = array();
        $model['video_id'] = Yii::$app->request->post('video_id', '');
        $model['cover'] = Yii::$app->request->post('cover', '');
        $model['duration'] = Yii::$app->request->post('duration', '');
        $model['upload_token'] = Yii::$app->request->post('upload_token', '');  
        if ($model['upload_token']) {
            VisionariesImage::updateVideoInfo($model);
        }             
        $result = array('status' => 0,'msg' => 'OK');
        die(json_encode($result));
      
    }
    public function actionVideofinish()
    {
        $get_str = json_encode($_REQUEST);       
        $get_str .= "SERVER=".json_encode($_SERVER);
        $this->add_log($get_str, 'Videofinish');
        $timestamp = Yii::$app->request->post('timestamp', ''); 
        $nonceStr = Yii::$app->request->post('nonceStr', ''); 
        $signature = Yii::$app->request->post('signature', ''); 
        $secret = 'RIubDHN8Ktt[hd8UusiLvG<IVtxZ?x1Zj]a?[@;G0AGCupTEA2==c56EJzPc8`RF';
        $nowsignature = sha1('nonceStr='.$nonceStr.'&secret='.$secret.'&timestamp='.$timestamp);
        if ($nowsignature != $signature ) {
             $this->add_log($get_str."status=400", 'Videofinish');
             $result = array('status' => 400,'msg' => 'signature is not right');
             die(json_encode($result));
        }
        $model = array();
        $model['video_id'] = Yii::$app->request->post('video_id', '');
        $model['cover'] = Yii::$app->request->post('cover', '');
        $model['duration'] = Yii::$app->request->post('duration', '');       
        if ($model['video_id']) {
            VisionariesImage::updateVideoFinish($model);
        }                   
        $result = array('status' => 0,'msg' => 'OK');
        die(json_encode($result));
      
    }


    //图片上传
    public function actionUpload()
    {
        $get_str = json_encode($_REQUEST);
       
        $get_str .= "SERVER=".json_encode($_SERVER);
        $this->add_log($get_str, 'AdminvisionaryUpload');

        $model = array();
        $model['action'] = Yii::$app->request->post('action', '');
        $model['id'] = Yii::$app->request->post('id', '');
        if ($model['action'] == 'uploadinfo') {
            $model['name'] = Yii::$app->request->post('name', '');
            $model['photo'] = Yii::$app->request->post('zipurl', '');
            $model['elite'] = Yii::$app->request->post('elite', '');
            $model['blo'] = Yii::$app->request->post('blo', '');
            $model['dji_gear'] = Yii::$app->request->post('dji_gear', '');
            $model['quote'] = Yii::$app->request->post('quote', ''); 
            if ($model['id'] > 0 ) {
                if ( $model['photo']) {
                  VisionariesUser::updateInfo($model);
                }   
            }else{
                if ( $model['photo']) {
                  VisionariesUser::add($model);
                }   
            }
            
        }else{
            $model['visi_user_id'] = Yii::$app->request->post('visi_user_id', '');
            $model['type'] = 'picture';
            $model['video_id'] =0;
            $model['cover'] = $model['upload_token'] = '';
            $model['duration'] = 0;
            $model['zipurl'] = Yii::$app->request->post('zipurl', '');
            $model['title'] = Yii::$app->request->post('title', '');
            $model['location'] = Yii::$app->request->post('location', '');
            $model['dji_gear'] = Yii::$app->request->post('dji_gear', '');
            $model['exif_info'] = Yii::$app->request->post('exif_info', ''); 
            if ($model['id'] > 0 ) {
                if ( $model['zipurl']) {
                   VisionariesImage::updateInfo($model);
                 }                 
            }else{
               if ( $model['zipurl']) {
                 VisionariesImage::add($model);
               }   
            }

            
        }   
             
        $result = array('status' => 200);
        die(json_encode($result));
      
    } 

    public function actionVideo()
    {
        //http://dev.e.dbeta.me/adminvisionary/image/
       $request = Yii::$app->getRequest();
       //var_dump($request);
       $visi_user_id = Yii::$app->request->get('visi_user_id', '');
       $id =Yii::$app->request->get('id', '');
       $imageInfo = array();
       if ($id  > 0) {
            $imageInfo = VisionariesImage::getAndEqualWhere(array('id' => $id,'visi_user_id' => $visi_user_id ), 0, 1,'id','desc');
            $imageInfo = $imageInfo['0'];                   
       }
       $name = '';
       if ($visi_user_id > 0) {
            $userList = VisionariesUser::getAndEqualWhere(array('id' => $visi_user_id ), 0, 1,'id','desc');          
            $watermark =  "© ".$userList['0']['name']." | DJI VISIONARIES";
        }


       $date = date("Y-m-d H:i:s",time());
     //var_dump($request,$request->getCsrfToken(),$request->csrfParam);
     //$toke = $request->getCsrfToken();
       return $this->renderPartial('video.tpl', ['id'=>$id,'date'=>$date,'visi_user_id' => $visi_user_id,'watermark' =>$watermark ,'imageInfo' => $imageInfo,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);
       exit;
    }
    
    public function actionImage()
    {
        //http://dev.e.dbeta.me/adminvisionary/image/
       $request = Yii::$app->getRequest();
       //var_dump($request);
       $visi_user_id = Yii::$app->request->get('visi_user_id', '');
       $id =Yii::$app->request->get('id', '');
       $imageInfo = array();
       if ($id  > 0) {
            $imageInfo = VisionariesImage::getAndEqualWhere(array('id' => $id,'visi_user_id' => $visi_user_id ), 0, 1,'id','desc');
            $imageInfo = $imageInfo['0'];                   
       }
       $date = date("Y-m-d H:i:s",time());
     //var_dump($request,$request->getCsrfToken(),$request->csrfParam);
     //$toke = $request->getCsrfToken();
       return $this->renderPartial('image.tpl', ['id'=>$id,'date'=>$date,'visi_user_id' => $visi_user_id,'imageInfo' => $imageInfo, 'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);
       exit;
    }

    public function actionInfo()
    {
        //http://dev.e.dbeta.me/adminvisionary/info/
       $id =Yii::$app->request->get('id', '');
       $request = Yii::$app->getRequest();
       //var_dump($request);      
       $date = date("Y-m-d H:i:s",time());
       $userList = array();
       if ($id  > 0) {
            $userList = VisionariesUser::getAndEqualWhere(array('id' => $id), 0, 1,'id','desc');
            $userList = $userList['0'];
            
       }
     //var_dump($request,$request->getCsrfToken(),$request->csrfParam);
     //$toke = $request->getCsrfToken();
       return $this->renderPartial('info.tpl', ['date'=>$date,'id'=>$id,'userList'=>$userList,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);
       exit;
    }
    public function actionInfostatus()
    {
         //http://dev.e.dbeta.me/adminvisionary/infostatus/
       $model =array();
       $model['id'] =Yii::$app->request->post('id', '');
       $model['status'] =Yii::$app->request->post('status', '');
       $result = $status =0;
       if ($model['id']  > 0) {
            $result = VisionariesUser::changeStatus($model);
            if ($result > 0) {
                $status = 200;
            }
       }

       echo json_encode(array('status' => $status,'data' => $result));
       exit;
    
    }
    public function actionUploadinfo()
    {
        $imgdata = array();
        $imgdata['name'] =Yii::$app->request->post('name', '');
        $imgdata['elite'] =Yii::$app->request->post('elite', '');
        $imgdata['blo'] =Yii::$app->request->post('blo', '');
        $imgdata['dji_gear'] =Yii::$app->request->post('dji_gear', '');
        $imgdata['quote'] =Yii::$app->request->post('quote', ''); 
        $imgdata['id'] =Yii::$app->request->post('id', '');      
       
        $uptypes=array( 'image/jpg','image/jpeg','image/png','image/pjpeg','image/gif','image/bmp','image/x-png');  
        
        if (empty($_FILES) ||  !is_uploaded_file($_FILES["picture"]["tmp_name"]))  
        {  
            if ( $imgdata['id'] > 0) {
                $imgdata['photo'] ='';
                VisionariesUser::updateInfo($imgdata);
            }else{
               echo "111";exit;   
            }             
            // exit;  
        }else{
          if(!in_array($_FILES["picture"]["type"], $uptypes))        
          {  
               echo "文件类型不符!".$file["type"];  
               exit;  
          }  
          $imgdata['pictureType'] = $_FILES["picture"]["type"];
          $imgdata['picture'] = $_FILES["picture"]["tmp_name"];
          $imgdata['action'] = 'uploadinfo';
          $return = $this->uploadImageServer($imgdata);
        }
          
        $this->redirect('/adminvisionary/index/'); 
      
    }
    public function actionUploadimage()
    {
        $imgdata = array();
        $imgdata['visi_user_id'] = Yii::$app->request->post('visi_user_id', '');
        if ($imgdata['visi_user_id'] > 0) {
            $userList = VisionariesUser::getAndEqualWhere(array('id' => $imgdata['visi_user_id'] ), 0, 1,'id','desc');          
            $imgdata['watermark'] = "© ".$userList['0']['name']." | DJI VISIONARIES";
        }
        $picture = Yii::$app->request->post('picture', '');
        $imgdata['title'] = Yii::$app->request->post('title', '');
        $imgdata['location'] = Yii::$app->request->post('location', '');
        $imgdata['dji_gear']  = Yii::$app->request->post('dji_gear', '');
        $imgdata['exif_info']= Yii::$app->request->post('exif_info', '');
        $imgdata['id'] =Yii::$app->request->post('id', ''); 
        $uptypes=array( 'image/jpg','image/jpeg','image/png','image/pjpeg','image/gif','image/bmp','image/x-png');        
        if (empty($_FILES) || !is_uploaded_file($_FILES["picture"]["tmp_name"]))  
        {  
            if ( $imgdata['id'] > 0) {
                $imgdata['zipurl'] ='';                
                VisionariesImage::updateInfo($imgdata);
            }else{
               echo "picture is not find";exit;   
            }            
        }else{
            if(!in_array($_FILES["picture"]["type"], $uptypes))        
            {  
               echo "File type does not match";  
               exit;  
            }            
            $imgdata['pictureType'] = $_FILES["picture"]["type"];
            $imgdata['picture'] = $_FILES["picture"]["tmp_name"];          
            $return = $this->uploadImageServer($imgdata);  

        }
        
        //var_dump($return) ;exit;
        $this->redirect('/adminvisionary/imageindex/?id='.$imgdata['visi_user_id']); 
   
    }
    //初始化视频上传
    /*
    *
    * http://10.60.215.5:8081/adminvisionary/uploadinitvideo
    */
    public function actionUploadinitvideo()
    {
        $model = array();
        $model['visi_user_id'] =Yii::$app->request->post('visi_user_id', '');
        $model['title'] =Yii::$app->request->post('title', '');
        $model['location'] =Yii::$app->request->post('location', '');
        $model['dji_gear'] =Yii::$app->request->post('dji_gear', '');
        $model['exif_info'] =Yii::$app->request->post('exif_info', ''); 
        $model['id'] =Yii::$app->request->post('id', '');
        $model['upload'] =Yii::$app->request->post('upload', '');
        if (empty($model['upload']) && $model['id'] >0) {
            $update_result = VisionariesImage::updateInfo($model);
            $result = array('status' => 200);
            die(json_encode($result));
        }

        $imgdata = array();       
        $imgdata['email'] = 'visionary@dji.com';
        $imgdata['file_md5'] = '';
        $imgdata['file_size'] = 0;
        $imgdata['upload_type'] = 0;
        $imgdata['app_id'] = 'visionaries';
        $result = $this->uploadVideoServer($imgdata); 
        if ($result) {
             $json =  json_decode($result, true);
             if ($json['status'] == '0' && $model['visi_user_id'] ) {
                 $model['upload_token'] =  $json['upload_token'];
                 $model['zipurl'] = $model['cover'] = $model['video_id'] = '';
                 $model['duration'] = 0;
                 $model['type'] = 'video';  
                 if ($model['id'] >0) {
                     $update_result = VisionariesImage::updateVideoReset($model);
                 }else{
                    VisionariesImage::add($model);
                 }             
                 
             }

          }  
        die( $result);
    }


  public function actionIndex()
  {

     $username = Yii::$app->user->identity->username ;
     $page = Yii::$app->request->get('page', 1);
     $size = Yii::$app->request->get('size', 20);
     $start = ($page - 1) * $size;
     if ( $start < 0 ) $start = 0;      
     $userList = VisionariesUser::getAndEqualWhere(array('status' => array('published','draft') ), $start, $size,'id');
     $request = Yii::$app->getRequest();
     //var_dump($request,$request->getCsrfToken(),$request->csrfParam);
     //$toke = $request->getCsrfToken();
     return $this->renderPartial('index.tpl', ['username' => $username,'page' => $page,'userList' => $userList,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);
     exit;

  }
  public function actionImageindex()
  {
      
     $id =Yii::$app->request->get('id', ''); 
     $page = Yii::$app->request->get('page', 1);
     $size = Yii::$app->request->get('size', 20);
     $start = ($page - 1) * $size;
     if ( $start < 0 ) $start = 0;        
     $userList = $userInfo= array();
     if ($id > 0) {
          $where = array('visi_user_id' => $id,'type' => 'picture','status' => array('published','draft'));
          $userList = VisionariesImage::getAndEqualWhere($where, $start, $size,'id');
          $userInfo = VisionariesUser::getAndEqualWhere(array('id' => $id), 0, 1,'id','desc');
          $userInfo = $userInfo['0'];
     }     


     $request = Yii::$app->getRequest();
     //var_dump($request,$request->getCsrfToken(),$request->csrfParam);
     //$toke = $request->getCsrfToken();
     return $this->renderPartial('imageindex.tpl', ['userInfo' => $userInfo,'page' => $page,'id' => $id,'userList' => $userList,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);
     exit;

  }
  public function actionVideoindex()
  {

     $id =Yii::$app->request->get('id', ''); 
     $page = Yii::$app->request->get('page', 1);
     $size = Yii::$app->request->get('size', 20);
     $start = ($page - 1) * $size;
     if ( $start < 0 ) $start = 0;        
      $userList = $userInfo= array();
     if ($id > 0) {
          $where = array('visi_user_id' => $id,'type' => 'video','status' => array('published','draft'));
          $userList = VisionariesImage::getAndEqualWhere($where, $start, $size,'id');
          $userInfo = VisionariesUser::getAndEqualWhere(array('id' => $id), 0, 1,'id','desc');
          $userInfo = $userInfo['0'];
     }         
     $request = Yii::$app->getRequest();
     if (file_exists(__DIR__ . '/../config/.config.php')) {
         require(__DIR__ . '/../config/.config.php');
     }
     $videoUrl = isset($YII_GLOBAL['videoserver']['uploadUrl']) ? $YII_GLOBAL['videoserver']['uploadUrl'] : '';
    
     //var_dump($request,$request->getCsrfToken(),$request->csrfParam);
     //$toke = $request->getCsrfToken();
     return $this->renderPartial('videoindex.tpl', ['userInfo' => $userInfo,'videoUrl' => $videoUrl,'page' => $page,'id' => $id,'userList' => $userList,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);
     exit;

  }

  /*
  *  修改图片或者视频的状态 
  * id  例如 1,2
  * status 对应的状态 deleted;published;draft
  * 请求链接地址 /adminvisionary/imagestatus/ 
  * 请求方式 POST
  */
  public function actionImagestatus()
  {      
    //http://dev.e.dbeta.me/adminvisionary/imagestatus/
    $model =array();
    $model['id'] =Yii::$app->request->post('id', '');
    $model['status'] =Yii::$app->request->post('status', '');
    $result = $status =0;
    if ($model['id']  > 0) {
        $model['id'] = explode(',', $model['id']);
        $result = VisionariesImage::changeStatus($model);
        if ($result > 0) {
            $status = 200;
        }
    }

    echo json_encode(array('status' => $status,'data' => $result));
    exit;

  }
  
    protected function uploadImageServer($imgdata)
    {
        if (YII_ENV == 'dev') {
           // $url = "http://10.60.215.176:9090/imageserver/upload";
        }else{
           // $url = "http://127.0.0.1:9090/imageserver/upload";
        }
        if (file_exists(__DIR__ . '/../config/.config.php')) {
            require(__DIR__ . '/../config/.config.php');
        }
        $url = isset($YII_GLOBAL['imageserver']['uploadUrl']) ? $YII_GLOBAL['imageserver']['uploadUrl'] : '';
        $url .= "/imageserver/upload";        

        
        $cfile = curl_file_create($imgdata['picture'],$imgdata['pictureType'],'picture'); // try adding 
        // Create a CURLFile object / oop method 
        #$cfile = new CURLFile('resource/test.png','image/png','testpic'); // uncomment and use if the upper procedural method is not working.

        // Assign POST data
        $imgdata['picture'] = $cfile;

        $ch = curl_init();//初始化curl

        curl_setopt($ch,CURLOPT_URL,$url);//抓取指定网页

        //curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_HTTPHEADER,array('User-Agent: Opera/9.80 (Windows NT 6.2; Win64; x64) Presto/2.12.388 Version/12.15','Referer: http://someaddress.tld','Content-Type: multipart/form-data'));


        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上

        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式

        curl_setopt($ch, CURLOPT_POSTFIELDS, $imgdata);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // if any redirection after upload

        $data = curl_exec($ch);//运行curl

        curl_close($ch);

        return $data;


    }
    protected function uploadVideoServer($imgdata)
    {
        if (YII_ENV == 'dev') {
            //$url = "http://10.60.215.114:9000/video/uploadinit";
        }else{
            //$url = "https://vip.aasky.net/video/uploadinit";
        }
        if (file_exists(__DIR__ . '/../config/.config.php')) {
            require(__DIR__ . '/../config/.config.php');
        }
        $url = isset($YII_GLOBAL['videoserver']['uploadUrl']) ? $YII_GLOBAL['videoserver']['uploadUrl'] : '';
        $url .= "/video/uploadinit"; 
    
        
        //$cfile = curl_file_create($imgdata['picture'],$imgdata['pictureType'],'picture'); // try adding 
        // Create a CURLFile object / oop method 
        #$cfile = new CURLFile('resource/test.png','image/png','testpic'); // uncomment and use if the upper procedural method is not working.

        // Assign POST data
       // $imgdata['picture'] = $cfile;

        $ch = curl_init();//初始化curl

        curl_setopt($ch,CURLOPT_URL,$url);//抓取指定网页

        //curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_HTTPHEADER,array('User-Agent: Opera/9.80 (Windows NT 6.2; Win64; x64) Presto/2.12.388 Version/12.15','Referer: http://someaddress.tld','Content-Type: multipart/form-data'));


        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上

        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式

        curl_setopt($ch, CURLOPT_POSTFIELDS, $imgdata);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // if any redirection after upload

        $data = curl_exec($ch);//运行curl

        curl_close($ch);

        return $data;


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
