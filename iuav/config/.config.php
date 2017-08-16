<?php

$YII_GLOBAL = [];

//自定义sso用户组件配置
$YII_GLOBAL['djiuser']['returnUrl']  = 'http://dev.e.dbeta.me/';
$YII_GLOBAL['djiuser']['loginUrl']  = 'http://dev.e.dbeta.me/';
$YII_GLOBAL['djiuser']['ssoConfig']['apiDomain']  = 'api.dbeta.me';
$YII_GLOBAL['djiuser']['ssoConfig']['cookieDomain']  = '.dbeta.me';
$YII_GLOBAL['djiuser']['ssoConfig']['appid']  = 'dji-forum';
$YII_GLOBAL['djiuser']['ssoConfig']['appkey'] = '22bb5f91395bd3fabed233deae4aa33a';

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

// test env use

//数据库配置
/*$YII_GLOBAL['db']['dsn'] = 'mysql:host=rm-bp14ptw0f4nfx64xy.mysql.rds.aliyuncs.com;dbname=db_iuav';
$YII_GLOBAL['db']['username'] = 'db_iuav';
$YII_GLOBAL['db']['password'] = 'Ck7rJaA38YK8H';

//用户中心配置
$YII_GLOBAL['GWServer']['cookieDomain'] = '.aasky.net';
$YII_GLOBAL['GWServer']['GWAPIAPPID']  = 'dji_ag';
$YII_GLOBAL['GWServer']['GWAPIURL']  = 'http://gateway-stg-alihz.aasky.net:9000';  //测试环境的网关
$YII_GLOBAL['GWServer']['loginUrl'] = 'https://membercenter.dbeta.me/user/login.html';  //测试环境下的用户中心地址
$YII_GLOBAL['GWServer']['GWAPIFLYSAFEKEY'] = '31ee39e0-5641-4e15-9b11-fbbd24cbdfb2'; //测试环境下的key
$YII_GLOBAL['GWServer']['logoutUrl'] = 'https://membercenter.dbeta.me/user/logout.html'; //测试环境下的退出地址

$YII_GLOBAL['GWServer']['returnUrl']  = 'http://ag.aasky.net/';
$YII_GLOBAL['GWServer']['HMACKEY']  = 'xTgF747f9QDbymeayKiV';
$YII_GLOBAL['GWServer']['HMACKEYRETURN']  = 'm3RQr8KZ9acVorZPXV6F';
$YII_GLOBAL['GWServer']['AES128KEY']  = 'aCV33qkfzBZU4V7o';
$YII_GLOBAL['GWServer']['AES128KEYRETURN']  = 'c97pLAEQnepZC97P';

$YII_GLOBAL['GWServer']['goTimeLockUrl']  = 'http://127.0.0.1:7778/locktime';
$YII_GLOBAL['GWServer']['goAuthToken']  = 'test-uc6ghqqe5sQMVdL0tdoCMCCABC';
$YII_GLOBAL['GWServer']['goRealTimeLockUrl']  = 'http://127.0.0.1:7778/lock';*/

// pro env use


//数据库配置
/*$YII_GLOBAL['db']['dsn'] = 'mysql:host=rm-bp1gx742ui4a42lqw.mysql.rds.aliyuncs.com;dbname=db_dji';
$YII_GLOBAL['db']['username'] = 'root_dji';
$YII_GLOBAL['db']['password'] = '3WY40AqYUaHSe';

//用户中心配置
$YII_GLOBAL['GWServer']['cookieDomain'] = '.dji.com';
$YII_GLOBAL['GWServer']['GWAPIAPPID']  = 'dji_ag';
$YII_GLOBAL['GWServer']['GWAPIURL']  = 'http://gateway-alihzvpc.dji.com:9000';//正式环境的网关
$YII_GLOBAL['GWServer']['loginUrl'] = 'https://account.dji.com/user/login.html'; //正式环境下的用户中心地址
$YII_GLOBAL['GWServer']['GWAPIFLYSAFEKEY'] = '0cc16319-3593-44a9-a648-eb4e17ef601d';//正式环境的key
$YII_GLOBAL['GWServer']['logoutUrl'] = 'https://account.dji.com/user/logout.html'; //正式环境下的登出地址

$YII_GLOBAL['GWServer']['returnUrl']  = 'https://cn-ag.dji.com/';
$YII_GLOBAL['GWServer']['HMACKEY']  = 'xTgF747f9QDbymeayKiV';
$YII_GLOBAL['GWServer']['HMACKEYRETURN']  = 'm3RQr8KZ9acVorZPXV6F';
$YII_GLOBAL['GWServer']['AES128KEY']  = 'aCV33qkfzBZU4V7o';
$YII_GLOBAL['GWServer']['AES128KEYRETURN']  = 'c97pLAEQnepZC97P';

$YII_GLOBAL['GWServer']['goTimeLockUrl']  = 'http://127.0.0.1:7776/locktime';
$YII_GLOBAL['GWServer']['goRealTimeLockUrl']  = 'http://127.0.0.1:7776/lock';
$YII_GLOBAL['GWServer']['goAuthToken']  = 'dbeta_dUMMbuajxEAXcfEjQ46NqiUgQmQQQ';*/



// other env use

//数据库配置
$YII_GLOBAL['db']['dsn'] = 'mysql:host=seoul-iuav-prod.cxhbcyqxgmgz.ap-northeast-2.rds.amazonaws.com;dbname=iuav';
$YII_GLOBAL['db']['username'] = 'iuav';
$YII_GLOBAL['db']['password'] = 'Md8CJn2yrpKbWrUPBje7vddf';

//用户中心配置
$YII_GLOBAL['GWServer']['cookieDomain'] = '.dji.com';
$YII_GLOBAL['GWServer']['GWAPIAPPID']  = 'dji_ag';
$YII_GLOBAL['GWServer']['GWAPIURL']  = 'http://gateway-awsus.dji.com:9000';//正式环境的网关
$YII_GLOBAL['GWServer']['loginUrl'] = 'https://account.dji.com/user/login.html'; //正式环境下的用户中心地址
$YII_GLOBAL['GWServer']['GWAPIFLYSAFEKEY'] = '0cc16319-3593-44a9-a648-eb4e17ef601d';//正式环境的key
$YII_GLOBAL['GWServer']['logoutUrl'] = 'https://account.dji.com/user/logout.html'; //正式环境下的登出地址

$YII_GLOBAL['GWServer']['returnUrl']  = 'https://ag.dji.com/';
$YII_GLOBAL['GWServer']['HMACKEY']  = 'xTgF747f9QDbymeayKiV';
$YII_GLOBAL['GWServer']['HMACKEYRETURN']  = 'm3RQr8KZ9acVorZPXV6F';
$YII_GLOBAL['GWServer']['AES128KEY']  = 'aCV33qkfzBZU4V7o';
$YII_GLOBAL['GWServer']['AES128KEYRETURN']  = 'c97pLAEQnepZC97P';

$YII_GLOBAL['GWServer']['goTimeLockUrl']  = 'http://127.0.0.1:7776/locktime';
$YII_GLOBAL['GWServer']['goRealTimeLockUrl']  = 'http://127.0.0.1:7776/lock';
$YII_GLOBAL['GWServer']['goAuthToken']  = 'dbeta_dUMMbuajxEAXcfEjQ46NqiUgQmQQQ';

