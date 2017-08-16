<?php

$YII_GLOBAL = [];

//自定义sso用户组件配置
$YII_GLOBAL['djiuser']['returnUrl']  = 'http://dev.e.dbeta.me/';
$YII_GLOBAL['djiuser']['loginUrl']  = 'http://dev.e.dbeta.me/';
$YII_GLOBAL['djiuser']['ssoConfig']['apiDomain']  = 'api.dbeta.me';
$YII_GLOBAL['djiuser']['ssoConfig']['cookieDomain']  = '.dbeta.me';
$YII_GLOBAL['djiuser']['ssoConfig']['appid']  = 'dji-forum';
$YII_GLOBAL['djiuser']['ssoConfig']['appkey'] = '';



//数据库配置
$YII_GLOBAL['db']['dsn'] = 'mysql:host=localhost;dbname=event';
$YII_GLOBAL['db']['username'] = 'root';
$YII_GLOBAL['db']['password'] = '';

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

//用户中心配置 测试环境
$YII_GLOBAL['GWServer']['GWAPIURL']  = '';
$YII_GLOBAL['GWServer']['GWAPIFLYSAFEKEY'] = '';
