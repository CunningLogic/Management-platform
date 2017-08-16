<?php

namespace app\models;

use yii\db\ActiveRecord;

class Agroaddress extends ActiveRecord
{
    public static function tableName()
    {
        return 'agro_address';
    }

    /*
    * 插入数据
    * @param int $model 
    * example:'$model=123'
    */
    public static function add($model = array() )
    {
        $release = new Agroaddress;
        $now_time = date('Y-m-d H:i:s',time());
        $release->created_at = $now_time;
        $release->name    = strip_tags($model['name']);
        $release->aid       = strip_tags($model['aid']);
        $release->parent  = strip_tags($model['parent']);
        $release->type  = strip_tags($model['type']);
        $release->updated_at = $now_time;
        $release->save();
        return $release->id;
    }

    public static function updateInfo( $model = array() )
    {
        $release              = Agroaddress::findOne(['id' => $model['id']]);
        $release->name    = strip_tags($model['name']);
        $release->aid       = strip_tags($model['aid']);
        $release->parent  = strip_tags($model['parent']);
        $release->type  = strip_tags($model['type']);
        $release->operator    = $model['operator'];
        $release->ip          = $model['ip'];
        $release->updated_at  = date('Y-m-d H:i:s');
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
                ->from(Agroaddress::tableName())
                ->where($where)
                ->orderBy($orderby_sort)
                ->offset($start)
                ->limit($limit)
                ->all();
        } else {
            return (new \yii\db\Query())
                ->select($fields)
                ->from(Agroaddress::tableName())
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
        $arr    = [];
        foreach ($where as $v) {
            $arr[]               = $v[0] . $v[1] . ' :' . $v[0];
            $params[':' . $v[0]] = $v[2];
        }
        $str = implode(' AND ', $arr);

        if ($limit > 0) {
            return (new \yii\db\Query())
                ->select($fields)
                ->from(Agroaddress::tableName())
                ->where($str, $params)
                ->orderBy($orderby_sort)
                ->offset($start)
                ->limit($limit)
                ->all();
        } else {
            return (new \yii\db\Query())
                ->select($fields)
                ->from(Agroaddress::tableName())
                ->where($str, $params)
                ->orderBy($orderby_sort)
                ->all();
        }
    }

}
