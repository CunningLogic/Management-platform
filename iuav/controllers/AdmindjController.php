<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

use app\models\Area;
use app\models\DraftArea;
use app\models\ReleaseArea;
use GeoIp2\Database\Reader;

class AdmindjController extends Controller
{
    // public $enableCsrfValidation = true;
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    //'logout' => ['post'],
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

    public function actionIndex()
    {
        $ip = "62.5.210.232";
        $reader = new Reader(__DIR__.'/../config/GeoIP2-Country.mmdb');                  
        $record = $reader->country($ip);
        if ($record ==false) {
             //use MaxMind\Db\Reader 修改文件，不会报错导致程序无法进行
                       // $this->add_log($get_str."ipnotindatabase=The address $ip is not in the database.", 'friday_product');
                       $country = 'US';
                   }else{
                       var_dump($record->country);
                       $country = $record->country->isoCode;
                   }                 
        echo $country;exit;
        echo "dji";exit;
        return $this->render('index');
    }

    public function actionLogin()
    {         
        if (!\Yii::$app->user->isGuest) {
            return $this->redirect('/admindj/');

        }
        $model = new LoginForm();       
        $LoginFormList = Yii::$app->request->post("LoginForm");
        //var_dump($LoginFormList);exit;
        $UserIP =  Yii::$app->request->getUserIP();

        if (strlen($LoginFormList['username']) > 30 )  {
           echo "Incorrect username strlen is  ".strlen($LoginFormList['username']);
           exit;
        }
        if (empty($LoginFormList['username']) || empty($LoginFormList['password']) ) {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
        if ($UserIP) {
            $luckkeyIP = 'uSiteController_actionLogin'.md5($UserIP);
            $luckdataIP = Yii::$app->cache->get($luckkeyIP);
            if ( $luckdataIP > 10 ) {
               echo "Incorrect username or password. ".$luckdataIP."times";
               exit;
            }
        }
        $luckkey = 'uSiteController_actionLogin'.md5($LoginFormList['username']);
        $luckdata = Yii::$app->cache->get($luckkey);
        if ( $luckdata > 5 ) {
           echo "Incorrect username or password. ".$luckdata."times";
           exit;
        }       
        $luckkeypassword = 'uSiteController_actionLogin_password'.md5($LoginFormList['password']);
        $luckdatapassword = Yii::$app->cache->get($luckkeypassword);
        if ( $luckdatapassword > 5 ) {
           echo "Incorrect username or password. ".$luckdatapassword."times";
           exit;
        }  
       
        if ($model->load(Yii::$app->request->post()) && $model->login()) {            
            return $this->redirect('/admindj/');
        } else {               
            Yii::$app->cache->set($luckkey, $luckdata+1, 3600);
            Yii::$app->cache->set($luckkeypassword, $luckdatapassword+1, 3600);
            if ($luckkeyIP) {
                Yii::$app->cache->set($luckkeyIP, $luckdataIP+1, 600);
            }
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        } else {
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }

    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionHelp()
    {
       
         $request = Yii::$app->getRequest();
        //var_dump($request,$request->getCsrfToken(),$request->csrfParam);
         //$toke = $request->getCsrfToken();
        return $this->renderPartial('help.tpl', ['username' => 'Alex','csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);
    }

}
