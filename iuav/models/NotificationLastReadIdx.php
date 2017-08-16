<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class NotificationLastReadIdx extends ActiveRecord
{
    public static function tableName() {
        return 'notification_last_read_idx';
    }

    public static function add($uid)
    {
        $release = new NotificationLastReadIdx;
        $release->uid = $uid;
        $release->save();
    }

    public static function upsert($uid, $new_idx) {
        $row = NotificationLastReadIdx::findOne(['uid' => $uid]);
        if ($row) {
            $row->last_read_idx = $new_idx;
            $row->save();
        } else {
            NotificationLastReadIdx::add($uid);
        }
    }
}
