<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Agronotice extends ActiveRecord
{
			
	public static function tableName()
  {
        return 'agro_notice';
  }

  public static function add($model)
	{
		$release = new Agronotice;
		$now_time = date('Y-m-d H:i:s',time());
		$release->created_at = $now_time;
		$release->type = strip_tags($model['type']);
		$release->title = strip_tags($model['title']);
		$release->content = strip_tags($model['content']);
	
		$release->operator = $model['operator'];
		$release->ip = $model['ip'];
	//	$release->ext1 = $model['ext1'];
		//$release->ext2 = $model['ext2'];
		$release->updated_at = $now_time;		
		$release->save();
		return $release->id;
	}
	public static function updateInfo($model)
  {
			$release = Agronotice::findOne(['id' => $model['id']]);
			$release->type = strip_tags($model['type']);
		  $release->title = strip_tags($model['title']);
		  $release->content = strip_tags($model['content']);
			$release->operator = $model['operator'];
			$release->ip = $model['ip'];
			$release->updated_at = date('Y-m-d H:i:s');  
			$release->save();       
			return $release->id;
  }    

  public static function updatePending($model)
  {
			$release = Agronotice::findOne(['id' => $model['id'],'status' => 'pending']);		
			$release->status = strip_tags($model['status']);
			$release->operator = $model['operator'];
			$release->ip = $model['ip'];
			$release->updated_at = date('Y-m-d H:i:s');  
			$release->save();       
			return $release->id;
  }    


  public static function get($start, $limit, $where = [])
  {
        return (new \yii\db\Query())
            ->select('*')
            ->from(Agronotice::tableName())
            ->where($where)
            ->orderBy(['id' => SORT_DESC])
            ->offset($start)
            ->limit($limit)
            ->all();
  }
  public static function getAndEqualWhereCount($where = [])
	{
		
		return (new \yii\db\Query())
				->select('id')
				->from(Agronotice::tableName())
				->where($where)
				->count();
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
				->from(Agronotice::tableName())
				->where($where)
				->orderBy($orderby_sort)
				->offset($start)
				->limit($limit)
				->all();
		} else {
			return (new \yii\db\Query())
				->select($fields)
				->from(Agronotice::tableName())
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
				->from(Agronotice::tableName())
				->where($str, $params)
				->orderBy($orderby_sort)
				->offset($start)
				->limit($limit)
				->all();
		} else {
			return (new \yii\db\Query())
				->select($fields)
				->from(Agronotice::tableName())
				->where($str, $params)
				->orderBy($orderby_sort)
				->all();
		}
	}	
	

}
