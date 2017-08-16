<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Agroevaluate extends ActiveRecord
{
			
	public static function tableName()
  {
        return 'agro_evaluate';
  }

   public static function getCaseNo($caseno)
  {
      $luckkey = 'AgroevaluategetCaseNo'.md5($caseno);
      $luckdata = Yii::$app->cache->get($luckkey);
      if ( $luckdata ) {
            return $luckdata;
       }   
       $data = array();
       $release = Agroevaluate::findOne(['caseno' => $caseno]);
       if ($release) {
          $data = array('totality' => $release->totality,'speed' => $release->speed,'attitude' => $release->attitude,'quality' => $release->quality,'message' => $release->message);
          Yii::$app->cache->set($luckkey, $data, 60);   
         return $data;
       }
       return $data;       
  }

  public static function add($model)
	{
		$release = new Agroevaluate;
		$now_time = date('Y-m-d H:i:s',time());
		$release->created_at = $now_time;
		//$release->status     = Area::PENDING;
		$release->account = strip_tags($model['account']);
		$release->user_id = strip_tags($model['user_id']);
		$release->register_phone = strip_tags($model['register_phone']);
    $release->totality = strip_tags($model['totality']);
    $release->speed = strip_tags($model['speed']);
    $release->quality = strip_tags($model['quality']);
    $release->attitude = strip_tags($model['attitude']);
    $release->message = strip_tags($model['message']);
    $release->caseno = strip_tags($model['caseno']);   
		$release->ip = $model['ip'];
	//	$release->ext1 = $model['ext1'];
		//$release->ext2 = $model['ext2'];
		$release->updated_at = $now_time;		
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
				->from(Agroevaluate::tableName())
				->where($where)
				->orderBy($orderby_sort)
				->offset($start)
				->limit($limit)
				->all();
		} else {
			return (new \yii\db\Query())
				->select($fields)
				->from(Agroevaluate::tableName())
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
				->from(Agroevaluate::tableName())
				->where($str, $params)
				->orderBy($orderby_sort)
				->offset($start)
				->limit($limit)
				->all();
		} else {
			return (new \yii\db\Query())
				->select($fields)
				->from(Agroevaluate::tableName())
				->where($str, $params)
				->orderBy($orderby_sort)
				->all();
		}
	}	
	

}
