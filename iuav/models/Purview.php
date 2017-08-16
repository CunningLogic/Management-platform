<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Purview extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'purview';
    }

    public static function add($model)
    {
        $release = new Purview;
        $now_time = date('Y-m-d H:i:s',time());
        $release->created_at = $now_time;       
        $release->description = strip_tags($model['description']);
        $release->redirect_url = strip_tags($model['redirect_url']);
        $release->redirect_name = strip_tags($model['redirect_name']);
        $release->method = strip_tags($model['method']);
        $release->upper_purview_id = strip_tags($model['upper_purview_id']);
        $release->updated_at = $now_time;       
        $release->save();
        return $release->id;
    } 
    public static function updateInfo($model)
    {
        $release = Purview::findOne(['id' => $model['id']]);
        $release->description = strip_tags($model['description']);
        $release->redirect_url = strip_tags($model['redirect_url']);
        $release->redirect_name = strip_tags($model['redirect_name']);
        $release->method = strip_tags($model['method']);
        $release->upper_purview_id = strip_tags($model['upper_purview_id']);
        $release->updated_at = date('Y-m-d H:i:s');  
        $release->save();       
        return $release->id;
    }  

    public static function updateDeletedInfo($model)
    {
        $release = Purview::findOne(['id' => $model['id'] ]);
        $release->deleted = $model['deleted'];
        $release->updated_at = date('Y-m-d H:i:s');  
        $release->save();       
        return $release->id;
    }     
    public static function get($start, $limit, $where = [])
    {
        return (new \yii\db\Query())
            ->select('*')
            ->from('purview')
            ->where($where)
            ->orderBy(['id' => SORT_DESC])
            ->offset($start)
            ->limit($limit)
            ->all();
    } 
     public static function findByUsername($method)
    {
          $user = Purview::find()
            ->where(['method' => $method])
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