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

class SiteController extends Controller
{
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
        $list = array('captcha' => array('class' => 'yii\captcha\CaptchaAction', 'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,) );
        $list['error'] = array('class' => 'app\components\ErrorAction',);
        return  $list;
    }

    public function actionIndex()
    {
        //http://www.dji.com/cn/product/mg-1
        $url = "http://www.dji.com/cn/product/mg-1";
        header("Location:$url");
        exit();
        echo "dji";exit;
        return $this->render('index');
    }

    public function actionLogin()
    {
        return $this->redirect('/admin/login');
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
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
            return $this->redirect('/?r=draft');
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
}
