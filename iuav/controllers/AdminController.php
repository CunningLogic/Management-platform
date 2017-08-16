<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

use app\models\User;
use GeoIp2\Database\Reader;
use yii\captcha\Captcha;
use yii\captcha\CaptchaValidator;
use app\components\AdminUser;

class AdminController extends Controller
{
    // public $enableCsrfValidation = true;
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout','index','add'],
                'rules' => [
                    [
                        'actions' => ['logout','live','index','add'],
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
       
        require '../lib/PHPGangsta/GoogleAuthenticator.php';
        if (Yii::$app->user->identity->google_auth) {
          $ga_secret = Yii::$app->user->identity->google_auth;
        }else{
          if (getenv("GOOGLE_AUTHENTICATOR")) {
              $ga_secret = getenv("GOOGLE_AUTHENTICATOR").'TR';  
          }else{
              $ga_secret = 'ARLNCSFWKQLZMOTR';    
          }
        }
        $luckkeyid = 'iuav_AdminController_actionIndex' . md5($ga_secret);
        $luckdata = Yii::$app->cache->get($luckkeyid);
        if ($luckdata > 10) {
            return $this->redirect('/admin/login/');
        }
        

        $ga = new \PHPGangsta_GoogleAuthenticator();
        $ga_randomwordz = "JcMFYTt2rBDuqVshuDVvDQVUqocaQx3DemsUiH232" ;
        if (isset($_COOKIE['_iuavgaLoginAdmin']) && isset($_COOKIE['_iuavgaLoginAdminTime']) ) {
             $ga_time = $_COOKIE['_iuavgaLoginAdminTime'];
             $timeSlice = floor($ga_time / 30);
             $oneCode = $ga->getCode($ga_secret,$timeSlice);
             $oneCode_cookie = md5($ga_time.$oneCode.$ga_randomwordz);
           if ( $_COOKIE['_iuavgaLoginAdmin'] == hash_hmac("sha256", $oneCode_cookie, $ga_randomwordz) ) {

           }else{
              Yii::$app->cache->set($luckkeyid, $luckdata+1, 600);
              setcookie('_iuavgaLoginAdmin', 0,  time() - 3600);
              setcookie('_iuavgaLoginAdminTime', 0,  time() - 3600);
              echo "<p>Refresh the page and try again.</p>";
              exit;
           }  

         }else{
            if (isset($_POST['ga_activon_bbs']) && $_POST['ga_activon_bbs'] == "opt") {
                    $pass_oneCode = $_POST['gapass'];
                    $checkResult = $ga->verifyCode($ga_secret, $pass_oneCode, 120);    // 2 = 2*30sec clock tolerance
                    if ($checkResult) {
                        $adminUser = new AdminUser();
                        $adminUser->login(Yii::$app->user->identity->id,Yii::$app->user->identity->role_id);  
                        $ga_time = time();
                        $oneCode_cookie = md5($ga_time.$pass_oneCode.$ga_randomwordz);
                        setcookie('_iuavgaLoginAdmin', hash_hmac("sha256", $oneCode_cookie, $ga_randomwordz) ,  time()+3600); //如果要用这个加密登录页面，最好把cookie有效时间改为60*3或*2，上面的keyRegeneration也要相应改为180或120，动态密码长度最好也改成8位以上。不然你来不及输入登录信息。
                        setcookie('_iuavgaLoginAdminTime', $ga_time ,  time()+3600);
                        //header("Location: $_SERVER[PHP_SELF]");
                        return $this->redirect('/admin/');
                    } else {
                       Yii::$app->cache->set($luckkeyid, $luckdata+1, 600);
                       echo "<p>Sorry, you could not be logged in at this time. Refresh the page and try again.</p>";
                        echo "<br /><a href='/admin/'> Refresh the page and try again.</a>";
                       exit;
                    }
            }else{            
              $title = "身份验证";
              $request = Yii::$app->getRequest();   
              $csrftoken = $request->getCsrfToken();
              echo <<<EOT
             <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                    <html xmlns="http://www.w3.org/1999/xhtml">
                    <head>
                    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
                    <title>$title</title>
                
                    </head>
                    <body>
                <form method="post" autocomplete="off" name="login" id="loginform" action="/admin/">
                <input type="hidden" name="_csrf" value="$csrftoken">
                <input type="hidden" name="ga_activon_bbs" value="opt">
                <p class="logintitle">ga pass : <input name="gapass" tabindex="1" type="password" class="txt" autocomplete="off" /> </p>
                <p class="loginnofloat"><input name="submit" value="submit"  tabindex="3" type="submit" class="btn" /></p>
                </form>
                </body></html>
EOT;
             exit;
            }

         }

       $adminUser = new AdminUser();
       $rolePurvieData = $adminUser->getRolePurvie();
       if ($rolePurvieData && count($rolePurvieData) >= 1 ) {
         return $this->redirect($rolePurvieData['0']['redirect_url']);
       }
       return $this->redirect('/adminuser/listagentpending/');         
    }

    public function actionLive()
    {
        exit;
        //Captcha::run();

        echo Captcha::widget(['name' => 'captcha',]);
        $userInputCaptcha = 'yitamut';
        //$tmpCaptcha = new CaptchaValidator();
        //echo $tmpCaptcha->validate($userInputCaptcha);
        //echo "--------";
            exit;
        //Yii::$app->user->logout();
        return $this->redirect('/adminlive/');
    }

    public function actionLogin()
    {  
        $ip = $_SERVER['REMOTE_ADDR'];
        $allow_ip = array('153.209.207.94','218.17.158.154','218.17.157.16','116.66.221.253','127.0.0.1','218.17.157.76','10.60.215.5','10.81.1.236','10.81.5.223','10.60.215.103','10.81.13.152','202.134.92.122');
        if (!in_array($ip,$allow_ip)) {
            header("location:/");
            exit;
        }
        //var_dump($_POST);exit;
        //var_dump(Yii::$app->user->isGuest);exit;       
        if (!\Yii::$app->user->isGuest) {
           //return $this->redirect('/admin/');

        }       
        Yii::$app->user->logout();
       // Yii::$app->user->logout();
        //var_dump($this->cssFiles);
        if (Yii::$app->user->identity) {
             //var_dump(Yii::$app->user->identity->username);
        }       
        //exit;
        $model = new LoginForm();       
        $LoginFormList = Yii::$app->request->post("LoginForm");
        $captcha = Yii::$app->request->post("captcha");
        //var_dump($LoginFormList);exit;
        $UserIP =  Yii::$app->request->getUserIP();

        if (strlen($LoginFormList['username']) > 30 )  {
           echo "Incorrect username strlen is  ".strlen($LoginFormList['username']);
           exit;
        }
        $request = Yii::$app->getRequest();   
        $error = $codeerror = '';     

        if (empty($LoginFormList['username']) || empty($LoginFormList['password']) ) {
           $CaptchaHtml =  Captcha::widget(['name' => 'captcha',]);
           return $this->render('login.tpl', ['CaptchaHtml' => $CaptchaHtml,'codeerror' =>$codeerror,'title' => 'Alex','error' => $error,'csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);           
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
           echo "Incorrect username or password. ".$luckdata."times!!!";
           exit;
        }       
        $luckkeypassword = 'uSiteController_actionLogin_password'.md5($LoginFormList['password']);
        $luckdatapassword = Yii::$app->cache->get($luckkeypassword);
        if ( $luckdatapassword > 5 ) {
           echo "Incorrect username or password. ".$luckdatapassword."times";
           exit;
        }  

        $tmpCaptcha = new CaptchaValidator();
       
       
        if ( $tmpCaptcha->validate($captcha) == '1' && $model->load(Yii::$app->request->post()) && $model->login()) { 
                   
            return $this->redirect('/admin/');
        } else { 

            if ($tmpCaptcha->validate($captcha) == '1') {
                $errors = $model->getErrors();
                if ($errors && isset($errors['password'])) {
                         $error = $errors['password']['0'];
                }          
                
             }else{
                $codeerror = "验证码不对";

             }
             Yii::$app->cache->set($luckkey, $luckdata+1, 3600);
             Yii::$app->cache->set($luckkeypassword, $luckdatapassword+1, 3600);
             if ($luckkeyIP) {
                    Yii::$app->cache->set($luckkeyIP, $luckdataIP+1, 600);
             }  

            
            
            $CaptchaHtml =  Captcha::widget(['name' => 'captcha',]);
            return $this->render('login.tpl', ['CaptchaHtml' => $CaptchaHtml,'codeerror' =>$codeerror,'error' => $error,'title' => 'Alex','csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);           
        }
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();
        $adminUser = new AdminUser();
        $adminUser->logout();
        setcookie('_iuavgaLoginAdmin', 0,  time() - 3600);
        setcookie('_iuavgaLoginAdminTime', 0,  time() - 3600);

        return $this->redirect('/admin/login');
    }

    public function actionAdd()
    {
        $LoginFormList = Yii::$app->request->post("LoginForm");
       // $model = array();
        //User::add($model);
        return $this->redirect('/admin/login');
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
        //$password = '260265486daeb1609ef47403b1f3d785';
        //$hash = Yii::$app->getSecurity()->generatePasswordHash($password);
        //var_dump($hash);exit;    

         $request = Yii::$app->getRequest();
        //var_dump($request,$request->getCsrfToken(),$request->csrfParam);
         //$toke = $request->getCsrfToken();
        return $this->renderPartial('help.tpl', ['username' => 'Alex','csrf-param' => $request->csrfParam,'csrftoken' =>  $request->getCsrfToken() ]);
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
     // 写入文件
    protected function add_log($msg, $type = 'site_login')
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
    

}
