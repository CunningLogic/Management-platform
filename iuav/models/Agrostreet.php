<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Agrostreet extends ActiveRecord
{
			
	public static function tableName()
  {
        return 'agro_street';
  }

  public static function add($model)
	{
		$release = new Agrostreet;
		$now_time = date('Y-m-d H:i:s',time());
		$release->created_at = $now_time;	
		$release->area_no = strip_tags($model['area_no']);
		$release->name = strip_tags($model['name']);
		$release->street_no = strip_tags($model['street_no']);
		$release->updated_at = $now_time;		
		$release->save();
		return $release->id;
	}
	 public static function updateInfo($model)
   {
        $release = Agrostreet::findOne(['id' => $model['id']]);
        $release->body_code = $model['body_code'];
        $release->body_code = $model['body_code'];
				$release->hardware_id = $model['hardware_id'];
			  $release->activation = $model['activation'];
				$release->scan_date = $model['scan_date'];
				$release->type = $model['type'];
				$release->operator = $model['operator'];
				$release->ip = $model['ip'];
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
				->from(Agrostreet::tableName())
				->where($where)
				->orderBy($orderby_sort)
				->offset($start)
				->limit($limit)
				->all();
		} else {
			return (new \yii\db\Query())
				->select($fields)
				->from(Agrostreet::tableName())
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
				->from(Agrostreet::tableName())
				->where($str, $params)
				->orderBy($orderby_sort)
				->offset($start)
				->limit($limit)
				->all();
		} else {
			return (new \yii\db\Query())
				->select($fields)
				->from(Agrostreet::tableName())
				->where($str, $params)
				->orderBy($orderby_sort)
				->all();
		}
	}	
	

}
