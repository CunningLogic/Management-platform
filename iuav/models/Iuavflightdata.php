<?php

namespace app\models;
use Yii;
use yii\db\ActiveRecord;

class Iuavflightdata extends ActiveRecord
{
			
	public static function tableName()
  {
        return 'iuav_flight_data';
  }

  public static function add($model)
	{
		$release = new Iuavflightdata;
		$now_time =time()-28800;		
		$release->team_id = strip_tags($model['team_id']);			
		$release->user_id = strip_tags($model['user_id']);
		$release->version = strip_tags($model['version']);
		$release->timestamp = strip_tags($model['timestamp']);
		$release->longi = strip_tags($model['longi']);
		$release->lati = strip_tags($model['lati']);
		$release->alti = strip_tags($model['alti']);
		$release->product_sn = strip_tags($model['product_sn']);
		$release->spray_flag = strip_tags($model['spray_flag']);
		$release->motor_status = strip_tags($model['motor_status']);
		$release->radar_height = strip_tags($model['radar_height']);
		$release->velocity_x = strip_tags($model['velocity_x']);
		$release->velocity_y = strip_tags($model['velocity_y']);
		$release->farm_delta_y = strip_tags($model['farm_delta_y']);
		$release->farm_mode = strip_tags($model['farm_mode']);
		$release->pilot_num = strip_tags($model['pilot_num']);
		$release->session_num = strip_tags($model['session_num']);
		$release->frame_index = strip_tags($model['frame_index']);
		$release->frame_flag = strip_tags($model['frame_flag']);
		$release->flight_version = strip_tags($model['flight_version']);
		$release->plant = strip_tags($model['plant']);		
		$release->work_area = strip_tags($model['work_area']);
		$release->boss_id = strip_tags($model['upper_uid']);
        $release->create_time = $now_time;
		$release->save();
		return $release->id;
	}
   
   public static function updateInfo($model)
   {
        $release = Iuavflightdata::findOne(['id' => $model['id']]);
       	$release->realname = strip_tags($model['realname']);
		$release->idcard = strip_tags($model['idcard']);
		$release->phone = strip_tags($model['phone']);
		$release->job_level = strip_tags($model['job_level']);
		$release->address = strip_tags($model['address']);		
		$release->ip = $model['ip'];
        $release->updated_at = date('Y-m-d H:i:s');  
        $release->save();       
        return $release->id;
    } 

   public static function deletedFlyer($model)
   {
        $release = Iuavflightdata::findOne(['id' => $model['id']]);
        $release->deleted = strip_tags($model['deleted']);	
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
				->from(Iuavflightdata::tableName())
				->where($where)
				->orderBy($orderby_sort)
				->offset($start)
				->limit($limit)
				->all();
		} else {
			return (new \yii\db\Query())
				->select($fields)
				->from(Iuavflightdata::tableName())
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
				->from(Iuavflightdata::tableName())
				->where($str, $params)
				->orderBy($orderby_sort)
				->offset($start)
				->limit($limit)
				->all();
		} else {
			return (new \yii\db\Query())
				->select($fields)
				->from(Iuavflightdata::tableName())
				->where($str, $params)
				->orderBy($orderby_sort)
				->all();
		}
	}
	public static function getIDWhere($where,$fields,$start = 0, $limit = 0)
	{	
		  $query = new  \yii\db\Query();
		  $query->select($fields)->from(Iuavflightdata::tableName());
		  if (isset($where['id'])) {		  	
		  	 $query->andWhere(['>', 'id', $where['id']]);
		  }
		  if (isset($where['frame_flag'])) {		  	
		  	 $query->andWhere(['=', 'frame_flag', $where['frame_flag']]);
		  }
		  $query->orderBy(['id' => SORT_ASC]);	
		  if ($limit > 0) {
		  	 	$query->offset($start)->limit($limit);
		  }	 
		  return $query->all();
	}

	public static function getTimestampWhere($where,$fields,$start = 0, $limit = 0)
	{	
		  $query = new  \yii\db\Query();
		  $query->select($fields)->from(Iuavflightdata::tableName());
		  if (isset($where['id'])) {		  	
		  	 $query->andWhere(['>', 'id', $where['id']]);
		  }
		  if (isset($where['upper_uid'])) {		  	
		  	 $query->andWhere(['=', 'boss_id', $where['boss_id']]);
		  }
		  if (isset($where['team_id'])) {		  	
		  	 $query->andWhere(['=', 'team_id', $where['team_id']]);
		  }
		  if (isset($where['start_date'])) {	  	
		  	 $query->andWhere(['>=', 'timestamp', $where['start_date']]);
		  }
		  if (isset($where['end_date'])) {		  	
		  	 $query->andWhere(['<=', 'timestamp', $where['end_date']]);
		  }
		  $query->groupBy('product_sn');
		  $query->orderBy(['id' => SORT_DESC]);
		  if ($limit > 0) {
		  	 	$query->offset($start)->limit($limit);
		  }	 
		  return $query->all();
	}
	public static function getByTimestamp($where,$fields,$start = 0, $limit = 0)
	{	
		  $query = new  \yii\db\Query();
		  $query->select($fields)->from(Iuavflightdata::tableName());
		  if (isset($where['startdate'])) 
		  {	  	
		  	 $query->andWhere(['>=', 'timestamp', $where['startdate']]);
		  	 if(isset($where['bossid']))
		  	 {
		  	 	$query->andWhere(['=', 'boss_id', $where['bossid']]);
		  	 }
		  	 if(isset($where['teamid']))
		  	 {
		  	 	$query->andWhere(['=', 'team_id', $where['teamid']]);
		  	 }
		  	 if(isset($where['product_sn']))
		  	 {
		  	 	$query->andWhere(['=', 'product_sn', $where['product_sn']]);
		  	 }
		  	 if(isset($where['uid']))
		  	 {
		  	 	$query->andWhere(['=', 'user_id', $where['uid']]);
		  	 }
		  }
		  
		  $query->orderBy(['timestamp' => SORT_ASC]);	
		  if ($limit > 0) {
		  	 	$query->offset($start)->limit($limit);
		  }	 

		  return $query->all();
	}

	public static function getOnline($where,$fields='id',$start = 0, $limit = 1)
	{	
		$current = (time()-10) * 1000;
        $where['startdate'] = $current;        
		$key = __CLASS__.__FUNCTION__.md5($where['product_sn'].$fields.$start.$limit);
        $data = Yii::$app->cache->get($key);
        if (empty($data)) {
			$result = self::getByTimestamp($where,$fields,$start, $limit);
			$aircraft = array();
			$aircraft['status'] = 0;
			if ($result) {
				$aircraft['status'] = 1;
			}
			Yii::$app->cache->set($key, $aircraft_status, 120);
		}
		return $data;
	}

}
