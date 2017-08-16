<?php

namespace app\controllers;
use app\models\Iuavflightdata;
use app\models\IuavFlightDataSearch;
use yii\data\ActiveDataProvider;//
use Yii;
use yii\data\Pagination;
use app\components\DjiUser;
class PlaneController extends \yii\web\Controller
{
    public function actionInfo($session_num, $product_sn)
    {
        if ($this->isLogin('actionIndex'))
        {
            $query = IuavFlightData::find()->where(['session_num' => $session_num, 'product_sn'=>$product_sn]);
            $dataProvider = new ActiveDataProvider([
                'query' => $query,

                'pagination' => [
                    'pageSize' => 15,
                ],
                'sort' => [
                    'defaultOrder' => [
                    'timestamp' => SORT_DESC,            
                    ]
                ],
            ]); 
            return $this->render('info', array('dataProvider' => $dataProvider, 'session_num'=>$session_num)); 
        }
    }
    
    public function actionStamp($product_sn)
    {
        if ($this->isLogin('actionIndex'))
        {
            $dataProvider = new ActiveDataProvider([
                'query' => IuavFlightData::find()->where('product_sn = :product_sn', array(":product_sn"=>$product_sn))->groupBy('session_num'),

                'pagination' => [
                    'pageSize' => 15,
                ],
                'sort' => [
                    'defaultOrder' => [
                    'session_num' => SORT_DESC,            
                    ]
                ],
            ]);
            return $this->render('stamp', array('dataProvider' => $dataProvider, 'product_sn'=>$product_sn)); 
        }

        //return $this->render('index');
    }
    protected function get_client_ip() {
        $ip = $_SERVER['REMOTE_ADDR'];
        return $ip;
    } 
    protected function getUserInfo($actionName,$lucked=1)
    {
        $status_msg = 'failed'; 
        $errorIpKey = __CLASS__.__FUNCTION__."_errorip_".md5($this->get_client_ip()); 
        $errorIpData = Yii::$app->cache->get($errorIpKey);        
        if ( $errorIpData  && $errorIpData > 30) {
           return array('status' => 1002,"status_msg" => "failed","message" => "系统错误!",);
        } 
        if (empty(Yii::$app->request->cookies['_meta_key']) )
        {
          $data = array('status' => 1001,'status_msg'=> $status_msg,'message'=>'登录已经过期,请重新登录');
          return $data;
        } 
        //var_dump(Yii::$app->request->cookies);
        $meta_key = Yii::$app->request->cookies['_meta_key']->value;
        if ($lucked == 1) 
        {
            $luckkey = $actionName.'_getUserInfo_'.md5($meta_key);
            $luckdata = Yii::$app->cache->get($luckkey);
            if ( $luckdata ) 
            {            
                $data = array('status' => 1004,'status_msg'=> $status_msg,'message'=>'1秒内重复提交,请稍后重试');
                return $data;
            }
            Yii::$app->cache->set($luckkey, 1, 1);
        }
       
        $djiUser = new DjiUser();     
        $userData = $djiUser->get_account_info_by_key($meta_key);
        //var_dump($userData);die;
        if ($userData && $userData['status'] == '0' && $userData['status_msg'] == 'ok') {
          return $userData;
        }else{
           Yii::$app->cache->set($errorIpKey, $errorIpData+1, 3600);
           return $userData;
        }
    }
    protected function getLogoutUrl($country)
    {
        $from = '>Uy^K)R8Rd$5!@6T^VQH}EVEo8ZD>b`1';
        $data = base64_encode('http://'.$_SERVER['HTTP_HOST'].'/user/login/');  
        //echo 'http://'.$_SERVER['HTTP_HOST'].'/user/login/';exit;
       // echo    $data;exit;
        $sign = md5($from . $data);

        $host = YII_DEBUG ? 'https://www.dbeta.me' : ($this->isMobile() ? 'https://m.dji.com' : 'https://www.dji.com');
        if (in_array($country,array('cn'))) {
            $url = $host . ($this->isMobile() ? '/cn/sessions/new' : '/cn/user/logout');
        }elseif (in_array($country,array('tw','mo','hk'))) {
            $url = $host . ($this->isMobile() ? '/cn/sessions/new' : '/zh-tw/user/logout');
        }else{
            $url = $host . ($this->isMobile() ? '/sessions/new' : '/user/logout');
        } 
        return $url . '?from=dji-sticker&data=' . $data . '&sign=' .$sign;
    }
    protected function isLogin($actionName)
    {
        $userData = $this->getUserInfo($actionName); 
        if ($userData && $userData['status'] == '0' && $userData['status_msg'] == 'ok' && $userData['items']['0']['account_info']['email'] == 'zhanbin.li@dji.com') 
        {
            return 1;
        }
        else if( $userData && $userData['status'] == '1004' && $userData['status_msg'] == 'failed')
        {
            echo "<script>alert('1秒内重复提交')</script>";
        }
        else
        { 
            $country = Yii::$app->request->get('country', 'cn');      
            $djiUser = new DjiUser();  
            $BackUrl = 'http://'.$_SERVER['HTTP_HOST'].'/user/logoutback/';        
            $url = $djiUser->getLogoutUrl($BackUrl,$djiUser->getLocale($country) );  
            Header("Location: $url "); 
            exit;
        }
        return 0;
    }
    public function actionIndex()
    {                                                     
        if ($this->isLogin('actionIndex')) 
        {
            return $this->render('index');
        }
        $url = Yii::$app->request->getReferrer(); //返回上一页的url地址
        header( "Location: $url " );
    }
    public function actionView()
    {
        //$model = new IuavFlightData();
        //$model = IuavFlightData::find()->where(['id'=>'1'])->one();
        //$model = IuavFlightData::find()->all();
        //$query = IuavFlightData::find();
        /*
        $connection = new \yii\db\Connection([
            'dsn' => 'mysql:host=localhost;dbname=yii2basic',
            'username' => 'root',
            'password' => '',
        ]);
        $connection->open();
        $command = $connection->createCommand('SELECT product_sn, count(timestamp) FROM iuav_flight_data');
        $query = $command->queryAll();
        var_dump($query);
        var_dump(IuavFlightData::find());die; */
        if ($this->isLogin('actionIndex'))
        {
            $dataProvider = new ActiveDataProvider([
                //'query' => IuavFlightData::find()->where('id = :id', array(":id"=>1)),
                'query' => IuavFlightData::find()->groupBy('product_sn'),

                'pagination' => [
                    'pageSize' => 15,
                ],
                'sort' => [
                    'defaultOrder' => [
                    'product_sn' => SORT_DESC,            
                    ]
                ],
            ]);
            //$searchModel = new IuavFlightDataSearch();
            //$dataProvider = $searchModel->search(Yii::$app->request->get());
            //$posts = $dataProvider->getModels();//返回一个Post实例的数组

            //var_dump($posts);die;
            return $this->render('view', array('dataProvider' => $dataProvider, )); 
        }
    }
    public function actionSearch()
    {
    	//$model = new IuavFlightDataSearch();
    	//echo '<pre>';
		//print_r($model->search(array('id'=>1)));
		//echo '</pre>';die;
    	//$model = IuavFlightData::find()->where(['id'=>'1'])->one();

        $model = new IuavFlightData();
        return $this->render('_search', array('model'=>$model));
    }

    public function actionUpdate($id)
    {
        //$model = new IuavFlightData();
        $model = IuavFlightData::find()->where(['id'=>$id])->one();
        return $this->render('view', array('model'=>$model));
    }
    public function actionDelete($id)
    {
        //$model = new IuavFlightData();
        $model = IuavFlightData::find()->where(['id'=>$id])->one();
        echo 'delete...';
        //return $this->render('view', array('model'=>$model));
    }
    public function actionResult()
    {
        //echo '<pre>';
        //print_r($_POST['IuavFlightData']['id']);
        //echo '</pre>';die;
        if(!isset($_POST)) {
            return;
        }
        $id = $_POST['IuavFlightData']['id'];
        //echo $id;
        $model = new IuavFlightData();
        $model = IuavFlightData::find()->where(['id'=>$id])->one();
        if($model == NULL)
        {
            echo 'not fond.';
            return;
        }
        return $this->render('view', array('model'=>$model));
    }

    public function actionCleandatabase() 
    {

        echo "<script> if(confirm( '确认是否清楚数据库？'))  location.href='./yesdeletedatabase';else location.href='./index'; </script>"; 
    }
    public function actionYesdeletedatabase() 
    {
        if ($this->isLogin('actionIndex'))
        {
            $count = IuavFlightData::deleteAll();
            echo '共删除'.$count.'条记录';
        }
    }
}
