<?php

namespace app\models;

use yii\db\ActiveRecord;

class Agroflight extends ActiveRecord
{
			
	public static function tableName()
    {
        return 'agro_flight';
    }

    public static function add($model)
	{
		$release = new Agroflight;
		$now_time = date('Y-m-d H:i:s',time());
		$release->created_at = $now_time;
		//$release->status     = Area::PENDING;
		$release->upper_uid = strip_tags($model['upper_uid']);
		$release->uid = strip_tags($model['uid']);
		$release->team_id = strip_tags($model['team_id']);
		$release->version = strip_tags($model['version']);
		$release->timestamp = strip_tags($model['timestamp']);
		$release->longi = strip_tags($model['longi']);
		$release->lati = strip_tags($model['lati']);
		if (isset($model['location']) && $model['location']) {
		   $release->location = strip_tags($model['location']);
	    }		
		$release->product_sn = strip_tags($model['product_sn']);
		$release->session_num = strip_tags($model['session_num']);
		$release->farm_delta_y = strip_tags($model['farm_delta_y']);
		$release->flight_version = strip_tags($model['flight_version']);
		$release->plant = strip_tags($model['plant']);
		$release->work_area = strip_tags($model['work_area']);
		$release->work_time = strip_tags($model['work_time']);
		$release->start_end = strip_tags($model['start_end']);
		if (isset($model['create_date'])) {
		   $release->create_date = strip_tags($model['create_date']);
	    }
	    if (isset($model['flight_data_id'])) {
		   $release->flight_data_id = strip_tags($model['flight_data_id']);
	    }
	   	if (isset($model['flyer_name'])) {
		   $release->flyer_name = strip_tags($model['flyer_name']);
	    }
	    if (isset($model['team_name'])) {
		   $release->team_name = strip_tags($model['team_name']);
	    }
	    if (isset($model['nickname'])) {
		   $release->nickname = strip_tags($model['nickname']);
	    }
		$release->updated_at = $now_time;		
		$release->save();
		return $release->id;
	}
   
    public static function updateInfo($model)
    {
        $release = Agroflight::findOne(['id' => $model['id']]);
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
        $release = Agroflight::findOne(['id' => $model['id']]);
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
				->from(Agroflight::tableName())
				->where($where)
				->orderBy($orderby_sort)
				->offset($start)
				->limit($limit)
				->all();
		} else {
			return (new \yii\db\Query())
				->select($fields)
				->from(Agroflight::tableName())
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
				->from(Agroflight::tableName())
				->where($str, $params)
				->orderBy($orderby_sort)
				->offset($start)
				->limit($limit)
				->all();
		} else {
			return (new \yii\db\Query())
				->select($fields)
				->from(Agroflight::tableName())
				->where($str, $params)
				->orderBy($orderby_sort)
				->all();
		}
	}

	public static function getTeamWhere($where,$fields,$start = 0, $limit = 0)
	{	
		  $query = new  \yii\db\Query();
		  $query->select($fields)->from('agro_flight')->leftJoin('agro_team', 'agro_flight.team_id = agro_team.id');
		  if (isset($where['uid'])) {		  	
		  	 $query->andWhere(['=', 'agro_flight.uid', $where['uid']]);
		  }
		  if (isset($where['team_id'])) {		  	
		  	 $query->andWhere(['=', 'agro_flight.team_id', $where['team_id']]);
		  }
		  if (isset($where['start_date'])) {		  	
		  	 $query->andWhere(['>=', 'agro_flight.create_date', $where['start_date']]);
		  }
		  if (isset($where['end_date'])) {		  	
		  	 $query->andWhere(['<=', 'agro_flight.create_date', $where['end_date']]);
		  }
		  if (isset($where['product_sn'])) {		  	
		  	 $query->andWhere(['in', 'agro_flight.product_sn', $where['product_sn']]);
		  }
		  if (isset($where['deleted'])) {		  	
		  	 $query->andWhere(['=', 'agro_flight.deleted', $where['deleted']]);
		  }			

		  $query->orderBy(['agro_flight.id' => SORT_DESC]);	
		  if ($limit > 0) {
		  	 	$query->offset($start)->limit($limit);
		  }	 
		  return $query->all();
	}

	public static function getActiveWhere($where,$subQuery, $fields, $start = 0, $limit = 0)
	{	
        $query = new \yii\db\Query();
        $query->select($fields)->from(['agro_flight'=>$subQuery]);
        if (isset($where['deleted'])) {
            $query->andWhere(['=', 'agro_flight.deleted', $where['deleted']]);
        }
        if (isset($where['start_date'])) {
            $query->andWhere(['>=', 'agro_flight.create_date', $where['start_date']]);
        }
        if (isset($where['end_date'])) {
            $query->andWhere(['<=', 'agro_flight.create_date', $where['end_date']]);
        }

        $or_conditon = ['or'];
        if (isset($where['team_name'])) {
            $or_conditon[] = ['like', 'agro_flight.team_name', $where['team_name']];
        }
        if (isset($where['nickname'])) {
            $or_conditon[] = ['like', 'agro_flight.nickname', $where['nickname']];
        }
        if (isset($where['flyer_name'])) {
            $or_conditon[] = ['like', 'agro_flight.flyer_name', $where['flyer_name']];
        }
        if (isset($where['location'])) {
            $or_conditon[] = ['like', 'agro_flight.location', $where['location']];
        }
        if (count($or_conditon) > 1) {
            $query->andWhere($or_conditon);
        }

        if(isset($where['order'])) {
            if($where['updown'] && $where['updown'] == 1) {
                $query->orderBy([$where['order'] => SORT_DESC]);
            } else {
                $query->orderBy([$where['order'] => SORT_ASC]);
            }
        } else {
            $query->orderBy(['agro_flight.id' => SORT_DESC]);
        }

        if ($limit > 0) {
        $query->offset($start)->limit($limit);
        }
        return $query->all();
	}
	public static function getActiveWhereCount($where = [])
	{
		
		  $query = new  \yii\db\Query();
		  $query->select('count(agro_flight.id) as flight_count,sum(agro_flight.work_area) as sum_area,sum(agro_flight.work_time) as sum_time')->from('agro_flight')->leftJoin('agro_active_info', 'agro_flight.product_sn = agro_active_info.hardware_id');
		  if (isset($where['uid'])) {		  	
		  	 $query->andWhere(['=', 'agro_flight.uid', $where['uid']]);
		  }
		  if (isset($where['upper_uid'])) {		  	
		  	 $query->andWhere(['=', 'agro_flight.upper_uid', $where['upper_uid']]);
		  	 $query->andWhere(['=', 'agro_active_info.uid', $where['upper_uid']]);
		  }
		  if (isset($where['team_id'])) {			  
		   	 $query->andWhere(['in', 'agro_flight.team_id', $where['team_id']]);		  	
		  }
		  if (isset($where['start_date'])) {		  	
		  	 $query->andWhere(['>=', 'agro_flight.create_date', $where['start_date']]);
		  }
		  if (isset($where['end_date'])) {		  	
		  	 $query->andWhere(['<=', 'agro_flight.create_date', $where['end_date']]);
		  }
		  if (isset($where['product_sn'])) {		  	
		  	 $query->andWhere(['in', 'agro_flight.product_sn', $where['product_sn']]);
		  }
		  if (isset($where['deleted'])) {		  	
		  	 $query->andWhere(['=', 'agro_flight.deleted', $where['deleted']]);
		  	 $query->andWhere(['=', 'agro_active_info.deleted', $where['deleted']]);
		  }	
		 return	$query->all();
	}

	public static function getRecordByID($model, $arr_id)
	{
		$query = new  \yii\db\Query();
		$query->select("*")->from('agro_flight');
		if (isset($model['deleted'])) {		  	
		  	$query->andWhere(['=', 'agro_flight.deleted', $model['deleted']]);		  	
		}
		if (isset($arr_id)) {		  	
		  	$query->andWhere(['in', 'agro_flight.id', $arr_id]);		  	
		}
		return	$query->all();
	}
	public static function getFlightSubQuery($where) {
		$subQuery = (new \yii\db\Query())->select('*')->from('agro_flight');
/*		$condition = array();
		$condition[] = 'and';
		$condition[] = 'deleted=0';

		$subcondition = array();
		$subcondition[] = 'or';
		$subcondition[] = 'agro_flight.upper_uid='.$where['upper_uid'];
		$subcondition[] = 'agro_flight.uid='.$where['uid'];

		if (isset($where['team_id'])) {
			$team_condition = array();
			$team_condition[] = 'in';
			$team_condition[] = 'agro_flight.team_id';
			$team_condition[] = $where['team_id'];
		   	
		   	$subcondition[] = $team_condition;
		}

		$condition[] = $subcondition;
		$subQuery->where($condition);*/
		
		if (isset($where['uid'])) {		  	
		  	$subQuery->orWhere(['=', 'uid', $where['uid']]); //作为飞手的
		}
		if (isset($where['upper_uid'])) {		  	
		  	$subQuery->orWhere(['=', 'upper_uid', $where['upper_uid']]);//作为老板的	  	 
		}
		if (isset($where['team_id'])) {			  
		   	$subQuery->orWhere(['in', 'team_id', $where['team_id']]);	//作为队长的	  	
		}
		return $subQuery;
	}
	public static function getWhereFlightCount($where = [], $subQuery, $fields = 'count(id) as flight_count')
	{		
		  $query = new  \yii\db\Query();
		  $query->select($fields)->from(['agro_flight'=>$subQuery]);
		  if (isset($where['deleted'])) {		  	
		  	 $query->andWhere(['=', 'deleted', $where['deleted']]);
		  }	
		  if (isset($where['start_date'])) {		  	
		  	 $query->andWhere(['>=', 'create_date', $where['start_date']]);
		  }
		  if (isset($where['end_date'])) {		  	
		  	 $query->andWhere(['<=', 'create_date', $where['end_date']]);
		  }

		  $or_condition = ['or'];
		  if (isset($where['nickname'])) {
		      $or_condition[] = ['like', 'nickname', $where['nickname']];
		  }
		  if (isset($where['flyer_name'])) {
		      $or_condition[] = ['like', 'flyer_name', $where['flyer_name']];
		  }
		  if (isset($where['location'])) {
		      $or_condition[] = ['like', 'location', $where['location']];
		  }
		  if (isset($where['team_name'])) {
		      $or_condition[] = ['like', 'team_name', $where['team_name']];
		  }

		  if (count($or_condition) > 1) {
		      $query->andWhere($or_condition);
          }

		  return $query->all();
	}
}
