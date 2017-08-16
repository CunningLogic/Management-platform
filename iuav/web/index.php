<?php

// comment out the following two lines when deployed to production
ini_set('date.timezone','Asia/Shanghai');

require(__DIR__ . '/../config/.config.php');

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/../vendor/phpoffice/phpexcel/Classes/PHPExcel.php');

$config = require(__DIR__ . '/../config/web.php');

(new yii\web\Application($config))->run();
