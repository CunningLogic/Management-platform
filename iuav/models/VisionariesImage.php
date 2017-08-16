<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

use app\components\Helper;

class VisionariesImage extends ActiveRecord
{
     public static function tableName()
    {
        return 'visionaries_image';
    }

    public static function add($model)
    {
        $release = new VisionariesImage;
        $now_time = date('Y-m-d H:i:s',time());
        $release->created_at = $now_time;		
        $release->visi_user_id = $model['visi_user_id'];
        $release->zipurl = $model['zipurl'];
        $release->title = strip_tags($model['title']);
        $release->location = strip_tags($model['location']);
        $release->dji_gear = strip_tags($model['dji_gear']);
        $release->exif_info = strip_tags($model['exif_info']);
        $release->type = $model['type']; 
        $release->video_id = $model['video_id'];  
        $release->cover = $model['cover']; 
        $release->duration = $model['duration']; 
        $release->upload_token = $model['upload_token'];  
        $release->status = 'draft';     
        //$release->longitude = $model['longitude'];
        //$release->latitude = $model['latitude'];
        
        $release->updated_at = $now_time;		
        $release->save();
        return $release->id;
    } 
    public static function updateInfo($model)
    {
        $release = VisionariesImage::findOne(['id' => $model['id']]);
        $release->visi_user_id = $model['visi_user_id'];
        if ($model['zipurl']) {
           $release->zipurl = $model['zipurl'];
        }        
        $release->title = strip_tags($model['title']);
        $release->location = strip_tags($model['location']);
        $release->dji_gear = strip_tags($model['dji_gear']);
        $release->exif_info = strip_tags($model['exif_info']);
        $release->updated_at = date('Y-m-d H:i:s');  
        $release->save();       
        return $release->id;
    }    
     public static function updateVideoReset($model)
    {
        $release = VisionariesImage::findOne(['id' => $model['id']]);
        $release->visi_user_id = $model['visi_user_id'];
        if ($model['zipurl']) {
           $release->zipurl = $model['zipurl'];
        }        
        $release->title = strip_tags($model['title']);
        $release->location = strip_tags($model['location']);
        $release->dji_gear = strip_tags($model['dji_gear']);
        $release->exif_info = strip_tags($model['exif_info']);
        $release->video_id = $model['video_id'];  
        $release->cover = $model['cover']; 
        $release->duration = $model['duration']; 
        $release->upload_token = $model['upload_token'];    
        $release->updated_at = date('Y-m-d H:i:s');   
        $release->status = 'draft';      
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
                ->from(VisionariesImage::tableName())				
                ->where($where)
                ->orderBy($orderby_sort)
                ->offset($start)
                ->limit($limit)
                ->all();
        } else {
            return (new \yii\db\Query())
                ->select($fields)
                ->from(VisionariesImage::tableName())
                ->where($where)
                ->orderBy($orderby_sort)
                ->all();
        }
    } 

    public static function getCountAndEqualWhere($where = [])
    {   

         return (new \yii\db\Query())
                ->select('id')
                ->from(VisionariesImage::tableName())
                ->where($where)
                ->count();
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
                ->from(VisionariesImage::tableName())
                ->where($str, $params)
                ->orderBy($orderby_sort)
                ->offset($start)
                ->limit($limit)
                ->all();
        } else {
            return (new \yii\db\Query())
                ->select($fields)
                ->from(VisionariesImage::tableName())
                ->where($str, $params)
                ->orderBy($orderby_sort)
                ->all();
        }
    }

    public static function updateVideoID($model)
    {
        $release = VisionariesImage::findOne(['upload_token' => $model['upload_token']]);
        $release->video_id = $model['video_id'];
        $release->updated_at = date('Y-m-d H:i:s');		
        $release->save();       
        return $release->id;
    }
    public static function updateVideoInfo($model)
    {
        $release = VisionariesImage::findOne(['upload_token' => $model['upload_token']]);
        $release->video_id = $model['video_id'];
        $release->cover = $model['cover'];
        $release->duration = $model['duration'];
        $release->updated_at = date('Y-m-d H:i:s');     
        $release->save();        
        return $release->id;
    }
    public static function updateVideoFinish($model)
    {
        $release = VisionariesImage::findOne(['video_id' => $model['video_id']]);       
        $release->cover = $model['cover'];
        $release->duration = $model['duration'];
        $release->updated_at = date('Y-m-d H:i:s');     
        $release->save();        
        return $release->id;
    }

    public static function updateInfoDisable($model)
    {
        $release = VisionariesImage::findOne(['id' => $model['id']]);
        $release->disable = $model['disable'];
        $release->updated_at = date('Y-m-d H:i:s');		
        $release->save();        
        return $release->id;
    }
    public static function changeStatus($model)
    {      
        return  VisionariesImage::updateAll(array('status' => $model['status'] ), ['id'=> $model['id'] ] );;
    }


   
}
