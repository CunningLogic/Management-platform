<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use app\models\Ddsliveroom;

class Ddslivevideo extends ActiveRecord
{

    public static function tableName()
    {
        return 'dds_live_video';
    }

    public static function add($model)
    {
        $release = new Ddslivevideo;
        $now_time = date('Y-m-d H:i:s',time());
        $release->created_at = $now_time;		
        $release->low_url = $model['low_url'];
        $release->url = $model['url'];
        $release->screenshot = $model['screenshot'];
        $release->type = $model['type'];
        $release->ext1 = $model['ext1'];
        $release->ext2 = $model['ext2'];
        $release->updated_at = $now_time;		
        $release->save();
        Ddsliveroom::setAllRoomInfo();
        return $release->id;
    }

    public static function getAndEqualWhere($where = [], $start = 0, $limit = 20, $orderby = 'id', $sort = 1, $fields = '*')
    {
        $orderby_sort = [];
        if ( $sort > 0 ) {
            $orderby_sort[$orderby] = SORT_DESC;
        } else {
            $orderby_sort[$orderby] = SORT_ASC;
        }

        if ( $limit > 0 ) {
            return (new \yii\db\Query())
                ->select($fields)
                ->from(Ddslivevideo::tableName())
                ->where($where)
                ->orderBy($orderby_sort)
                ->offset($start)
                ->limit($limit)
                ->all();
        } else {
            return (new \yii\db\Query())
                ->select($fields)
                ->from(Ddslivevideo::tableName())
                ->where($where)
                ->orderBy($orderby_sort)
                ->all();
        }
    }

    public static function getAndWhere($where = [], $start = 0, $limit = 20, $orderby = 'id', $sort = 1, $fields = '*')
    {
        $orderby_sort = [];
        if ( $sort > 0 ) {
            $orderby_sort[$orderby] = SORT_DESC;
        } else {
            $orderby_sort[$orderby] = SORT_ASC;
        }

        $params = [];
        $arr = [];
        foreach ( $where as $v ) {
            $arr[] = $v[0] .  $v[1] . ' :' . $v[0];
            $params[':'.$v[0]] = $v[2];
        }
        $str = implode(' AND ', $arr);

        if ( $limit > 0 ) {
            return (new \yii\db\Query())
                ->select($fields)
                ->from(Ddslivevideo::tableName())
                ->where($str, $params)
                ->orderBy($orderby_sort)
                ->offset($start)
                ->limit($limit)
                ->all();
        } else {
            return (new \yii\db\Query())
                ->select($fields)
                ->from(Ddslivevideo::tableName())
                ->where($str, $params)
                ->orderBy($orderby_sort)
                ->all();
        }
    }

    public static function updateInfo($model)
    {
        $release = Ddslivevideo::findOne(['id' => $model['id']]);
//        $release->level = $model['level'];
        $release->low_url = $model['low_url'];
        $release->url = $model['url'];
        $release->screenshot = $model['screenshot'];
        $release->type = $model['type'];
        $release->updated_at = date('Y-m-d H:i:s');		
        $release->save();
        Ddsliveroom::setAllRoomInfo();
        return $release->id;
    }



}
