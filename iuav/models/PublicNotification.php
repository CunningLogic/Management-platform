<?php

namespace app\models;

use yii\db\ActiveRecord;

class PublicNotification extends ActiveRecord
{

    public static function tableName()
    {
        return 'public_notification';
    }

    public static function add($model)
    {
        $release = new PublicNotification;
        $release->uid = isset($model['uid']) ? $model['uid'] : '*';
        if (isset($model['date'])) {
            $release->date = strip_tags($model['date']);
        }
        if (isset($model['content'])) {
            $release->content = strip_tags($model['content']);
        }

        $release->save();
        return $release->id;
    }

    public static function getAndEqualWhere($where = [], $start = 0, $limit = 20, $orderby = 'id', $sort = 1, $fields = '*')
    {
        $orderby_sort = [];
        if ($sort > 0) {
            $orderby_sort[$orderby] = SORT_DESC;
        } else {
            $orderby_sort[$orderby] = SORT_ASC;
        }

        if ($limit > 0) {
            return (new \yii\db\Query())
                ->select($fields)
                ->from(PublicNotification::tableName())
                ->where($where)
                ->orderBy($orderby_sort)
                ->offset($start)
                ->limit($limit)
                ->all();
        } else {
            return (new \yii\db\Query())
                ->select($fields)
                ->from(PublicNotification::tableName())
                ->where($where)
                ->orderBy($orderby_sort)
                ->all();
        }
    }

    public static function getAndWhere($where = [], $start = 0, $limit = 20, $orderby = 'id', $sort = 1, $fields = '*')
    {
        $orderby_sort = [];
        if ($sort > 0) {
            $orderby_sort[$orderby] = SORT_DESC;
        } else {
            $orderby_sort[$orderby] = SORT_ASC;
        }

        $params = [];
        $arr = [];
        foreach ($where as $v) {
            $arr[] = $v[0] . $v[1] . ' :' . $v[0];
            $params[':' . $v[0]] = $v[2];
        }
        $str = implode(' AND ', $arr);

        if ($limit > 0) {
            return (new \yii\db\Query())
                ->select($fields)
                ->from(PublicNotification::tableName())
                ->where($str, $params)
                ->orderBy($orderby_sort)
                ->offset($start)
                ->limit($limit)
                ->all();
        } else {
            return (new \yii\db\Query())
                ->select($fields)
                ->from(PublicNotification::tableName())
                ->where($str, $params)
                ->orderBy($orderby_sort)
                ->all();
        }
    }
}
