<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Apply extends ActiveRecord
{
	const PENDING  = 'pending';
	const READY    = 'ready';
	const RELEASE  = 'release';
		
	public static function tableName()
  {
        return 'apply';
  }

  public static function add($model)
	{
		$release = new Apply;
		$now_time = date('Y-m-d H:i:s',time());
		$release->created_at = $now_time;
		//$release->status     = Area::PENDING;
		$release->activity_id = $model['activity_id'];
		$release->user_key = $model['user_key'];
	  $release->email = $model['email'];
		$release->ip = $model['ip'];
		$release->nationality = $model['nationality'];
		$release->ip_country = $model['ip_country'];
		$release->joindate = date('Y-m-d',time() );
		$release->ext2 = $model['ext2'];
		$release->ext1 = $model['ext1'];
		$release->prize_id = $model['prize_id'];
		$release->updated_at = $now_time;		
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
				->from(Apply::tableName())
				->where($where)
				->orderBy($orderby_sort)
				->offset($start)
				->limit($limit)
				->all();
		} else {
			return (new \yii\db\Query())
				->select($fields)
				->from(Apply::tableName())
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
				->from(Apply::tableName())
				->where($str, $params)
				->orderBy($orderby_sort)
				->offset($start)
				->limit($limit)
				->all();
		} else {
			return (new \yii\db\Query())
				->select($fields)
				->from(Apply::tableName())
				->where($str, $params)
				->orderBy($orderby_sort)
				->all();
		}
	}
	
	public static function getWhereCount($activity_id)
	{
		return Apply::find()
			->where(['activity_id' => $activity_id])
			->count();
	}

	


	

}
