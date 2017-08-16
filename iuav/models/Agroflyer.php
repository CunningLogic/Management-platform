<?php

namespace app\models;

use yii\db\ActiveRecord;
use Yii;

class Agroflyer extends ActiveRecord
{
			
	public static function tableName()
  {
        return 'agro_flyer';
  }

  public static function add($model)
	{
		$release = new Agroflyer;
		$now_time = date('Y-m-d H:i:s',time());
		$release->created_at = $now_time;
		//$release->status     = Area::PENDING;
		$release->team_id = strip_tags($model['team_id']);
		$release->upper_uid = strip_tags($model['upper_uid']);
		$release->account = strip_tags($model['account']);
		$release->uid = strip_tags($model['uid']);
		if (isset($model['avatar'])) {
		   $release->avatar = strip_tags($model['avatar']);
	    }
	    if (isset($model['nickname'])) {
		   $release->nickname = strip_tags($model['nickname']);
	    }
	   	if (isset($model['realname'])) {
		   $release->realname = strip_tags($model['realname']);
	    }
		if (isset($model['job_level'])) {
		   $release->job_level = strip_tags($model['job_level']);
	    }
	    if (isset($model['phone'])) {
		    $release->phone = strip_tags($model['phone']);
        }
		$release->ip = $model['ip'];
	//	$release->ext1 = $model['ext1'];
		//$release->ext2 = $model['ext2'];
		$release->updated_at = $now_time;		
		$release->save();
		return $release->id;
	}

    public static function add2($model)
    {
        $release = new Agroflyer;
        $now_time = date('Y-m-d H:i:s',time());
        $release->created_at = $now_time;
        //$release->status     = Area::PENDING;
        $release->team_id = strip_tags($model['team_id']);
        $release->upper_uid = strip_tags($model['upper_uid']);
        $release->account = strip_tags($model['account']);
        $release->uid = strip_tags($model['uid']);
        if (isset($model['avatar'])) {
            $release->avatar = strip_tags($model['avatar']);
        }
        if (isset($model['nickname'])) {
            $release->nickname = strip_tags($model['nickname']);
        }
        if (isset($model['realname'])) {
            $release->realname = strip_tags($model['realname']);
        }
        if (isset($model['job_level'])) {
            $release->job_level = strip_tags($model['job_level']);
        }
        if (isset($model['phone'])) {
            $release->phone = strip_tags($model['phone']);
        }
        $release->ip = $model['ip'];
        //	$release->ext1 = $model['ext1'];
        //$release->ext2 = $model['ext2'];
        $release->updated_at = $now_time;
        $release->save();
        return $release;
    }
   
   public static function updateInfo($model)
   {
        $release = Agroflyer::findOne(['id' => $model['id']]);
        if (isset($model['realname'])) {
		   $release->realname = strip_tags($model['realname']);
	    }
	    if (isset($model['idcard'])) {
		   $release->idcard = strip_tags($model['idcard']);
	    }
	    if (isset($model['phone'])) {
		   $release->phone = strip_tags($model['phone']);
	    }
	    if (isset($model['job_level'])) {
		   $release->job_level = strip_tags($model['job_level']);
	    }
	    if (isset($model['address'])) {
		   $release->address = strip_tags($model['address']);
	    }
	   	if (isset($model['company_name'])) {
		   $release->company_name = strip_tags($model['company_name']);
	    }
	    if (isset($model['ip'])) {
		   $release->ip = strip_tags($model['ip']);
	    }
	    if (isset($model['last_read_id'])) {
            $release->last_read_id = strip_tags($model['last_read_id']);
        }
        $release->updated_at = date('Y-m-d H:i:s');  
        $release->save();       
        return $release->id;
    } 

   public static function deletedFlyer($model)
   {
        $release = Agroflyer::findOne(['id' => $model['id']]);
        $release->deleted = strip_tags($model['deleted']);	
        $release->updated_at = date('Y-m-d H:i:s');  
        $release->save();       
        return $release->id;
   }
   public static function changeStatus($model)
   {      
        return  Agroflyer::updateAll(array('deleted' => strip_tags($model['deleted']) ), ['team_id'=> strip_tags($model['team_id']) ] );;
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
				->from(Agroflyer::tableName())
				->where($where)
				->orderBy($orderby_sort)
				->offset($start)
				->limit($limit)
				->all();
		} else {
			return (new \yii\db\Query())
				->select($fields)
				->from(Agroflyer::tableName())
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
				->from(Agroflyer::tableName())
				->where($str, $params)
				->orderBy($orderby_sort)
				->offset($start)
				->limit($limit)
				->all();
		} else {
			return (new \yii\db\Query())
				->select($fields)
				->from(Agroflyer::tableName())
				->where($str, $params)
				->orderBy($orderby_sort)
				->all();
		}
	}

	public static function getTeamWhere($where,$fields,$start = 0, $limit = 0)
	{	

		  $query = new  \yii\db\Query();
		  $query->select($fields)->from('agro_flyer')->leftJoin('agro_team', 'agro_flyer.team_id = agro_team.id');
		  if (isset($where['agro_policies_id']) && $where['agro_policies_id'] == 'isnull') {
		  	
		  	 $query->andWhere(['is', 'agro_policies.id', null]);
		  }
		  if (isset($where['uid'])) {		  	
		  	 $query->andWhere(['=', 'agro_flyer.uid', $where['uid']]);
		  }
		  if (isset($where['id'])) {		  	
		  	 $query->andWhere(['=', 'agro_flyer.id', $where['id']]);
		  }
		  if (isset($where['team_id'])) {		  	
		  	 $query->andWhere(['=', 'agro_flyer.team_id', $where['team_id']]);
		  }
		  if (isset($where['deleted'])) {		  	
		  	 $query->andWhere(['=', 'agro_flyer.deleted', $where['deleted']]);
		  	 $query->andWhere(['=', 'agro_team.deleted', $where['deleted']]);
		  }		

		  $query->orderBy(['agro_flyer.id' => SORT_DESC]);	
		  if ($limit > 0) {
		  	 	$query->offset($start)->limit($limit);
		  }	 
		  return $query->all();
	}

    public static function getTeamWhere2($where,$fields)
    {
        $query = new  \yii\db\Query();
        $query->select($fields)->from('agro_flyer');
        if (isset($where['team_id'])) {
            $query->andWhere(['=', 'agro_flyer.team_id', $where['team_id']]);
        }
        if (isset($where['deleted'])) {
            $query->andWhere(['=', 'agro_flyer.deleted', $where['deleted']]);
        }

        $query->orderBy(['agro_flyer.id' => SORT_DESC]);
        return $query->all();
    }

    public static function getAllTeamId($uid)
    {
        $query = new \yii\db\Query();
        $query->select('team_id')->from('agro_flyer');
        $query->where([
            'and',
            'deleted=0',
            [
                'or',
                'agro_flyer.upper_uid='.$uid,
                'agro_flyer.uid='.$uid
            ]
        ]);

        $query->groupBy('agro_flyer.team_id');

        return $query->all();
    }

	public static function getAllUserList()
	{	
		  $query = new  \yii\db\Query();
		  $fields = 'account';
		  return $query->select($fields)->distinct()->from('agro_flyer')->all();		  
	}

	public static function getUidData($where,$fields='*') 
    {
    	if (isset($where['upper_uid'])) {
    		$key = __CLASS__.__FUNCTION__.md5($where['uid'].$where['upper_uid'].$fields);
    	}else{
    		$key = __CLASS__.__FUNCTION__.md5($where['uid'].$fields);
    	}        
        $data = Yii::$app->cache->get($key);
        if (empty($data)) {
            $query = new  \yii\db\Query();
		    $query->select($fields)->from(Agroflyer::tableName());  
		    $query->andWhere(['=', 'deleted', '0']);
		    if (isset($where['uid'])) {		  	
		  	   $query->andWhere(['=', 'uid', $where['uid']]);
		  	   
		    }
		    if (isset($where['upper_uid'])) {
		    	$query->andWhere(['=', 'upper_uid', $where['upper_uid']]);
		    }
            $data = $query->all();
            Yii::$app->cache->set($key, $data, 3600);
        }
        return $data;
    }
   	public static function getNameByID($where,$fields='realname') 
    {
    	if(isset($where['flyerid'])) {
    		$key = __CLASS__.__FUNCTION__.md5($where['flyerid'].$fields.'flyerid'); 
    	} else if(isset($where['id'])){
    		$key = __CLASS__.__FUNCTION__.md5($where['id'].$fields.'id'); 
    	}   
        $data = Yii::$app->cache->get($key);
        if (empty($data)) {
            $query = new  \yii\db\Query();
		    $query->select($fields)->from(Agroflyer::tableName());  
		    if (isset($where['flyerid'])) {		  	
		  	   $query->andWhere(['=', 'uid', $where['flyerid']]); 
		    }
		    if (isset($where['deleted'])) {		  	
		  	   $query->andWhere(['=', 'deleted', $where['deleted']]); 
		    }
		    if (isset($where['teamid'])) {		  	
		  	   $query->andWhere(['=', 'team_id', $where['teamid']]); 
		    }
		   	if (isset($where['bossid'])) {		  	
		  	   $query->andWhere(['=', 'upper_uid', $where['bossid']]); 
		    }
		   	if (isset($where['id'])) {		  	
		  	   $query->andWhere(['=', 'id', $where['id']]); 
		    }
            $data = $query->one();
            Yii::$app->cache->set($key, $data, 3600);
        }
        return $data['realname'];
    }
}
