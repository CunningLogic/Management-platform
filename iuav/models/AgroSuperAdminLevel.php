<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii;

class AgroSuperAdminLevel extends ActiveRecord
{

    public static function tableName()
    {
        return 'super_admin_level';
    }

    public static function add($model)
    {
        $release = new AgroSuperAdminLevel;
        $release->uid = $model['uid'];
        $release->level = $model['level'];
        $release->save();
        return $release->id;
    }

    public static function getUidLevel($where = [], $start = 0, $limit = 0, $orderby = 'id', $sort = 1, $fields = '*')
    {
        $release = AgroSuperAdminLevel::findOne(['uid' => $where['uid']]);
        if (!$release) {
            return null;
        }
        return $release->level;
    }
}
