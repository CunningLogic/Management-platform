<?php

namespace app\models;

use yii\db\ActiveRecord;

class Bossviewworkinfo extends ActiveRecord
{
    public static function tableName()
    {
        return '_boss_view_workinfo';
    }

    public static function get_work_time($uid)
    {
        $row = Bossviewworkinfo::findOne(['upper_uid' => $uid]);
        if (!$row) {
            return null;
        }

        return intval($row->time);
    }
}
