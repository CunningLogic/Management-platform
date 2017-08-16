<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Agroclient extends ActiveRecord
{
			
	public static function tableName()
    {
        return 'agro_client';
    }

    public static function add($model)
	{
		$release = new Agroclient;
		$now_time = date('Y-m-d H:i:s',time());
		$release->created_at = $now_time;		
		$release->user_id = $model['user_id'];
		if (isset($model['account'])) {
			      $release->account = $model['account'];
		}	
		if (isset($model['is_account'])) {
			      $release->is_account = $model['is_account'];
		}	
		$release->updated_at = $now_time;		
		$release->save();
		return $release->id;
	}
	public static function getAndEqualWhereCount($where = [])
	{
		
		return (new \yii\db\Query())
				->select('count(*) as allsum')
				->from(Agroclient::tableName())
				->where($where)
				->count();
	}
	public static function updateInfo($model)
    {
        $release = Agroclient::findOne(['user_id' => $model['user_id']]);
       	if (isset($model['is_account'])) {
			      $release->is_account = $model['is_account'];
		}		
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
				->from(Agroclient::tableName())
				->where($where)
				->orderBy($orderby_sort)
				->offset($start)
				->limit($limit)
				->all();
		} else {
			return (new \yii\db\Query())
				->select($fields)
				->from(Agroclient::tableName())
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
				->from(Agroclient::tableName())
				->where($str, $params)
				->orderBy($orderby_sort)
				->offset($start)
				->limit($limit)
				->all();
		} else {
			return (new \yii\db\Query())
				->select($fields)
				->from(Agroclient::tableName())
				->where($str, $params)
				->orderBy($orderby_sort)
				->all();
		}
	}	
	

}
