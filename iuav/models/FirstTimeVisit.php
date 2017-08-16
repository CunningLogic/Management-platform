<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii;

class FirstTimeVisit extends ActiveRecord
{

    public static function tableName()
    {
        return 'first_time_visit';
    }

    public static function isFirstTimeVisit($uid) {
        $row = FirstTimeVisit::findOne(['uid' => $uid]);
        if (!$row) {
            return true;
        }

        return false;
    }

    public static function markVisited($uid, $account) {
        $row = new FirstTimeVisit;
        $row->uid = $uid;
        $row->account = $account;
        $row->save();

        return $row->id;
    }
}
