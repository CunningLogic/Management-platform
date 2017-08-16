<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class UserAvatar extends ActiveRecord
{

    public static function tableName()
    {
        return 'user_avatar';
    }

    public static function setUserAvatar($account, $uid, $avatar_path) {
        $row = UserAvatar::findOne(['uid' => $uid]);
        if ($row) {
            $old = $row->avatar;
            $row->avatar = $avatar_path;
            $row->save();

            return $old;
        } else {
            $row = new UserAvatar;
            $row->account = $account;
            $row->uid = $uid;
            $row->avatar = $avatar_path;
            $row->save();

            return null;
        }
    }

    public static function getUserAvatar($uid) {
        $row = UserAvatar::findOne(['uid' => $uid]);
        if (!$row) {
            return null;
        }

        return Yii::$app->params['OSS_ENV']['bind_domain'].$row['avatar'];
    }
}
