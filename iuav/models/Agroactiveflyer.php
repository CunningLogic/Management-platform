<?php

namespace app\models;

use yii\db\ActiveRecord;

class Agroactiveflyer extends ActiveRecord
{
			
	public static function tableName()
  {
        return 'agro_active_flyer';
  }

  public static function add($model)
	{
		$release = new Agroactiveflyer;
		$now_time = date('Y-m-d H:i:s',time());
		$release->created_at = $now_time;
		//$release->status     = Area::PENDING;
		$release->active_id = strip_tags($model['active_id']);
		$release->hardware_id = strip_tags($model['hardware_id']);
		$release->flyer_id = strip_tags($model['flyer_id']);
		$release->flyer_uid = strip_tags($model['flyer_uid']);
		if (isset($model['showed'])) {
			 $release->showed = strip_tags($model['showed']);
		}	
		if (isset($model['deleted'])) {
			 $release->deleted = strip_tags($model['deleted']);
		}		
	//	$release->ext1 = $model['ext1'];
		//$release->ext2 = $model['ext2'];
		$release->updated_at = $now_time;		
		$release->save();
		return $release->id;
	}
   
   public static function updateInfo($model)
   {
        $release = Agroactiveflyer::findOne(['id' => $model['id']]);
        $release->active_id = strip_tags($model['active_id']);
		$release->hardware_id = strip_tags($model['hardware_id']);
		$release->flyer_id = strip_tags($model['flyer_id']);
		$release->flyer_uid = strip_tags($model['flyer_uid']);
		if (isset($model['showed'])) {
			 $release->showed = strip_tags($model['showed']);
		}	
		if (isset($model['deleted'])) {
			 $release->deleted = strip_tags($model['deleted']);
		}		
        $release->updated_at = date('Y-m-d H:i:s');  
        $release->save();       
        return $release->id;
    } 

   public static function updateDeletedInfo($model)
   {
        $release = Agroactiveflyer::findOne(['id' => $model['id']]);
        if (isset($model['deleted'])) {
			 $release->deleted = strip_tags($model['deleted']);
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
				->from(Agroactiveflyer::tableName())
				->where($where)
				->orderBy($orderby_sort)
				->offset($start)
				->limit($limit)
				->all();
		} else {
			return (new \yii\db\Query())
				->select($fields)
				->from(Agroactiveflyer::tableName())
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
				->from(Agroactiveflyer::tableName())
				->where($str, $params)
				->orderBy($orderby_sort)
				->offset($start)
				->limit($limit)
				->all();
		} else {
			return (new \yii\db\Query())
				->select($fields)
				->from(Agroactiveflyer::tableName())
				->where($str, $params)
				->orderBy($orderby_sort)
				->all();
		}
	}
	public static function getFlyerWhere($where,$fields,$start = 0, $limit = 0)
	{	
		$query = new  \yii\db\Query();
		$query->select($fields)->from('agro_active_flyer')->leftJoin('agro_flyer', 'agro_active_flyer.flyer_id = agro_flyer.id');
		if (isset($where['agro_policies_id']) && $where['agro_policies_id'] == 'isnull') {		  	
			 $query->andWhere(['is', 'agro_policies.id', null]);
		}
		if (isset($where['active_id'])) {		  	
			 $query->andWhere(['=', 'agro_active_flyer.active_id', $where['active_id']]);
		}
		if (isset($where['flyer_uid'])) {		  	
			 $query->andWhere(['=', 'agro_active_flyer.flyer_uid', $where['flyer_uid']]);
		}
		if (isset($where['hardware_id'])) {		  	
			 $query->andWhere(['=', 'agro_active_flyer.hardware_id', $where['hardware_id']]);
		}
		if (isset($where['team_id'])) {		  	
			 $query->andWhere(['=', 'agro_flyer.team_id', $where['team_id']]);
		}
		if (isset($where['uid'])) {		  	
			 $query->andWhere(['=', 'agro_flyer.uid', $where['uid']]);
		}
		if (isset($where['deleted'])) {		  	
			 $query->andWhere(['=', 'agro_active_flyer.deleted', $where['deleted']]);
			 $query->andWhere(['=', 'agro_flyer.deleted', $where['deleted']]);
		}		

		$query->orderBy(['agro_flyer.id' => SORT_DESC]);	
		if ($limit > 0) {
			 	$query->offset($start)->limit($limit);
		}	 
		return $query->all();
	}
	public static function changeStatus($model)
    {      
      return Agroactiveflyer::updateAll(array('deleted' => strip_tags($model['deleted']) ), ['flyer_id'=> strip_tags($model['flyer_id']) ] );
    }    
   	public static function deleteAllFlyers($model)
    {      
      return Agroactiveflyer::updateAll(array('deleted' => strip_tags($model['deleted']) ), ['active_id'=> strip_tags($model['active_id']) ] );
    } 
    public static function getActiveInfoWhere($where, $fields = '*', $start = 0, $limit = 0)
	{
		$query = new  \yii\db\Query();
		$query->select($fields)->from('agro_active_flyer')->leftJoin('agro_active_info', 'agro_active_flyer.active_id = agro_active_info.id');
		if (isset($where['flyer_uid'])) {		  	
			 $query->andWhere(['=', 'agro_active_flyer.flyer_uid', $where['flyer_uid']]);
		}
		if (isset($where['deleted'])) {		  	
			$query->andWhere(['=', 'agro_active_flyer.deleted', $where['deleted']]);
			$query->andWhere(['=', 'agro_active_info.deleted', $where['deleted']]);
		}
		$query->orderBy(['agro_active_flyer.id' => SORT_DESC]);	
		if ($limit > 0) {
			$query->offset($start)->limit($limit);
		}	 
		return $query->all();
	}
}
