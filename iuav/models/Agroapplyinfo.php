<?php

namespace app\models;

use yii\db\ActiveRecord;

class Agroapplyinfo extends ActiveRecord
{
			
	public static function tableName()
  {
        return 'agro_apply_info';
  }

  public static function add($model)
	{
		$release = new Agroapplyinfo;
		$now_time = date('Y-m-d H:i:s',time());
		$release->created_at = $now_time;
		//$release->status     = Area::PENDING;
		$release->order_id = strip_tags($model['order_id']);
		$release->agent_id = strip_tags($model['agent_id']);
		$release->upper_agent_id = strip_tags($model['upper_agent_id']);
		$release->user_type = strip_tags($model['user_type']);
		if (isset($model['company_name'])) {
			 $release->company_name = strip_tags($model['company_name']);
		}	
		if (isset($model['telephone'])) {
			 $release->telephone = strip_tags($model['telephone']);
		}
		if (isset($model['company_number'])) {			
		   $release->company_number = strip_tags($model['company_number']);
		}	
		if (isset($model['is_policies'])) {			
		   $release->is_policies = strip_tags($model['is_policies']);
		}			
		$release->realname = strip_tags($model['realname']);
		$release->idcardtype = strip_tags($model['idcardtype']);
		$release->idcard = strip_tags($model['idcard']);
		$release->phone = strip_tags($model['phone']);
	  $release->country = strip_tags($model['country']);
		$release->province = strip_tags($model['province']);
		$release->city = strip_tags($model['city']);
		$release->area = strip_tags($model['area']);
		$release->street = strip_tags($model['street']);
		$release->address = strip_tags($model['address']);
		$release->account = strip_tags($model['account']);
		if (isset($model['uid'])) {			
		   $release->uid = strip_tags($model['uid']);
		}	
		$release->is_mall = strip_tags($model['is_mall']);	
		$release->ip = $model['ip'];
	//	$release->ext1 = $model['ext1'];
		//$release->ext2 = $model['ext2'];
		$release->updated_at = $now_time;		
		$release->save();
		return $release->id;
	}
   
   public static function updateInfo($model)
   {
        $release = Agroapplyinfo::findOne(['id' => $model['id']]);
        $release->realname = strip_tags($model['realname']);		
		$release->idcard = strip_tags($model['idcard']);
		$release->phone = strip_tags($model['phone']);
		$release->country = strip_tags($model['country']);
		$release->province = strip_tags($model['province']);
		$release->city = strip_tags($model['city']);
		$release->area = strip_tags($model['area']);
		$release->street = strip_tags($model['street']);
		$release->address = strip_tags($model['address']);
		$release->account = strip_tags($model['account']);
		if (isset($model['uid'])) {			
		   $release->uid = strip_tags($model['uid']);
		}	
		$release->operator = $model['operator'];
		$release->ip = $model['ip'];
        $release->updated_at = date('Y-m-d H:i:s');  
        $release->save();       
        return $release->id;
    } 

   public static function updatePoliciesNoInfo($model)
   {
        $release = Agroapplyinfo::findOne(['id' => $model['id']]);
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
				->from(Agroapplyinfo::tableName())
				->where($where)
				->orderBy($orderby_sort)
				->offset($start)
				->limit($limit)
				->all();
		} else {
			return (new \yii\db\Query())
				->select($fields)
				->from(Agroapplyinfo::tableName())
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
				->from(Agroapplyinfo::tableName())
				->where($str, $params)
				->orderBy($orderby_sort)
				->offset($start)
				->limit($limit)
				->all();
		} else {
			return (new \yii\db\Query())
				->select($fields)
				->from(Agroapplyinfo::tableName())
				->where($str, $params)
				->orderBy($orderby_sort)
				->all();
		}
	}

	public static function getPoliciesWhere($where,$fields,$start = 0, $limit = 0)
	{	

		  $query = new  \yii\db\Query();
		  $query->select($fields)->from('agro_apply_info')->leftJoin('agro_policies', 'agro_apply_info.id = agro_policies.apply_id');
		  if (isset($where['agro_policies_id']) && $where['agro_policies_id'] == 'isnull') {
		  	
		  	 $query->andWhere(['is', 'agro_policies.id', null]);
		  }
		  if (isset($where['is_policies'])) {		  	
		  	 $query->andWhere(['=', 'agro_apply_info.is_policies', $where['is_policies']]);
		  }	

		  $query->orderBy(['agro_apply_info.id' => SORT_DESC]);	
		  if ($limit > 0) {
		  	 	$query->offset($start)->limit($limit);
		  }	 
		  return $query->all();
	}

	public static function getAllUserList()
	{	
		  $query = new  \yii\db\Query();
		  $fields = 'account';
		  return $query->select($fields)->distinct()->from('agro_apply_info')->all();		  
	}


	

}
