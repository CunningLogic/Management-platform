<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

use app\components\Helper;

class VisionariesUser extends ActiveRecord
{
    public static function tableName()
    {
        return 'visionaries_user';
    }

    public static function add($model)
    {
        $release = new VisionariesUser;
        $now_time = date('Y-m-d H:i:s',time());
        $release->created_at = $now_time;		
        $release->name = strip_tags($model['name']);
        $release->photo = $model['photo'];
        $release->elite = $model['elite'];
        $release->blo = strip_tags($model['blo']);
        $release->dji_gear = strip_tags($model['dji_gear']);
        $release->quote = strip_tags($model['quote']);
        $release->status = 'draft';           
        $release->updated_at = $now_time;		
        $release->save();
        return $release->id;
    } 
    public static function updateInfo($model)
    {
        $release = VisionariesUser::findOne(['id' => $model['id']]);
        $release->name = strip_tags($model['name']);
        if ($model['photo']) {
            $release->photo = $model['photo'];
        }       
        $release->elite = $model['elite'];
        $release->blo = strip_tags($model['blo']);
        $release->dji_gear = strip_tags($model['dji_gear']);
        $release->quote = strip_tags($model['quote']);
        $release->updated_at = date('Y-m-d H:i:s');     
        $release->save();       
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
                ->from(VisionariesUser::tableName())				
                ->where($where)
                ->orderBy($orderby_sort)
                ->offset($start)
                ->limit($limit)
                ->all();
        } else {
            return (new \yii\db\Query())
                ->select($fields)
                ->from(VisionariesUser::tableName())
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
                ->from(VisionariesUser::tableName())
                ->where($str, $params)
                ->orderBy($orderby_sort)
                ->offset($start)
                ->limit($limit)
                ->all();
        } else {
            return (new \yii\db\Query())
                ->select($fields)
                ->from(VisionariesUser::tableName())
                ->where($str, $params)
                ->orderBy($orderby_sort)
                ->all();
        }
    }

    public static function changeStatus($model)
    {
        $release = VisionariesUser::findOne(['id' => $model['id']]);
        $release->status = $model['status'];
        $release->updated_at = date('Y-m-d H:i:s');		
        $release->save();       
        return $release->id;
    }
    public static function updateInfoDisable($model)
    {
        $release = VisionariesUser::findOne(['id' => $model['id']]);
        $release->disable = $model['disable'];
        $release->updated_at = date('Y-m-d H:i:s');		
        $release->save();        
        return $release->id;
    }

   
}
