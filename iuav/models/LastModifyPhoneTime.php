<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class LastModifyPhoneTime extends ActiveRecord
{
    public static function tableName()
    {
        return 'last_modify_phone_time';
    }
    public static function add($model)
    {
        $release = (new LastModifyPhoneTime)->findOne($model['uid']);//类似replace
        if(!$release)  $release = new LastModifyPhoneTime;
        $release->uid = $model['uid'];
        $release->last_modify_phone_time = $model['last_modify_phone_time'];
        $release->save();
    }

    public static function getLastModifyTs($where = [], $fields = '*')
    {
        $query = new  \yii\db\Query();
        $query->select($fields)->from('last_modify_phone_time');
        if (isset($where['uid'])) {
            $query->andWhere(['=', 'uid', $where['uid']]);
        }
        return $query->one();
    }
}