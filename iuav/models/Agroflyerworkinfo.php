<?php

namespace app\models;

use yii\db\ActiveRecord;
use Yii;

class Agroflyerworkinfo extends ActiveRecord 
{		
	public static function tableName() {
		return 'agro_flyer_workinfo';
	}
	public static function add($model) {
		$release = new Agroflyerworkinfo;
		$now_time = date('Y-m-d H:i:s',time());

		$release->created_at = $now_time;
		$release->team_id = $model['team_id'];
		$release->upper_uid = $model['upper_uid'];
		$release->uid = $model['uid'];
		$release->all_time = $model['work_time'];
		$release->all_area = $model['work_area'];
		$release->all_times = 1;

		$release->updated_at = $now_time;		
		$release->save();
		return $release->id;
	}
	public static function getAndEqualWhere($where = [], $fields = '*', $start = 0, $limit = 20, $orderby = 'id', $sort = 1)
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
				->from(Agroflyerworkinfo::tableName())
				->where($where)
				->orderBy($orderby_sort)
				->offset($start)
				->limit($limit)
				->all();
		} else {
			return (new \yii\db\Query())
				->select($fields)
				->from(Agroflyerworkinfo::tableName())
				->where($where)
				->orderBy($orderby_sort)
				->all();
		}
	}
}