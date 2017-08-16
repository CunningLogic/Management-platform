<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Agropolicies extends ActiveRecord
{
			
	public static function tableName()
  {
        return 'agro_policies';
  }

  public static function getPolNo($apply_id,$order_id)
  {
      $luckkey = 'AgropoliciesgetPolNo'.md5($apply_id.$order_id);
      $luckdata = Yii::$app->cache->get($luckkey);
      if ( $luckdata ) {
            return $luckdata;
      }    
      $polnostr = ''; 
      $nowdate = date("YmdHis",time()); 
      $release = Agropolicies::findOne(['apply_id' => $apply_id,'order_id'=>$order_id]);
      if (empty($release) || empty($release->pol_no) || empty($release->query_flag) ) {
      	 $polnostr = '处理中';
      	 $data = array('polnostr' => $polnostr, 'pol_no' => '');
      }elseif ($nowdate > $release->exp_tm) {
      	 $polnostr = '已经过期';
      	 $data = array('polnostr' => $polnostr, 'pol_no' => $release->pol_no);
      }elseif ($release->pol_no && $release->query_flag == '1') {
      	 $polnostr = $release->pol_no;
      	 $data = array('polnostr' => $polnostr, 'pol_no' => $release->pol_no);
      }      
      //Yii::$app->cache->set($luckkey, $data, 3600);   
      return $data;
  }


  public static function add($model)
  {
		$release = new Agropolicies;
		$now_time = date('Y-m-d H:i:s',time());
		$release->created_at = $now_time;
		//$release->status     = Area::PENDING;
		$release->order_id = strip_tags($model['order_id']);
		$release->apply_id = strip_tags($model['apply_id']);
		$release->query_id = strip_tags($model['query_id']);
        $release->input_tm = strip_tags($model['input_tm']);
		$release->ip = $model['ip'];
	//	$release->ext1 = $model['ext1'];
		//$release->ext2 = $model['ext2'];
		$release->updated_at = $now_time;		
		$release->save();
		return $release->id;
	}
    public static function simpleAdd($model)
    {
        $release = new Agropolicies;
        $now_time = date('Y-m-d H:i:s',time());
        $release->created_at = $now_time;      
        $release->order_id = strip_tags($model['order_id']);
        $release->apply_id = strip_tags($model['apply_id']);
        $release->pol_no = strip_tags($model['pol_no']);
        $release->exp_tm = strip_tags($model['exp_tm']);
        $release->query_flag = strip_tags($model['query_flag']);   
        $release->updated_at = $now_time;       
        $release->save();
        return $release->id;
    }
	 public static function updateInfo($model)
   {
        $release = Agropolicies::findOne(['id' => $model['id']]);
				//$release->order_id = strip_tags($model['order_id']);
				//$release->apply_id = strip_tags($model['apply_id']);
				//$release->query_id = strip_tags($model['query_id']);
				$release->pol_no = strip_tags($model['pol_no']);
				if (isset($model['eff_tm'])) {
				    $release->eff_tm = strip_tags($model['eff_tm']);		  
				}	
				$release->exp_tm = strip_tags($model['exp_tm']);
				//$release->input_tm = strip_tags($model['input_tm']);
        $release->amount = strip_tags($model['amount']);
				$release->premium = strip_tags($model['premium']);
				$release->query_flag = strip_tags($model['query_flag']);
				$release->query_desc = strip_tags($model['query_desc']);	
				$release->ip = $model['ip'];
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
				->from(Agropolicies::tableName())
				->where($where)
				->orderBy($orderby_sort)
				->offset($start)
				->limit($limit)
				->all();
		} else {
			return (new \yii\db\Query())
				->select($fields)
				->from(Agropolicies::tableName())
				->where($where)
				->orderBy($orderby_sort)
				->all();
		}
	}
  public static function getAndEqualWhereCount($where = [])
  {
    
    return (new \yii\db\Query())
        ->select('id')
        ->from(Agropolicies::tableName())
        ->where($where)
        ->count();
  }
   public static function updateIsNotice($model)
    {
        $release = Agropolicies::findOne(['id' => $model['id']]);
        $release->is_notice = $model['is_notice'];
        $release->updated_at = date('Y-m-d H:i:s');  
        $release->save();       
        return $release->id;
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
				->from(Agropolicies::tableName())
				->where($str, $params)
				->orderBy($orderby_sort)
				->offset($start)
				->limit($limit)
				->all();
		} else {
			return (new \yii\db\Query())
				->select($fields)
				->from(Agropolicies::tableName())
				->where($str, $params)
				->orderBy($orderby_sort)
				->all();
		}
	}

    public static function getSearchWhere($where,$fields,$start = 0, $limit = 0)
    { 
          $query = new  \yii\db\Query();
          $query->select($fields)->from(Agropolicies::tableName());
          foreach ($where as $key => $value) {
              if ($key == 'begin') {
                  $query->andWhere(['>=', 'updated_at', $value." 00:00:00"]);
              }elseif ($key == 'end') {
                  $query->andWhere(['<=', 'updated_at', $value." 23:59:59"]);
              }else{
                  $query->andWhere(['=', $key, $value]);
              }
          } 
          $query->orderBy(['id' => SORT_DESC]);    
          if ($limit > 0) {
                $query->offset($start)->limit($limit);
          }  
          return $query->all();
    }
    
    public static function changeStatus($model)
    {      
        return  Agropolicies::updateAll(array('mark' => $model['mark'] ), ['id'=> $model['id'] ] );;
    }

   public static function getPolNoStr($pol_no,$query_flag,$exp_tm)
   {
      
      $polnostr = ''; 
      $nowdate = date("YmdHis",time()); 
      if (empty($pol_no) || empty($query_flag) || $query_flag != '1' ) {
         $polnostr = '处理中';         
      }elseif ($nowdate > $exp_tm) {
         $polnostr = '已经过期';       
      }     
      return $polnostr;
   }
	

}
