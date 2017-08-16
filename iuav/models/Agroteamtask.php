<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii;

class Agroteamtask extends ActiveRecord
{
			
	public static function tableName()
  	{
        return 'agro_team_task';
  	}

  	public static function add($model)
	{
		$release = new Agroteamtask;
		$now_time = date('Y-m-d H:i:s',time());
		$release->created_at = $now_time;
		$release->uid = strip_tags($model['uid']);					
		$release->team_id = strip_tags($model['team_id']);	
		$release->task_id = strip_tags($model['task_id']);
		$release->updated_at = $now_time;	
		if (isset($model['ip'])) {
			 $release->ip = strip_tags($model['ip']);
		}	
		$release->save();
		return $release->id;
	}
	public static function getShareTaskWhere($where = [], $start = 0, $limit = 0, $orderby = 'id', $sort = 1, $fields = '*')
	{
		  $query = new  \yii\db\Query();
		  $query->select($fields)->from('agro_team_task')->leftJoin('agro_task', 'agro_team_task.task_id = agro_task.id');
		  if (isset($where['uid'])) {		  	
		  	 $query->andWhere(['=', 'agro_team_task.uid', $where['uid']]);
		  }
		  if (isset($where['team_id'])) {		  	
		  	 $query->andWhere(['=', 'agro_team_task.team_id', $where['team_id']]);
		  }
		  if (isset($where['deleted'])) {		  	
		  	 $query->andWhere(['=', 'agro_task.deleted', $where['deleted']]);
		  	 $query->andWhere(['=', 'agro_team_task.deleted', $where['deleted']]);
		  }		

		  $query->orderBy(['agro_task.id' => SORT_DESC]);	
		  if ($limit > 0) {
		  	 	$query->offset($start)->limit($limit);
		  }	 
		  return $query->all();
	}

/*	public static function getShareTaskWhere($where = [], $start = 0, $limit = 0, $orderby = 'id', $sort = 1, $fields = '*')
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
				->from(Agroteamtask::tableName())
				->where($where)
				->orderBy($orderby_sort)
				->offset($start)
				->limit($limit)
				->all();
		} else {
			return (new \yii\db\Query())
				->select($fields)
				->from(Agroteamtask::tableName())
				->where($where)
				->orderBy($orderby_sort)
				->all();
		}
	}*/
}