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
class DailyController extends Controller
{
    public function actionIndex() //每天执行一次
    {
    	$where['deleted'] = '0';
    	$where['is_active'] = '1';
        $tmp['lock_begin'] = $tmp['lock_end'] = '0';
        $activeInfo = Agroactiveinfo::getAndEqualWhere($where, 0,0);
	    foreach ($activeInfo as $key => $value) { 
	    	if($value['lock_begin'] && $value['lock_begin'] && strlen($value['lock_begin']) == 10) {
	            $tmp['lock_begin'] = 1000 * mktime(0, 0, 0, substr($value['lock_begin'], 5,2), substr($value['lock_begin'], 8,2), substr($value['lock_begin'], 0,4));
	            $tmp['lock_end'] = 1000 * mktime(0, 0, 0, substr($value['lock_end'], 5,2), substr($value['lock_end'], 8,2), substr($value['lock_end'], 0,4));
	            if($tmp['lock_end'] > time() * 1000 && time() * 1000 > $tmp['lock_begin'] && $value['locked'] == 0) { //判断时间段锁定是否到期
	                $release = Agroactiveinfo::findOne(['id' => $value['id']]);
	                $release->locked = '1';
	                $release->save();
	            }
	        }
        }
    }
}