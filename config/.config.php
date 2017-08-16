<?php

$YII_GLOBAL = [];

//自定义sso用户组件配置
$YII_GLOBAL['djiuser']['returnUrl']  = 'http://dev.e.dbeta.me/';
$YII_GLOBAL['djiuser']['loginUrl']  = 'http://dev.e.dbeta.me/';
$YII_GLOBAL['djiuser']['ssoConfig']['apiDomain']  = 'api.dbeta.me';
$YII_GLOBAL['djiuser']['ssoConfig']['cookieDomain']  = '.dbeta.me';
$YII_GLOBAL['djiuser']['ssoConfig']['appid']  = 'dji-forum';
$YII_GLOBAL['djiuser']['ssoConfig']['appkey'] = '22bb5f91395bd3fabed233deae4aa33a';

//本地数据库
//$YII_GLOBAL['db']['dsn'] = 'mysql:host=127.0.0.1;dbname=event';
//$YII_GLOBAL['db']['username'] = 'root';
//$YII_GLOBAL['db']['password'] = '';

/*//正式环境远程数据库配置
$YII_GLOBAL['db']['dsn'] = 'mysql:host=rm-bp1gx742ui4a42lqwo.mysql.rds.aliyuncs.com;dbname=db_dji';
$YII_GLOBAL['db']['username'] = 'root_dji';
$YII_GLOBAL['db']['password'] = '3WY40AqYUaHSe';*/

//测试环境远程数据库配置
$YII_GLOBAL['db']['dsn'] = 'mysql:host=rm-bp14ptw0f4nfx64xyo.mysql.rds.aliyuncs.com;dbname=db_iuav';
$YII_GLOBAL['db']['username'] = 'db_iuav';
$YII_GLOBAL['db']['password'] = 'Ck7rJaA38YK8H';

//缓存配置
$YII_GLOBAL['cache']['servers'][0]['host']  = '127.0.0.1';

//邮件设置
$YII_GLOBAL['mailer']['transport'] = [
    'class' => 'Swift_SmtpTransport',
    'host' => 'smtp.163.com',
    'username' => 'yourusername',
    'password' => 'yourpassword',
    'port' => '25',
    'encryption' => 'tls',
];
$YII_GLOBAL['mailer']['messageConfig']['from'] = ['admin@yourdomain.com' => 'administrator'];

/***
 * 管理邮箱，多个以英文分号隔开
 */
$YII_GLOBAL['adminEmail'] = 'admin@dji.com,admin2@dji.com';

//视频和图片上传
$YII_GLOBAL['videoserver']['uploadUrl']  = 'http://10.60.215.114:9000';
$YII_GLOBAL['imageserver']['uploadUrl'] = 'http://10.60.215.176:9090';


//用户中心配置
$YII_GLOBAL['GWServer']['GWAPIAPPID']  = 'dji_ag';  //访问网关的appid,测试环境和正式环境都是一样的


$YII_GLOBAL['GWServer']['returnUrl']  = 'http://ag.aasky.net/'; //测试环境域名
$YII_GLOBAL['GWServer']['cookieDomain'] = '.aasky.net';    //测试环境的域
$YII_GLOBAL['GWServer']['GWAPIURL']  = 'http://106.75.195.219:9000';  //测试环境的网关
$YII_GLOBAL['GWServer']['loginUrl'] = 'https://membercenter.dbeta.me/user/login.html';  //测试环境下的登录用户中心地址
$YII_GLOBAL['GWServer']['GWAPIFLYSAFEKEY'] = '31ee39e0-5641-4e15-9b11-fbbd24cbdfb2'; //测试环境下的key
$YII_GLOBAL['GWServer']['logoutUrl'] = 'https://membercenter.dbeta.me/user/logout.html'; //测试环境下的退出地址
$YII_GLOBAL['GWServer']['registerUrl'] = 'https://membercenter.dbeta.me/user/register.html'; //测试环境下的注册地址


/*$YII_GLOBAL['GWServer']['returnUrl']  = 'http://ag.dji.com/'; //正式环境域名
$YII_GLOBAL['GWServer']['cookieDomain'] = '.dji.com';    //正式环境的域
$YII_GLOBAL['GWServer']['GWAPIURL']  = 'http://gateway-alihzvpc.dji.com:9000';//正式环境的网关 
$YII_GLOBAL['GWServer']['loginUrl'] = 'https://account.dji.com/user/login.html'; //正式环境下的用户中心地址
$YII_GLOBAL['GWServer']['GWAPIFLYSAFEKEY'] = '0cc16319-3593-44a9-a648-eb4e17ef601d';//正式环境的key
$YII_GLOBAL['GWServer']['logoutUrl'] = 'https://account.dji.com/user/logout.html'; //正式环境下的登出地址*/



$YII_GLOBAL['GWServer']['HMACKEY']  = 'xTgF747f9QDbymeayKiV';
$YII_GLOBAL['GWServer']['HMACKEYRETURN']  = 'm3RQr8KZ9acVorZPXV6F';
$YII_GLOBAL['GWServer']['AES128KEY']  = 'aCV33qkfzBZU4V7o';
$YII_GLOBAL['GWServer']['AES128KEYRETURN']  = 'c97pLAEQnepZC97P'; 

$YII_GLOBAL['GWServer']['goSendmsgUrl']  = 'http://127.0.0.1:9090/sendmsg';
$YII_GLOBAL['GWServer']['goTimeLockUrl']  = 'http://127.0.0.1:7778/locktime';
$YII_GLOBAL['GWServer']['goRealTimeLockUrl']  = 'http://127.0.0.1:7778/lock';
$YII_GLOBAL['GWServer']['goAuthToken']  = '50ad92d516354154be023513fd39601f77847643';

$YII_GLOBAL['GWServer']['country'] = 'CN';


