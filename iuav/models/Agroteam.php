<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii;

class Agroteam extends ActiveRecord
{
			
	public static function tableName()
  {
        return 'agro_team';
  }

  public static function add($model)
	{
		$release = new Agroteam;
		$now_time = date('Y-m-d H:i:s',time());
		$release->created_at = $now_time;
		$release->uid = strip_tags($model['uid']);
		$release->name = strip_tags($model['name']);
		if (isset($model['captain'])) {
			$release->captain = strip_tags($model['captain']);
		}			
		if (isset($model['avatar'])) {
			$release->avatar = strip_tags($model['avatar']);
		}	
		if (isset($model['ip'])) {
			$release->ip = strip_tags($model['ip']);
		}
		if (isset($model['showed'])) {
			$release->showed = strip_tags($model['showed']);
		}
			
		$release->upper_teamid = 0;
		$release->updated_at = $now_time;
		$release->save();
		return $release;
	}
   
   public static function updateInfo($model)
   {
        $release = Agroteam::findOne(['id' => $model['id']]);
      
		if (isset($model['name'])) {			
		   $release->name = strip_tags($model['name']);
		}
		if (isset($model['deleted'])) {			
		   $release->deleted = strip_tags($model['deleted']);
		}
		if (isset($model['app_login_limit'])) {
		    $release->app_login_limit = strip_tags($model['app_login_limit']);
        }
        if (isset($model['captain'])) {
		    $release->captain = strip_tags($model['captain']);
        }
        if (isset($model['ip'])) {
		    $release->ip = strip_tags($model['ip']);
        }
        if (isset($model['app_login_limit'])) {
		    $release->app_login_limit = strip_tags($model['app_login_limit']);
        }
        $release->updated_at = date('Y-m-d H:i:s');  
        $release->save();       
        return $release->id;
    } 

   public static function updatePoliciesNoInfo($model)
   {
        $release = Agroteam::findOne(['id' => $model['id']]);
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
				->from(Agroteam::tableName())
				->where($where)
				->orderBy($orderby_sort)
				->offset($start)
				->limit($limit)
				->all();
		} else {
			return (new \yii\db\Query())
				->select($fields)
				->from(Agroteam::tableName())
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
				->from(Agroteam::tableName())
				->where($str, $params)
				->orderBy($orderby_sort)
				->offset($start)
				->limit($limit)
				->all();
		} else {
			return (new \yii\db\Query())
				->select($fields)
				->from(Agroteam::tableName())
				->where($str, $params)
				->orderBy($orderby_sort)
				->all();
		}
	}

	public static function getFlyerWhere($where,$fields,$start = 0, $limit = 0)
	{	

		  $query = new  \yii\db\Query();
		  $query->select($fields)->from('agro_team')->leftJoin('agro_flyer', 'agro_flyer.team_id = agro_team.id');
		  if (isset($where['uid'])) {		  	
		  	 $query->andWhere(['=', 'agro_team.uid', $where['uid']]);
		  }
		  if (isset($where['deleted'])) {		  	
		  	 //$query->andWhere(['=', 'agro_flyer.deleted', $where['deleted']]);
		  	 $query->andWhere(['=', 'agro_team.deleted', $where['deleted']]);
		  }
		  if (isset($where['showed'])) {		  	
		  	 //$query->andWhere(['=', 'agro_flyer.deleted', $where['deleted']]);
		  	 $query->andWhere(['=', 'agro_team.showed', $where['showed']]);
		  }
		  if (isset($where['team_id'])) {		  	
		  	 //$query->andWhere(['=', 'agro_flyer.deleted', $where['deleted']]);
		  	 $query->andWhere(['=', 'agro_team.id', $where['team_id']]);
		  }
		  if (isset($where['user_id'])) {		  	
		  	 //$query->andWhere(['=', 'agro_flyer.deleted', $where['deleted']]);
		  	 $query->andWhere(['=', 'agro_flyer.uid', $where['user_id']]);
		  }

		  $query->orderBy(['agro_team.id' => SORT_DESC]);	
		  if ($limit > 0) {
		  	 	$query->offset($start)->limit($limit);
		  }	 
		  return $query->all();
	}

    public static function getMultiFlyerWhere($where,$fields)
    {
        $query = new \yii\db\Query();
        $query->select($fields)->from('agro_team')->innerJoin('agro_flyer', 'agro_flyer.team_id = agro_team.id');
        if (isset($where['uid'])) {
            $query->andWhere(['=', 'agro_team.uid', $where['uid']]);
        }
        if (isset($where['deleted'])) {
            $query->andWhere(['=', 'agro_team.deleted', $where['deleted']]);
        }
        if (isset($where['showed'])) {
            $query->andWhere(['=', 'agro_team.showed', $where['showed']]);
        }
        if (isset($where['team_id'])) {
            if (is_array($where['team_id'])) {
                $query->andWhere(['in', 'agro_team.id', $where['team_id']]);
            } else {
                $query->andWhere(['=', 'agro_team.id', $where['team_id']]);
            }
        }
        if (isset($where['user_id'])) {
            $query->andWhere(['=', 'agro_flyer.uid', $where['user_id']]);
        }

        $query->orderBy(['agro_team.id' => SORT_DESC]);
        return $query->all();
    }
	
	public static function getAndEqualWhereCount($where = [])
	{
		
		return (new \yii\db\Query())
				->select('id')
				->from(Agroteam::tableName())
				->where($where)
				->count();
	}
	
	public static function getAllUserList()
	{	
		  $query = new  \yii\db\Query();
		  $fields = 'account';
		  return $query->select($fields)->distinct()->from('agro_team')->all();		  
	}

	public static function getIdData($where,$fields='*') 
    {
        $query = new  \yii\db\Query();
        $query->select($fields)->from(Agroteam::tableName());
        if (isset($where['id'])) {
           $query->andWhere(['=', 'id', $where['id']]);
           $query->andWhere(['=', 'deleted', '0']);
        }
        $data = $query->all();
        return $data;
    }

	public static function getNameByID($where,$fields='name') 
    {
        $key = __CLASS__.__FUNCTION__.md5($where['teamid'].$fields);
        $data = Yii::$app->cache->get($key);
        if (empty($data)) {
            $query = new  \yii\db\Query();
		    $query->select($fields)->from(Agroteam::tableName());  
		    if (isset($where['teamid'])) {		  	
		  	   $query->andWhere(['=', 'id', $where['teamid']]);
		    }
		   	if (isset($where['bossid'])) {		  	
		  	   $query->andWhere(['=', 'uid', $where['bossid']]);
		    }
            $data = $query->one();
            Yii::$app->cache->set($key, $data, 3600);
        }
        return $data['name'];
    }
    public static function editBossName($model) {
        $release = Agroteam::findOne(['uid' => $model['boss_id'], 'upper_teamid' => $model['upper_teamid']]);
		if (isset($model['bossname'])) {			
		   $release->name = strip_tags($model['bossname']);
		}

        $release->updated_at = date('Y-m-d H:i:s');  
        $release->save();       
        return $release->id;
    } 
	public static function getNameByUpperID($model)
    {
        $release = Agroteam::findOne(['uid' => $model['boss_id'], 'upper_teamid' => $model['upper_teamid']]);
        if($release)
        	return $release->name;
        else
        	return null;
    }

    public static function getSelfTeamIds($uid) {
        $query = new \yii\db\Query();
        $query->select('id as team_id')->from('agro_team');
        $query->where(['and', 'deleted=0', 'uid='.$uid]);

        $query->groupBy('id');

        return $query->all();
    }
}
