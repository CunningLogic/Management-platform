<?php

namespace app\models;

use yii\db\ActiveRecord;

class Agrotask extends ActiveRecord
{
			
	public static function tableName()
  {
        return 'agro_task';
  }

  public static function add($model)
	{
		$release = new Agrotask;
		$now_time = date('Y-m-d H:i:s',time());
		$release->created_at = $now_time;
		//$release->status     = Area::PENDING;
		$release->upper_uid = strip_tags($model['upper_uid']);
		$release->uid = strip_tags($model['uid']);
		$release->team_id = strip_tags($model['team_id']);
		if (isset($model['operator'])) {
			 $release->operator = strip_tags($model['operator']);			
		}
		if (isset($model['name'])) {
			 $release->name = strip_tags($model['name']);			
		}
		if (isset($model['date'])) {
			 $release->date = strip_tags($model['date']);
		}	
		if (isset($model['time'])) {
			 $release->time = strip_tags($model['time']);
		}
		if (isset($model['area'])) {
			 $release->area = strip_tags($model['area']);
		}
		if (isset($model['type'])) {
			 $release->type = strip_tags($model['type']);
		}
		if (isset($model['crop'])) {
			 $release->crop = strip_tags($model['crop']);
		}
		if (isset($model['crop_stage'])) {
			 $release->crop_stage = strip_tags($model['crop_stage']);
		}
		if (isset($model['prevent'])) {
			 $release->prevent = strip_tags($model['prevent']);
		}
		if (isset($model['setting'])) {
			 $release->setting = strip_tags($model['setting']);
		}
		if (isset($model['key_point'])) {
			 $release->key_point = strip_tags($model['key_point']);
		}
		if (isset($model['home'])) {
			 $release->home = strip_tags($model['home']);
		}
		if (isset($model['obstacle_point'])) {
			 $release->obstacle_point = strip_tags($model['obstacle_point']);
		}
		if (isset($model['plan_edge_poit'])) {
			 $release->plan_edge_poit = strip_tags($model['plan_edge_poit']);
		}
		if (isset($model['edge_point'])) {
			 $release->edge_point = strip_tags($model['edge_point']);
		}
		if (isset($model['way_point'])) {
			 $release->way_point = strip_tags($model['way_point']);
		}
		if (isset($model['lat'])) {
			 $release->lat = strip_tags($model['lat']);
		}
		if (isset($model['lng'])) {
			 $release->lng = strip_tags($model['lng']);
		}
		if (isset($model['location'])) {
			 $release->location = strip_tags($model['location']);
		}
		if (isset($model['battery_times'])) {
			 $release->battery_times = strip_tags($model['battery_times']);
		}
		if (isset($model['interval'])) {
			 $release->interval = strip_tags($model['interval']);
		}
		if (isset($model['calibrate_point'])) {
			 $release->calibrate_point = strip_tags($model['calibrate_point']);
		}
		if (isset($model['app_type'])) {
			 $release->app_type = strip_tags($model['app_type']);
		}
		if (isset($model['radar_height'])) {
			 $release->radar_height = strip_tags($model['radar_height']);
		}	
		if (isset($model['spray_flow'])) {
			 $release->spray_flow = strip_tags($model['spray_flow']);
		}	
		if (isset($model['work_speed'])) {
			 $release->work_speed = strip_tags($model['work_speed']);
		}	
		if (isset($model['spray_width'])) {
			 $release->spray_width = strip_tags($model['spray_width']);
		}			
		if (isset($model['ip'])) {			
		   $release->ip = strip_tags($model['ip']);
		}	
		if (isset($model['geoStartTime'])) {
            $release->geoStartTime = strip_tags($model['geoStartTime']);
        }
        if (isset($model['geoEndTime'])) {
            $release->geoEndTime = strip_tags($model['geoEndTime']);
        }
        if (isset($model['isInGeo'])) {
            $release->isInGeo = strip_tags($model['isInGeo']);
        }	
        if (isset($model['spraying_dir'])) {
            $release->spraying_dir = strip_tags($model['spraying_dir']);
        }
        if (isset($model['have_break_info'])) {
            $release->have_break_info = strip_tags($model['have_break_info']);
        }
        if (isset($model['last_spraying_break_dir'])) {
            $release->last_spraying_break_dir = strip_tags($model['last_spraying_break_dir']);
        }
        if (isset($model['last_spraying_break_index'])) {
            $release->last_spraying_break_index = strip_tags($model['last_spraying_break_index']);
        }
        if (isset($model['last_spraying_break_point'])) {
            $release->last_spraying_break_point = strip_tags($model['last_spraying_break_point']);
        }
		$release->updated_at = $now_time;		
		$release->save();
		return $release->id;
	}
   
   public static function updateInfo($model)
   {
        $release = Agrotask::findOne(['id' => $model['id']]);
        if (isset($model['name'])) {
			 $release->name = strip_tags($model['name']);
		}
		if (isset($model['date'])) {
			  $release->date = strip_tags($model['date']);
		}	
		if (isset($model['time'])) {
			 $release->time = strip_tags($model['time']);
		}
		if (isset($model['area'])) {
			 $release->area = strip_tags($model['area']);
		}
		if (isset($model['type'])) {
			 $release->type = strip_tags($model['type']);
		}
		if (isset($model['crop'])) {
			 $release->crop = strip_tags($model['crop']);
		}
		if (isset($model['crop_stage'])) {
			 $release->crop_stage = strip_tags($model['crop_stage']);
		}
		if (isset($model['prevent'])) {
			 $release->prevent = strip_tags($model['prevent']);
		}
		if (isset($model['setting'])) {
			 $release->setting = strip_tags($model['setting']);
		}
		if (isset($model['key_point'])) {
			 $release->key_point = strip_tags($model['key_point']);
		}
		if (isset($model['home'])) {
			 $release->home = strip_tags($model['home']);
		}
		if (isset($model['obstacle_point'])) {
			 $release->obstacle_point = strip_tags($model['obstacle_point']);
		}
		if (isset($model['plan_edge_poit'])) {
			 $release->plan_edge_poit = strip_tags($model['plan_edge_poit']);
		}
		if (isset($model['edge_point'])) {
			 $release->edge_point = strip_tags($model['edge_point']);
		}
		if (isset($model['way_point'])) {
			 $release->way_point = strip_tags($model['way_point']);
		}
		if (isset($model['lat'])) {
			 $release->lat = strip_tags($model['lat']);
		}
		if (isset($model['lng'])) {
			 $release->lng = strip_tags($model['lng']);
		}
		if (isset($model['location'])) {
			 $release->location = strip_tags($model['location']);
		}
		if (isset($model['battery_times'])) {
			 $release->battery_times = strip_tags($model['battery_times']);
		}
		if (isset($model['interval'])) {
			 $release->interval = strip_tags($model['interval']);
		}
		if (isset($model['app_type'])) {
			 $release->app_type = strip_tags($model['app_type']);
		}	
		if (isset($model['radar_height'])) {
			 $release->radar_height = strip_tags($model['radar_height']);
		}	
		if (isset($model['spray_flow'])) {
			 $release->spray_flow = strip_tags($model['spray_flow']);
		}	
		if (isset($model['work_speed'])) {
			 $release->work_speed = strip_tags($model['work_speed']);
		}	
		if (isset($model['spray_width'])) {
			 $release->spray_width = strip_tags($model['spray_width']);
		}				
		if (isset($model['ip'])) {			
		   $release->ip = strip_tags($model['ip']);
		}
		if (isset($model['geoStartTime'])) {
            $release->geoStartTime = strip_tags($model['geoStartTime']);
        }
        if (isset($model['geoEndTime'])) {
            $release->geoEndTime = strip_tags($model['geoEndTime']);
        }
        if (isset($model['isInGeo'])) {
            $release->isInGeo = $model['isInGeo'] ? 1 : 0;
        }
        if (isset($model['spraying_dir'])) {
            $release->spraying_dir = strip_tags($model['spraying_dir']);
        }
        if (isset($model['have_break_info'])) {
            $release->have_break_info = strip_tags($model['have_break_info']);
        }
        if (isset($model['last_spraying_break_dir'])) {
            $release->last_spraying_break_dir = strip_tags($model['last_spraying_break_dir']);
        }
        if (isset($model['last_spraying_break_index'])) {
            $release->last_spraying_break_index = strip_tags($model['last_spraying_break_index']);
        }
        if (isset($model['last_spraying_break_point'])) {
            $release->last_spraying_break_point = strip_tags($model['last_spraying_break_point']);
        }
       	if (isset($model['calibrate_point'])) {
			 $release->calibrate_point = strip_tags($model['calibrate_point']);
		}
        $release->updated_at = date('Y-m-d H:i:s');  
        $release->save();       
        return $release->id;
    } 

   public static function updatePoliciesNoInfo($model)
   {
        $release = Agrotask::findOne(['id' => $model['id']]);
        $release->policies_no = strip_tags($model['policies_no']);
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
				->from(Agrotask::tableName())
				->where($where)
				->orderBy($orderby_sort)
				->offset($start)
				->limit($limit)
				->all();
		} else {
			return (new \yii\db\Query())
				->select($fields)
				->from(Agrotask::tableName())
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
				->from(Agrotask::tableName())
				->where($str, $params)
				->orderBy($orderby_sort)
				->offset($start)
				->limit($limit)
				->all();
		} else {
			return (new \yii\db\Query())
				->select($fields)
				->from(Agrotask::tableName())
				->where($str, $params)
				->orderBy($orderby_sort)
				->all();
		}
	}
	public static function getTaskSubQuery($where) {
		$subQuery = (new \yii\db\Query())->select('*')->from(Agrotask::tableName());
        $subQuery->Where(['=', 'deleted', $where['deleted']]);

        $or_condition = ['or'];
        if (isset($where['uid'])) {
            //作为飞手的
            $or_condition[] = ['=', 'uid', $where['uid']];
        }
        if (isset($where['upper_uid'])) {
            $or_condition[] = ['=', 'upper_uid', $where['upper_uid']];//作为老板的
        }
        if (isset($where['team_id'])) {
            $or_condition[] = ['in', 'team_id', $where['team_id']];	//作为队长的
        }
        
        if (count($or_condition) > 1) {
            $subQuery->andWhere($or_condition);
        }

		return $subQuery;
	}
	public static function getTasksWhere($where, $subQuery, $fields, $start = 0, $limit = 0)
	{	
		  $query = new  \yii\db\Query();
		  $query->select($fields)->from([Agrotask::tableName()=>$subQuery]);
		  if (isset($where['deleted']) && $where['deleted']) {
		  	 $query->andWhere(['=', 'deleted', $where['deleted']]);
		  }		
		  if (isset($where['starttime']) && $where['starttime']) {
		  	 $query->andWhere(['>=', 'date', $where['starttime']]);
		  }
		  if (isset($where['endtime']) && $where['endtime']) {
		  	 $query->andWhere(['<=', 'date', $where['endtime']]);
		  }

		  $or_condition = ['or'];
		  if (isset($where['name']) && $where['name']) {
		      $or_condition[] = ['like', 'name', $where['name']];
		  }
		  if (isset($where['location']) && $where['location']) {
		      $or_condition[] = ['like', 'location', $where['location']];
		  }
		  if (count($or_condition) > 1) {
		      $query->andWhere($or_condition);
          }

		  if (isset($where['order']) && $where['order']) {
		  	 if (isset($where['updown']) && $where['updown'] == '1') {
		  	 	$query->orderBy([$where['order'] => SORT_DESC]);	
		  	 }
		  	 else {
		  	 	$query->orderBy([$where['order'] => SORT_ASC]);
		  	 }
		  }
		  else {
		  	 $query->orderBy(['id' => SORT_DESC]); 
		  }
		  	
		  if ($limit > 0) {
		  	 	$query->offset($start)->limit($limit);
		  }	 
		  return $query->all();
	}
	public static function getTasksWhereCount($where, $subQuery, $fields, $start = 0, $limit = 0)
	{	
		$query = new  \yii\db\Query();
		$query->select($fields)->from([Agrotask::tableName()=>$subQuery]);
/*		if (isset($where['upper_uid']) && $where['upper_uid']) {
			 $query->andWhere(['=', 'upper_uid', $where['upper_uid']]);
		}
		if (isset($where['uid']) && $where['uid']) {
			 $query->andWhere(['=', 'uid', $where['uid']]);
		}
		if (isset($where['team_id']) && $where['team_id']) {
			 $query->andWhere(['in', 'team_id', $where['team_id']]);
		}*/
		if (isset($where['deleted']) && $where['deleted']) {
			 $query->andWhere(['=', 'deleted', $where['deleted']]);
		}		
		if (isset($where['starttime']) && $where['starttime']) {
			 $query->andWhere(['>=', 'date', $where['starttime']]);
		}
		if (isset($where['endtime']) && $where['endtime']) {
			 $query->andWhere(['<=', 'date', $where['endtime']]);
		}

		$or_condition = ['or'];
		if (isset($where['name']) && $where['name']) {
		    $or_condition[] = ['like', 'name', $where['name']];
		}
		if (isset($where['location']) && $where['location']) {
		    $or_condition[] = ['like', 'location', $where['location']];
		}
		if (count($or_condition) > 1) {
		    $query->andWhere($or_condition);
        }

		$query->orderBy(['id' => SORT_DESC]);	
		if ($limit > 0) {
			 	$query->offset($start)->limit($limit);
		}	 
		return $query->count();
	}
	public static function getDateWhere($where,$fields,$start = 0, $limit = 0)
    {	
		$query = new  \yii\db\Query();
		$query->select($fields)->from(Agrotask::tableName());
		if (isset($where['upper_uid']) && $where['upper_uid']) {
			 $query->andWhere(['=', 'upper_uid', $where['upper_uid']]);
		}
		if (isset($where['uid']) && $where['uid']) {
			 $query->andWhere(['=', 'uid', $where['uid']]);
		}
		if (isset($where['deleted']) && $where['deleted']) {
			 $query->andWhere(['=', 'deleted', $where['deleted']]);
		}		
		if (isset($where['starttime']) && $where['starttime']) {
			 $query->andWhere(['>=', 'date', $where['starttime']]);
		}
		if (isset($where['endtime']) && $where['endtime']) {
			 $query->andWhere(['<=', 'date', $where['endtime']]);
		}
		if (isset($where['team_id']) && $where['team_id']) {
			 $query->andWhere(['=', 'team_id', $where['team_id']]);
		}
		$query->orderBy(['id' => SORT_DESC]); 
		if ($limit > 0) {
			 	$query->offset($start)->limit($limit);
		}	 
		return $query->all();
    }
    public static function getNameByID($where,$fields='name')
    {
        $query = new  \yii\db\Query();
        $query->select($fields)->from(Agrotask::tableName());
        if (isset($where['taskid'])) {
            $query->andWhere(['=', 'id', $where['taskid']]);
        }
        $data = $query->one();
        return isset($data) ? $data['name'] : '';
    }
}
