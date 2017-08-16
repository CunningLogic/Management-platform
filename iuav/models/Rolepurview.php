<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Rolepurview extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'role_purview';
    }

    public static function add($model)
    {
        $release = new Rolepurview;
        $now_time = date('Y-m-d H:i:s',time());
        $release->created_at = $now_time;       
        $release->role_id = strip_tags($model['role_id']);
        $release->purview_id = strip_tags($model['purview_id']);
        $release->sort_order = strip_tags($model['sort_order']);
             
        $release->updated_at = $now_time;       
        $release->save();
        return $release->id;
    } 
     public static function addLine($roleid,$purviewid)
    {
        $release = new Rolepurview;
        $now_time = date('Y-m-d H:i:s',time());
        $release->created_at = $now_time;       
        $release->role_id = strip_tags($roleid);
        $release->purview_id = strip_tags($purviewid);            
        $release->updated_at = $now_time;       
        $release->save();
        return $release->id;
    } 

    public static function updateInfo($roleid,$purviewids,$action)
    {
        $purviewidList = explode(',', $purviewids);
        if (is_array($purviewidList) && $purviewidList ) {
            foreach ($purviewidList as $key => $value) {
                $release = Rolepurview::findOne(['role_id' => $roleid,'purview_id' => $value]);
                if ($release) {
                      $release->purview_id = strip_tags($purviewids);
                      if ($action == 'add') {
                        $release->deleted = 0;
                      }else{
                        $release->deleted = 1;
                      }
                      $release->updated_at = date('Y-m-d H:i:s');  
                      $release->save();   
                }else{
                    if ($action == 'add') {
                        self::addLine($roleid,$value);
                    }
                      
                }
            }
        }          
        return true;
    }  

    public static function updateDeletedInfo($model)
    {
        $release = Rolepurview::findOne(['id' => $model['id'] ]);
        $release->deleted = $model['deleted'];
        $release->updated_at = date('Y-m-d H:i:s');  
        $release->save();       
        return $release->id;
    }     
    public static function get($start, $limit, $where = [])
    {
        return (new \yii\db\Query())
            ->select('*')
            ->from('role_purview')
            ->where($where)
            ->orderBy(['id' => SORT_DESC])
            ->offset($start)
            ->limit($limit)
            ->all();
    } 
     public static function findByUsername($name)
    {
          $user = Rolepurview::find()
            ->where(['name' => $name])
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

    public static function getPurviewWhere($where,$fields,$start = 0, $limit = 0)
    {   

          $query = new  \yii\db\Query();
          $query->select($fields)->from('role_purview')->leftJoin('purview', 'role_purview.purview_id = purview.id');
          if (isset($where['upper_purview_id']) ) {
            
             $query->andWhere(['=', 'purview.upper_purview_id',$where['upper_purview_id']]);
          }
          if (isset($where['role_id']) ) {
            
             $query->andWhere(['=', 'role_purview.role_id',$where['role_id']]);
          } 
          if (isset($where['method']) ) {
            
             $query->andWhere(['=', 'purview.method',$where['method']]);
          } 
          if (isset($where['deleted']) ) {            
             $query->andWhere(['=', 'purview.deleted',$where['deleted']]);
             $query->andWhere(['=', 'role_purview.deleted',$where['deleted']]);
          }        
          $query->orderBy(['role_purview.id' => SORT_DESC]); 
          if ($limit > 0) {
                $query->offset($start)->limit($limit);
          }  
          return $query->all();
    }

   
    
}