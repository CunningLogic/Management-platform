<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Agrorecord extends ActiveRecord
{
	public static function tableName()
	{
	    return 'agro_record';
	}
	public static function add($model)
	{
	    if (!isset(Yii::$app->params['ENV_COUNTRY']) || Yii::$app->params['ENV_COUNTRY'] != 'CN') {
	        return 0;
        }

        // only CN log operation record
		$release = new Agrorecord;
		$now_time = date('Y-m-d H:i:s',time());
		$release->created_at = $now_time;
		$release->create_date = date('Ymd', time());
		$release->upper_uid = $model['upper_uid'];
		$release->uid = $model['uid'];
		$release->team_id = $model['team_id'];
		$release->operator = $model['operator'];
		$release->type = $model['type'];
		$release->content = $model['content'];
		$release->detail = $model['detail'];
		$release->ip = $model['ip'];
		$release->deleted = '0';		
		$release->save(); 
		return $release->id;
	}
	public static function getAndWhere($where, $fields, $start = 0, $limit = 0) 
	{
 		$query = new  \yii\db\Query();
		$query->select($fields)->from('agro_record');
		if (isset($where['uid'])) {		  	
			 $query->andWhere(['=', 'uid', $where['uid']]);
		}
		if (isset($where['upper_uid'])) {		  	
			 $query->andWhere(['=', 'upper_uid', $where['upper_uid']]);		  	
		}
		if (isset($where['start_date'])) {		  	
			 $query->andWhere(['>=', 'agro_record.create_date', $where['start_date']]);
		}
		if (isset($where['end_date'])) {		  	
			 $query->andWhere(['<=', 'agro_record.create_date', $where['end_date']]);
		}
		if (!empty($where['operator'])) {		  	
			 $query->andWhere(['like', 'operator', $where['operator']]);
		}
		if (isset($where['content'])) {		  	
			 $query->andWhere(['like', 'content', $where['content']]); 	
		}	
		if (isset($where['type'])) {		  	
			 $query->andWhere(['=', 'type', $where['type']]);		  	
		}	
		$query->orderBy(['id' => SORT_DESC]);
			
		if ($limit > 0) {
			 	$query->offset($start)->limit($limit);
		}	 
		return $query->all();
	}
	public static function getWhereRecordsCount($where = [],$fields = 'count(id) as records_count')
	{		
		$query = new  \yii\db\Query();
		$query->select($fields)->from('agro_record');
		if (isset($where['uid'])) {		  	
			 $query->andWhere(['=', 'uid', $where['uid']]);
		}
		if (isset($where['upper_uid'])) {		  	
			 $query->andWhere(['=', 'upper_uid', $where['upper_uid']]);		  	
		}
		if (isset($where['start_date'])) {		  	
			 $query->andWhere(['>=', 'agro_record.create_date', $where['start_date']]);
		}
		if (isset($where['end_date'])) {		  	
			 $query->andWhere(['<=', 'agro_record.create_date', $where['end_date']]);
		}
		if (!empty($where['operator'])) {		  	
			 $query->andWhere(['like', 'operator', $where['operator']]);
		}
		if (isset($where['content'])) {		  	
			 $query->andWhere(['like', 'content', $where['content']]); 	
		}	
		if (isset($where['type'])) {		  	
			 $query->andWhere(['=', 'type', $where['type']]);		  	
		}
		return $query->one();
	}
}