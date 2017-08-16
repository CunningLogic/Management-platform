<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Role extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'role';
    }

    public static function add($model)
    {
        $release = new Role;
        $now_time = date('Y-m-d H:i:s',time());
        $release->created_at = $now_time;       
        $release->name = strip_tags($model['name']);
        $release->sort_order = strip_tags($model['sort_order']);
             
        $release->updated_at = $now_time;       
        $release->save();
        return $release->id;
    } 
    public static function updateInfo($model)
    {
        $release = Role::findOne(['id' => $model['id']]);
        $release->name = strip_tags($model['name']);
        $release->sort_order = strip_tags($model['sort_order']);
        $release->updated_at = date('Y-m-d H:i:s');  
        $release->save();       
        return $release->id;
    }  

    public static function updateDeletedInfo($model)
    {
        $release = Role::findOne(['id' => $model['id'] ]);
        $release->deleted = $model['deleted'];
        $release->updated_at = date('Y-m-d H:i:s');  
        $release->save();       
        return $release->id;
    }     
    public static function get($start, $limit, $where = [])
    {
        return (new \yii\db\Query())
            ->select('*')
            ->from('role')
            ->where($where)
            ->orderBy(['id' => SORT_DESC])
            ->offset($start)
            ->limit($limit)
            ->all();
    } 
     public static function findByUsername($name)
    {
          $user = Role::find()
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
   
    
}