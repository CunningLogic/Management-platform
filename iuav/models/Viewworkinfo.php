<?php

namespace app\models;

use yii\db\ActiveRecord;

class Viewworkinfo extends ActiveRecord
{
    public static function tableName()
    {
        return '_view_workinfo';
    }

    public static function get_work_info($uid)
    {
        $row = Viewworkinfo::findOne(['uid' => $uid]);
        if (!$row) {
            return null;
        }

        return $row->area;
    }
}