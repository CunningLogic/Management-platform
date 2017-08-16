<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

use app\components\Helper;

class Ddsliveroom extends ActiveRecord
{
    const ALL_ROOM_KEY = '__ALL_ROOM_INFO_KEY__';
    const ROOM_UPDATE_TIME_KEY = '__ROOM_UPDATE_KET__';

    public static function tableName()
    {
        return 'dds_live_room';
    }

    public static function add($model)
    {
        $release = new Ddsliveroom;
        $now_time = date('Y-m-d H:i:s',time());
        $release->created_at = $now_time;		
        $release->video_id = $model['video_id'];
        $release->name = $model['name'];
        $release->ext1 = $model['ext1'];
        $release->ext2 = $model['ext2'];
        $release->updated_at = $now_time;		
        $release->save();
        return $release->id;
    }

    public function getDdslivevideo()
    {
        return $this->hasOne(Ddslivevideo::className(), ['id' => 'video_id']);
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
                ->from(Ddsliveroom::tableName())				
                ->where($where)
                ->orderBy($orderby_sort)
                ->offset($start)
                ->limit($limit)
                ->all();
        } else {
            return (new \yii\db\Query())
                ->select($fields)
                ->from(Ddsliveroom::tableName())
                ->where($where)
                ->orderBy($orderby_sort)
                ->all();
        }
    }

    public static function getJoinAndEqualWhere($where = [], $start = 0, $limit = 20, $orderby = 'dds_live_room.id', $sort = 1, $fields = 'dds_live_room.id as room_id,dds_live_room.name,dds_live_room.disable,dds_live_video.* ')
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
                ->from(Ddsliveroom::tableName())
                ->leftJoin('dds_live_video', 'dds_live_video.id = dds_live_room.video_id')
                ->where($where)
                ->orderBy($orderby_sort)
                ->offset($start)
                ->limit($limit)
                ->all();
        } else {
            return (new \yii\db\Query())
                ->select($fields)
                ->from(Ddsliveroom::tableName())
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
                ->from(Ddsliveroom::tableName())
                ->where($str, $params)
                ->orderBy($orderby_sort)
                ->offset($start)
                ->limit($limit)
                ->all();
        } else {
            return (new \yii\db\Query())
                ->select($fields)
                ->from(Ddsliveroom::tableName())
                ->where($str, $params)
                ->orderBy($orderby_sort)
                ->all();
        }
    }

    public static function updateInfoVideo($model)
    {
        $release = Ddsliveroom::findOne(['id' => $model['id']]);
        $release->video_id = $model['video_id'];
        $release->updated_at = date('Y-m-d H:i:s');		
        $release->save();
        self::setAllRoomInfo();
        return $release->id;
    }
    public static function updateInfoDisable($model)
    {
        $release = Ddsliveroom::findOne(['id' => $model['id']]);
        $release->disable = $model['disable'];
        $release->updated_at = date('Y-m-d H:i:s');		
        $release->save();
        self::setAllRoomInfo();
        return $release->id;
    }

    /**
     * last_time 上次请求时间
     **/
    public static function getRoomInfoByTime($last_time) {
        $info = [
                'current_time'  => Helper::microtime(),
                'is_same'       => 1,
                'room_info'     => self::getAllRoomInfo(),
                ];
        if ($last_time <= self::getUpdateTime()) {
            $info['is_same'] = 0;
        }

        return $info;
    }



    /***
     * 获取所有直播房间的信息，包括房间所对应的视频信息
     **/
    public static function getAllRoomInfo() 
    {
        $info = Yii::$app->cache->get(self::ALL_ROOM_KEY);
        if (empty($info)) {
            self::setAllRoomInfo();
            $info = Yii::$app->cache->get(self::ALL_ROOM_KEY);
        }
        $info = self::setDefaultImage($info);
        return $info ? $info : [];
    }

    public static function setAllRoomInfo()
    {
        $rooms = self::find()->with('ddslivevideo')->all();
        $info = [];
        foreach ($rooms as $room) {
            if ($room->ddslivevideo) {
                $info[] = [
                    'id'            => $room->id,
                    'name'          => $room->name,
                    'type'          => $room->ddslivevideo->type,
                    'disable'       => $room->disable,
                    'screenshot'    => trim($room->ddslivevideo->screenshot),
                    'url'           => $room->ddslivevideo->type ? explode(',', trim($room->ddslivevideo->url)) : trim($room->ddslivevideo->url),
                    'low_url'       => $room->ddslivevideo->type ? explode(',', trim($room->ddslivevideo->low_url)) : trim($room->ddslivevideo->low_url),
                ];
            } else {
                $info[] = [
                    'id'            => $room->id,
                    'name'          => $room->name,
                    'type'          => '',
                    'disable'       => $room->disable,
                    'screenshot'    => '',
                    'url'           => '',
                    'low_url'       => '',
                ];
            }
        }
        Yii::$app->cache->set(self::ALL_ROOM_KEY, $info, 3600);
        self::setUpdateTime();
    }

    /**
     * type 为 2的时候代表插播图片
     **/
    public static function setDefaultImage($rooms)
    {
        $info = [];
        $img = self::getDefaultImage();
        foreach($rooms as $k => $room) {
            if ($room['disable'] == 1) {
                if ($room['id'] == 1) {
                    $room['type'] = 2;
                    //主视频永远可用，后台关闭代表切换到图片模式，前台仍然显示为可用即可
                    $room['disable'] = 0; 
                    $room['screenshot'] = $img;
                    $info[] = $room;
                } 
            } else {
                $info[] = $room;
            }
        }
        return $info;
    }

    /***
     * start_time 2015-12-20 10:50 
     * end_time 2015-12-20 22:00?  
     */
    public static function getDefaultImage()
    {
        $start_time = strtotime('2015-12-20 10:50:00');
        $end_time = strtotime('2015-12-20 20:00:00');
        $now = time();
        if ($now < $start_time) {
            return "http://" . $_SERVER["HTTP_HOST"] . '/event/live/images/default_img_before.png';
        } elseif ($now < $end_time) {
            return "http://" . $_SERVER["HTTP_HOST"] . '/event/live/images/default_img_current.png';
        } else {
            return "http://" . $_SERVER["HTTP_HOST"] . '/event/live/images/default_img_after.png';
        }
    }

    public static function setUpdateTime()
    {
        Yii::$app->cache->set(self::ROOM_UPDATE_TIME_KEY, Helper::microtime());
    }

    public static function getUpdateTime() 
    {
        $time = Yii::$app->cache->get(self::ROOM_UPDATE_TIME_KEY);
        if (empty($time)) {
            self::setUpdateTime();
            $time = Yii::$app->cache->get(self::ROOM_UPDATE_TIME_KEY);
        }
        return $time;
    }
}
