<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use yii\console\Controller;
use yii;
use app\models\Agroactiveinfo;
/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class MonthlyController extends Controller
{
    public function actionIndex($n) { //每个月执行一次,分配n条短信
        $getStr = json_encode($n);
        $this->add_log($getStr, 'commands_monthly');//写入日志

        $where['deleted'] = '0';
        $where['is_active'] = '1';
        $activeInfo = Agroactiveinfo::findAll($where); 
        foreach ($activeInfo as $key => $value) { 
            $value->msg_sum = $n; 
            $value->save();
        }
    }
     // 写入文件
    protected function add_log($msg, $type = 'commands_monthly') {
        $ip = '';
        $logfile = __DIR__.'/../runtime/logs/'.date('Ymd').'-'.$type.'.log';
        if ( ! file_exists($logfile))
        {
            file_put_contents($logfile, '');
        }
        $SERVER_ADDR = '';
        file_put_contents($logfile, date('Y/m/d H:i:s').":  $msg 条短信 >>> $ip - $type  >> SERVER_ADDR=$SERVER_ADDR \r\n", FILE_APPEND);
    }
}