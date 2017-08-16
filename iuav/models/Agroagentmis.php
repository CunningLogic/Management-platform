<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Agroagentmis extends ActiveRecord
{
			
	public static function tableName()
  {
        return 'agro_agent_mis';
  }
  public static function getAndEqualWhereCount($where = [])
	{
		
		return (new \yii\db\Query())
				->select('id')
				->from(Agroagentmis::tableName())
				->where($where)
				->count();
	}

  public static function add($model)
	{
		$release = new Agroagentmis;
		$now_time = date('Y-m-d H:i:s',time());
		$release->created_at = $now_time;
		//$release->status     = Area::PENDING;
		$release->agentname = $model['agentname'];
		$release->code = $model['code'];
	  $release->realname = $model['realname'];
		$release->idcard = $model['idcard'];
		$release->phone = $model['phone'];
		$release->email = $model['email'];
		$release->country = $model['country'];
	    $release->province = $model['province'];
	    $release->city = $model['city'];
	    $release->address = $model['address'];
	    if (isset($model['staff'])) {
	    	$release->staff = $model['staff'];
	    }
	    if (isset($model['misuid'])) {
	    	$release->misuid = $model['misuid'];
	    }
		//$release->operator = $model['operator'];
		$release->ip = $model['ip'];
	//	$release->ext1 = $model['ext1'];
		//$release->ext2 = $model['ext2'];
		$release->updated_at = $now_time;		
		$release->save();
		return $release->id;
	}
	public static function updateInfo($model)
    {
		$release = Agroagentmis::findOne(['id' => $model['id']]);
		//$release->status     = Area::PENDING;
		if (isset($model['agentname'])) {
			$release->agentname = $model['agentname'];
		}
		if (isset($model['code'])) {
			$release->code = $model['code'];
		}	
		if (isset($model['realname'])) {
			$release->realname = $model['realname'];
		}
		if (isset($model['idcard'])) {
			$release->idcard = $model['idcard'];
		}
		if (isset($model['phone'])) {
			$release->phone = $model['phone'];
		}
		if (isset($model['country'])) {
			$release->country = $model['country'];
		}
		if (isset($model['email'])) {
			$release->email = $model['email'];
		}
		if (isset($model['province'])) {
			$release->province = $model['province'];
		}	
		if (isset($model['city'])) {
			$release->city = $model['city'];
		}
		if (isset($model['address'])) {
			$release->address = $model['address'];
		}			
		if (isset($model['staff'])) {
			$release->staff = $model['staff'];
		}
		if (isset($model['misuid'])) {
			$release->misuid = $model['misuid'];
		}
		if (isset($model['ip'])) {
			$release->ip = $model['ip'];
		}
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
				->from(Agroagentmis::tableName())
				->where($where)
				->orderBy($orderby_sort)
				->offset($start)
				->limit($limit)
				->all();
		} else {
			return (new \yii\db\Query())
				->select($fields)
				->from(Agroagentmis::tableName())
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
				->from(Agroagentmis::tableName())
				->where($str, $params)
				->orderBy($orderby_sort)
				->offset($start)
				->limit($limit)
				->all();
		} else {
			return (new \yii\db\Query())
				->select($fields)
				->from(Agroagentmis::tableName())
				->where($str, $params)
				->orderBy($orderby_sort)
				->all();
		}
	}
	public static function getRealNameWithCode($code)
    {
      $luckkey = 'v1AgroagentmisgetRealNameWithCode'.md5($code);
      $luckdata = Yii::$app->cache->get($luckkey);
      if ( $luckdata ) {
            return $luckdata;
       }   
       $data = array('realname' => '','staff' => '');
       $release = Agroagentmis::findOne(['code' => $code]);
       if ($release) {
          $data = array('realname' => $release->realname,'staff' => $release->staff) ; 
          Yii::$app->cache->set($luckkey, $data, 3600);   
          return $data;
       }
       return $data;       
    }

	

}
