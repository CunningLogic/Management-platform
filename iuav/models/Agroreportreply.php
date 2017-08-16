<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Agroreportreply extends ActiveRecord
{
			
	public static function tableName()
  {
        return 'agro_report_reply';
  }

  public static function addRemark($model)
	{
		$release = new Agroreportreply;
		$now_time = date('Y-m-d H:i:s',time());
		$release->created_at = $now_time;
		//$release->status     = Area::PENDING;
		$release->report_id = strip_tags($model['id']);
		$release->message = strip_tags($model['remark']);
		$release->status = strip_tags($model['status']);
    $release->operator = strip_tags($model['operator']);  
		$release->ip = $model['ip'];
	//	$release->ext1 = $model['ext1'];
		//$release->ext2 = $model['ext2'];
		$release->updated_at = $now_time;		
		$release->save();
		return $release->id;
	}
	
	 
	public static function getAndEqualWhereCount($where = [])
	{
		
		return (new \yii\db\Query())
				->select('id')
				->from(Agroreportreply::tableName())
				->where($where)
				->count();
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
				->from(Agroreportreply::tableName())
				->where($where)
				->orderBy($orderby_sort)
				->offset($start)
				->limit($limit)
				->all();
		} else {
			return (new \yii\db\Query())
				->select($fields)
				->from(Agroreportreply::tableName())
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
				->from(Agroreportreply::tableName())
				->where($str, $params)
				->orderBy($orderby_sort)
				->offset($start)
				->limit($limit)
				->all();
		} else {
			return (new \yii\db\Query())
				->select($fields)
				->from(Agroreportreply::tableName())
				->where($str, $params)
				->orderBy($orderby_sort)
				->all();
		}
	}	
	

}
