<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Activity extends ActiveRecord
{
	const PENDING  = 'pending';
	const READY    = 'ready';
	const RELEASE  = 'release';
		
	public static function tableName()
  {
        return 'activity';
  }

  public static function add($model)
	{
		$release = new Activity;
		$now_time = date('Y-m-d H:i:s',time());
		$release->create_at = $now_time;
		//$release->status     = Area::PENDING;
		$release->name = $model['name'];
		$release->token = $model['token'];
	  $release->applicant = $model['applicant'];
		$release->ip = $model['ip'];
		$release->nationality = $model['nationality'];
		$release->startfrom = $model['startfrom'];
		$release->startto = $model['startto'];
		$release->joinfrom = $model['joinfrom'];
		$release->jointo = $model['jointo'];
		$release->desc = $model['desc'];
		$release->ext1 = $model['ext1'];
		$release->ext2 = $model['ext2'];
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
				->from(Activity::tableName())
				->where($where)
				->orderBy($orderby_sort)
				->offset($start)
				->limit($limit)
				->all();
		} else {
			return (new \yii\db\Query())
				->select($fields)
				->from(Activity::tableName())
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
				->from(Activity::tableName())
				->where($str, $params)
				->orderBy($orderby_sort)
				->offset($start)
				->limit($limit)
				->all();
		} else {
			return (new \yii\db\Query())
				->select($fields)
				->from(Activity::tableName())
				->where($str, $params)
				->orderBy($orderby_sort)
				->all();
		}
	}
	
	public static function getWhereCount($activity_id)
	{
		return Activity::find()
			->where(['activity_id' => $activity_id])
			->count();
	}
	

}
