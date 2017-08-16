<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Agroactiveinfo extends ActiveRecord
{
			
	public static function tableName()
  {
        return 'agro_active_info';
  }

  public static function add($model)
	{
		$release = new Agroactiveinfo;
		$now_time = date('Y-m-d H:i:s',time());
		$release->created_at = $now_time;
		$release->active_tm = $now_time; //激活时间
		if(isset($model['hardware_id'])) {
			$release->hardware_id = strip_tags($model['hardware_id']);
		}
		if(isset($model['order_id'])) {
			$release->order_id = strip_tags($model['order_id']);
		}
		if(isset($model['body_code'])) {
			$release->body_code = strip_tags($model['body_code']);
		}
		if(isset($model['idcard'])) {
			$release->idcard = strip_tags($model['idcard']);
		}
		if(isset($model['phone'])) {
			$release->phone = strip_tags($model['phone']);
		}
		if(isset($model['type'])) {
			$release->type = strip_tags($model['type']);
		}
		if(isset($model['ip'])) {
			$release->ip = $model['ip'];
		}
		if(isset($model['pol_no'])) {
			$release->pol_no = $model['pol_no'];
		}
		if(isset($model['deleted'])) {
			$release->deleted = $model['deleted'];
		}
		if(isset($model['is_active'])) {
			$release->is_active = $model['is_active'];
		}
		$release->updated_at = $now_time;		
		$release->save();
		return $release->id;
	}
	public static function simpleAdd($model)
	{
		$release = new Agroactiveinfo;
		$now_time = date('Y-m-d H:i:s',time());
		$release->created_at = $now_time;
	    if (isset($model['uid'])) {			
		   $release->uid = strip_tags($model['uid']);
		}
		$release->order_id = strip_tags($model['order_id']);
		$release->apply_id = strip_tags($model['apply_id']);	
		$release->body_code = strip_tags($model['body_code']);
		$release->hardware_id = strip_tags($model['hardware_id']);
		$release->type = strip_tags($model['type']);		
		$release->updated_at = $now_time;		
		$release->save();
		return $release->id;
	}
	

	public static function getAndEqualWhereCount($where = [])
	{
		
		return (new \yii\db\Query())
				->select('id')
				->from(Agroactiveinfo::tableName())
				->where($where)
				->count();
	}

	public static function getWhereTodayCount($where = [],$start,$end)
	{
		
		return (new \yii\db\Query())
				->select('id')
				->from(Agroactiveinfo::tableName())
				->where($where)
				->andwhere(['>=', 'created_at', $start.' 00:00:00'])
				->andwhere(['<=', 'created_at', $end.' 23:59:59'])
				->count();
	}

	public static function getWhereGroupCount($where = [],$start,$end)
	{
		
		return (new \yii\db\Query())
				->select('count(*) as total_mon,upper_agent_id')
				->from(Agroactiveinfo::tableName())
				->where($where)
				->andwhere(['>=', 'created_at', $start.' 00:00:00'])
				->andwhere(['<=', 'created_at', $end.' 23:59:59'])
				->groupBy('upper_agent_id')
				->orderBy(['total_mon' => SORT_DESC])
				->all();
	}


   public static function updateInfo($model)
   {
        $release = Agroactiveinfo::findOne(['id' => $model['id']]);
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

    public static function updateDeleted($model)
    {
        $release = Agroactiveinfo::findOne(['id' => $model['id']]);
		$release->deleted = $model['deleted'];
		$release->updated_at = date('Y-m-d H:i:s');  
		$release->save();       
        return $release->id;
    }
    public static function updateIsNotice($model)
    {
        $release = Agroactiveinfo::findOne(['id' => $model['id']]);
		$release->is_notice = $model['is_notice'];
		$release->updated_at = date('Y-m-d H:i:s');  
		$release->save();       
        return $release->id;
    }  
    public static function updatePhoneNickname($model)
    {
        $release = Agroactiveinfo::findOne(['body_code'=> $model['body_code']]);
        if(isset($model['uid'])) {
        	$release->uid = $model['uid'];
        }
        if(isset($model['email'])) {
        	$release->account = $model['email'];
        }
        if(isset($model['phone'])) {
        	$release->phone = $model['phone'];
        }
		if(isset($model['nickname'])) {
        	$release->nickname = $model['nickname'];
        }
       	if(isset($model['hardware_id'])) {
        	$release->hardware_id = $model['hardware_id'];
        }
        if(isset($model['is_active'])) {
        	$release->is_active = $model['is_active'];
        }
        if(isset($model['active_location'])) {
        	$release->active_location = $model['active_location'];
        }
        if(isset($model['team_id'])) {
            $release->team_id = $model['team_id'];
        }

		$release->updated_at = date('Y-m-d H:i:s');  
		$release->save();       
        return $release->id;
    } 
	public static function updateTimeLocked($model)
    {
        $release = Agroactiveinfo::findOne(['id' => $model['id']]);
		
		$release->timelocked = strip_tags($model['timelocked']);
		$release->timelocked_notice = strip_tags($model['timelocked_notice']);	

		if (isset($model['lock_begin'])) {			
		   $release->lock_begin = strip_tags($model['lock_begin']);
		}

		if (isset($model['lock_end'])) {			
		   $release->lock_end = strip_tags($model['lock_end']);
		}

		$release->updated_at = date('Y-m-d H:i:s');  
		$release->save();       
		return $release->id;
    }  
    public static function updateNicknameLocked($model, &$update_set = [])
    {
        $release = Agroactiveinfo::findOne(['id' => $model['id'],'uid' => $model['uid']]);
        if (isset($model['locked'])) {			
		   $release->locked = strip_tags($model['locked']);
		}
		if (isset($model['nickname'])) {
            $update_set['nickname'] = [$release->nickname, $model['nickname']];
		    $release->nickname = strip_tags($model['nickname']);
		}
		if (isset($model['locked_notice'])) {			
		   $release->locked_notice = strip_tags($model['locked_notice']);
		}
		if (isset($model['team_id'])) {
            $update_set['team_id'] = [$release->team_id, $model['team_id']];
		    $release->team_id = strip_tags($model['team_id']);
		}

		$release->updated_at = date('Y-m-d H:i:s');  
		$release->save();       
		return $release->id;
    }       


	public static function getAndEqualWhere($where = [], $start = 0, $limit = 0, $orderby = 'id', $sort = 1, $fields = '*')
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
				->from(Agroactiveinfo::tableName())
				->where($where)
				->orderBy($orderby_sort)
				->offset($start)
				->limit($limit)
				->all();
		} else {
			return (new \yii\db\Query())
				->select($fields)
				->from(Agroactiveinfo::tableName())
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
				->from(Agroactiveinfo::tableName())
				->where($str, $params)
				->orderBy($orderby_sort)
				->offset($start)
				->limit($limit)
				->all();
		} else {
			return (new \yii\db\Query())
				->select($fields)
				->from(Agroactiveinfo::tableName())
				->where($str, $params)
				->orderBy($orderby_sort)
				->all();
		}
	}	

	public static function getPoliciesWhereByflyer($where,$fields,$start = 0, $limit = 0)
	{	
		  $query = new  \yii\db\Query();
		  $query->select($fields)->from('agro_active_info')->leftJoin('agro_active_flyer', 'agro_active_info.id = agro_active_flyer.active_id');
		  if (isset($where['uid'])) {		  	
		  	 $query->andWhere(['in', 'agro_active_info.uid', $where['uid']]);
		  }
		  if (isset($where['deleted'])) {
		   	 $query->andWhere(['=', 'agro_active_info.deleted', $where['deleted']]);
		  }
		  if (isset($where['team_id'])) {
		   	 $query->andWhere(['in', 'agro_active_info.team_id', $where['team_id']]);
		  }
		  if (isset($where['flyer_uid'])) {
		   	 $query->andWhere(['=', 'agro_active_flyer.flyer_uid', $where['flyer_uid']]);
		  }
		  $query->groupBy('agro_active_info.id');
		  $query->orderBy(['agro_active_info.id' => SORT_DESC]);	
		  if ($limit > 0) {
		  	 	$query->offset($start)->limit($limit);
		  }	 
		  return $query->all();
	}
	public static function getPoliciesWhereByflyerOrderByIsOnline($where,$fields,$start = 0, $limit = 0)
	{	
		  $query = new  \yii\db\Query();
		  $query->select($fields)->from('agro_active_info')->leftJoin('agro_active_flyer', 'agro_active_info.id = agro_active_flyer.active_id');
		  if (isset($where['uid'])) {		  	
		  	 $query->andWhere(['in', 'agro_active_info.uid', $where['uid']]);
		  }
		  if (isset($where['deleted'])) {
		   	 $query->andWhere(['=', 'agro_active_info.deleted', $where['deleted']]);
		  }
		  if (isset($where['team_id'])) {
		   	 $query->andWhere(['in', 'agro_active_info.team_id', $where['team_id']]);
		  }
		  if (isset($where['flyer_uid'])) {
		   	 $query->andWhere(['=', 'agro_active_flyer.flyer_uid', $where['flyer_uid']]);
		  }
		  $query->groupBy('agro_active_info.id');
		  $query->orderBy(['agro_active_info.is_online' => SORT_DESC]);	
		  if ($limit > 0) {
		  	 	$query->offset($start)->limit($limit);
		  }	 
		  return $query->all();
	}
	public static function getPoliciesWhere($where,$fields,$start = 0, $limit = 0)
	{	

		  $query = new  \yii\db\Query();
		  $query->select($fields)->from('agro_active_info');
		  if (isset($where['uid'])) {		  	
		  	 $query->andWhere(['=', 'agro_active_info.uid', $where['uid']]);
		  }
		  if (isset($where['deleted'])) {
		   	 $query->andWhere(['=', 'agro_active_info.deleted', $where['deleted']]);
		  }
		  if (isset($where['team_id'])) {
		   	 $query->andWhere(['=', 'agro_active_info.team_id', $where['team_id']]);
		  }
		  $query->orderBy(['agro_active_info.id' => SORT_DESC]);
		  if ($limit > 0) {
		  	 	$query->offset($start)->limit($limit);
		  }	 
		  return $query->all();
	}
    public static function getPoliciesWhereOrderByIsOnline($where,$fields,$start = 0, $limit = 0)
    {
        $query = new  \yii\db\Query();
        $query->select($fields)->from('agro_active_info');
        if (isset($where['uid'])) {
            $query->andWhere(['=', 'agro_active_info.uid', $where['uid']]);
        }
        if (isset($where['deleted'])) {
            $query->andWhere(['=', 'agro_active_info.deleted', $where['deleted']]);
        }
        if (isset($where['team_id'])) {
            $query->andWhere(['in', 'agro_active_info.team_id', $where['team_id']]);
        }
        $query->orderBy(['agro_active_info.is_online' => SORT_DESC]);
        if ($limit > 0) {
            $query->offset($start)->limit($limit);
        }
        return $query->all();
    }
	public static function getSNData($where,$fields='*',$start = 0, $limit = 0) 
    {
        $key = __CLASS__.__FUNCTION__.md5($where['hardware_id'].$fields.$start.$limit);
        $data = Yii::$app->cache->get($key);
        if (empty($data)) {
            $query = new  \yii\db\Query();
		    $query->select($fields)->from(Agroactiveinfo::tableName()); 
		    $query->andWhere(['=', 'deleted', '0']); 
		    if (isset($where['hardware_id'])) {		  	
		  	   $query->andWhere(['=', 'hardware_id', $where['hardware_id']]);
		    }
		    if ($limit > 0) {
		  	 	$query->offset($start)->limit($limit);
		    }	 
            $data = $query->all();
            Yii::$app->cache->set($key, $data, 3600);
        }
        return $data;
    }
    public static function changeTeamid($model)
    {      
      return Agroactiveinfo::updateAll(array('team_id' => 0 ), ['team_id'=> strip_tags($model['team_id']),'uid'=> strip_tags($model['uid']) ] );
    }   

	public static function getNameByID($where) 
    {
        $key = __CLASS__.__FUNCTION__.md5($where['product_sn']);
        $data = Yii::$app->cache->get($key);
        if (empty($data)) {
            $query = new  \yii\db\Query();
		    $query->select("nickname")->from(Agroactiveinfo::tableName());  
		    if (isset($where['product_sn'])) {		  	
		  	   $query->andWhere(['=', 'hardware_id', $where['product_sn']]);
		    }
		   	if (isset($where['deleted'])) {		  	
		  	   $query->andWhere(['=', 'deleted', $where['deleted']]);
		    }

            $data = $query->one();
            Yii::$app->cache->set($key, $data, 3600);
        }
        return $data['nickname'];
    }

    public static function getFirstActiveRecord($uid)
    {
        return (new \yii\db\Query())
            ->select('*')
            ->from(Agroactiveinfo::tableName())
            ->where(['=', 'uid', $uid])
            ->orderBy('active_tm')
            ->one();
    }
}
