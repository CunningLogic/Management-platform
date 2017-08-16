<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Agroagent extends ActiveRecord
{
			
	public static function tableName()
  {
        return 'agro_agent';
  }
  public static function getAgentname($id)
  {
      $luckkey = 'AgroagentgetAgentname'.md5($id);
      $luckdata = Yii::$app->cache->get($luckkey);
      if ( $luckdata ) {
            return $luckdata;
       }      
       $release = Agroagent::findOne(['id' => $id]);
       if ($release) {
         Yii::$app->cache->set($luckkey, $release->agentname, 3600);   
         return $release->agentname;
       }
       return '';       
  }

  public static function getAgentNamePhone($id)
  {
      $luckkey = 'v1AgroagentgetAgentphoneNameCode'.md5($id);
      $luckdata = Yii::$app->cache->get($luckkey);
      if ( $luckdata ) {
            return $luckdata;
       }   
       $data = array('username' => '','phone' => '','agentname' => '','code' => '','oldcode' => '','is_policies' => '1');
       $release = Agroagent::findOne(['id' => $id]);
       if ($release) {
          $data = array('username' => $release->username,'phone' => $release->phone,'agentname' =>$release->agentname,'code' =>$release->code,'oldcode' =>$release->oldcode,'is_policies' =>$release->is_policies) ; 
         Yii::$app->cache->set($luckkey, $data, 3600);   
         return $data;
       }
       return $data;       
  }

  public static function getAgentNameForCode($code)
  {
      $luckkey = 'AgroagentgetAgentNameForCode'.md5($code);
      $luckdata = Yii::$app->cache->get($luckkey);
      if ( $luckdata ) {
            return $luckdata;
       }   
       $data = array('phone' => '','agentname' => '','code' => '','id'=>'');
       $release = Agroagent::findOne(['code' => $code]);
       if ($release) {
          $data = array('phone' => $release->phone,'agentname' =>$release->agentname,'code' =>$release->code,'id' =>$release->id) ; 
         Yii::$app->cache->set($luckkey, $data, 3600);   
         return $data;
       }
       return $data;       
  }

  public static function getAgentNameForUsername($username)
  {
      $luckkey = 'AgroagentgetAgentNameForusername'.md5($username);
      $luckdata = Yii::$app->cache->get($luckkey);
      if ( $luckdata ) {
            return $luckdata;
       }   
       $data = array('phone' => '','agentname' => '','code' => '','id'=>0,'upper_agent_id' => 0,'accessToken' => '');
       $release = Agroagent::findOne(['username' => $username]);
       if ($release) {
          $data = array('accessToken' => $release->accessToken,'upper_agent_id' => $release->upper_agent_id,'phone' => $release->phone,'agentname' =>$release->agentname,'code' =>$release->code,'id' =>$release->id) ; 
         Yii::$app->cache->set($luckkey, $data, 3600);   
         return $data;
       }
       return $data;       
  }



  public static function add($model)
	{
		$release = new Agroagent;
		$now_time = date('Y-m-d H:i:s',time());
		$release->created_at = $now_time;
		$release->username = strtolower(strip_tags($model['username']));
		$release->password = strip_tags($model['password']);
		$release->email = strtolower(strip_tags($model['email']));
		$release->phone = strip_tags($model['phone']);
		$release->authKey = strip_tags($model['authKey']);
		$release->accessToken = strip_tags($model['accessToken']);
		// $release->role = $model['role']; 
		if ($model['upper_agent_id']) {
			 $release->upper_agent_id = $model['upper_agent_id']; 	
		}
			
		$release->realname = strip_tags($model['realname']);
		$release->agentname = strip_tags($model['agentname']);
    if (isset($model['code']) && $model['code']) {
         $release->code = trim(strip_tags($model['code'])); 
    }
    if (isset($model['oldcode'])) {
         $release->oldcode = trim(strip_tags($model['oldcode'])); 
    }
    if (isset($model['is_policies'])) {
         $release->is_policies = trim(strip_tags($model['is_policies'])); 
    }
    if (isset($model['inside'])) {
         $release->inside = trim(strip_tags($model['inside'])); 
    }
   
		$release->country = strip_tags($model['country']);
		$release->province = strip_tags($model['province']);
		$release->city = strip_tags($model['city']);
		$release->address = strip_tags($model['address']);
		//$release->zipcode = strip_tags($model['zipcode']);
		//$release->account = strip_tags($model['account']);
		//$release->uid = strip_tags($model['uid']);
		$release->staff = strip_tags($model['staff']);
		$release->status = 'pending';
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
			$release = Agroagent::findOne(['id' => $model['id']]);
			$release->username = strip_tags($model['username']);
			if ($model['password']) {
					$release->password = strip_tags($model['password']);
					$release->authKey = strip_tags($model['authKey']);
					$release->accessToken = strip_tags($model['accessToken']);
			}
			if ($model['upper_agent_id']) {
			 $release->upper_agent_id = $model['upper_agent_id']; 	
		  }
		
			$release->email = strip_tags($model['email']);
			$release->phone = strip_tags($model['phone']);		
			
			// $release->role = $model['role']; 				
			$release->realname = strip_tags($model['realname']);
			$release->agentname = strip_tags($model['agentname']);	
      if (isset($model['code']) && $model['code']) {
        $release->code = strip_tags($model['code']);  
      }
      if (isset($model['oldcode'])) {
         $release->oldcode = trim(strip_tags($model['oldcode'])); 
      }
      if (isset($model['is_policies'])) {
         $release->is_policies = trim(strip_tags($model['is_policies'])); 
      }  
      if (isset($model['inside'])) {
         $release->inside = trim(strip_tags($model['inside'])); 
      }    
			$release->country = strip_tags($model['country']);
			$release->province = strip_tags($model['province']);
			$release->city = strip_tags($model['city']);
			$release->address = strip_tags($model['address']);
			//$release->zipcode = strip_tags($model['zipcode']);
			//$release->account = strip_tags($model['account']);
			//$release->uid = strip_tags($model['uid']);
			$release->staff = strip_tags($model['staff']);
			//$release->status = 'pending';
			$release->operator = $model['operator'];
			$release->ip = $model['ip'];
			$release->updated_at = date('Y-m-d H:i:s');  
			$release->save();  
      $luckkey = 'AgroagentgetAgentphoneNameCode'.md5($model['id']);
      Yii::$app->cache->delete($luckkey);   

			return $release->id;
  }  

  public static function updatePassword($model)
  {
      $release = Agroagent::findOne(['username' => $model['username']]);    
      $release->password = strip_tags($model['password']);
      $release->authKey = strip_tags($model['authKey']);
      $release->accessToken = strip_tags($model['accessToken']);
      $release->ip = $model['ip'];
      $release->updated_at = date('Y-m-d H:i:s');  
      $release->save();       
      return $release->id;
  }    

  

  public static function updatePending($model)
  {
			$release = Agroagent::findOne(['id' => $model['id']]);	
      if (empty($release)) {
          return false;
       }	
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
            ->from(Agroagent::tableName())
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
				->from(Agroagent::tableName())
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
				->from(Agroagent::tableName())
				->where($where)
				->orderBy($orderby_sort)
				->offset($start)
				->limit($limit)
				->all();
		} else {
			return (new \yii\db\Query())
				->select($fields)
				->from(Agroagent::tableName())
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
				->from(Agroagent::tableName())
				->where($str, $params)
				->orderBy($orderby_sort)
				->offset($start)
				->limit($limit)
				->all();
		} else {
			return (new \yii\db\Query())
				->select($fields)
				->from(Agroagent::tableName())
				->where($str, $params)
				->orderBy($orderby_sort)
				->all();
		}
	}

	 /**
     * Finds user by username
     *
     * @param  string      $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
          $user = Agroagent::find()
            ->where(['username' => $username])
            ->asArray()
            ->one();

            if($user){
            return new static($user);
        }

        return null;
        /*foreach (self::$users as $user) {
            if (strcasecmp($user['username'], $username) === 0) {
                return new static($user);
            }
        }

        return null;*/
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * Validates password
     *
     * @param  string  $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
       
        //echo $password;exit;
        $password = md5($this->getAuthKey().$password);
      
        //$hash = Yii::$app->getSecurity()->generatePasswordHash($password);
        //var_dump($password,$hash,Yii::$app->getSecurity()->validatePassword($password, $this->password));exit;       

        //echo $password;exit;
        return Yii::$app->getSecurity()->validatePassword($password, $this->password);
    }
    	
	

}
