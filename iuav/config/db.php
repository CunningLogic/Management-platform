<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => isset($YII_GLOBAL['db']['dsn']) ? $YII_GLOBAL['db']['dsn'] : 'mysql:host=localhost;dbname=event',
    'username' => isset($YII_GLOBAL['db']['username']) ? $YII_GLOBAL['db']['username'] : 'root',
    'password' => isset($YII_GLOBAL['db']['password']) ? $YII_GLOBAL['db']['password'] : '',
    'charset' => 'utf8',
];
