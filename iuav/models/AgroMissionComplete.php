<?php

namespace app\models;

use yii\db\ActiveRecord;

class AgroMissionComplete extends ActiveRecord
{

    public static function tableName()
    {
        return 'agro_mission_complete';
    }

    /*
    public static function add($model)
    {
        $release = new AgroMissionComplete;
        if (isset($model['task_id'])) {
            $release->task_id = strip_tags($model['task_id']);
        }
        if (isset($model['task_name'])) {
            $release->task_name = strip_tags($model['task_name']);
        }
        if (isset($model['pilot_id'])) {
            $release->pilot_id = strip_tags($model['pilot_id']);
        }
        if (isset($model['pilot_name'])) {
            $release->pilot_name = strip_tags($model['pilot_name']);
        }
        if (isset($model['team_id'])) {
            $release->team_id = strip_tags($model['team_id']);
        }
        if (isset($model['team_name'])) {
            $release->team_name = strip_tags($model['team_name']);
        }
        if (isset($model['start_spray_time'])) {
            $release->start_spray_time = strip_tags($model['start_spray_time']);
        }
        if (isset($model['stop_spray_time'])) {
            $release->stop_spray_time = strip_tags($model['stop_spray_time']);
        }
        if (isset($model['plan_area'])) {
            $release->plan_area = strip_tags($model['plan_area']);
        }
        if (isset($model['spraying_area'])) {
            $release->spraying_area = strip_tags($model['spraying_area']);
        }
        if (isset($model['set_flow_mu'])) {
            $release->set_flow_mu = strip_tags($model['set_flow_mu']);
        }
        if (isset($model['total_flow'])) {
            $release->total_flow = strip_tags($model['total_flow']);
        }
        if (isset($model['spray_mode'])) {
            $release->spray_mode = strip_tags($model['spray_mode']);
        }

        $release->save();
        return $release->id;
    }

    public static function updateInfo($model)
    {
        $release = AgroMissionComplete::findOne(['id' => $model['id']]);

        if (isset($model['task_id'])) {
            $release->task_id = strip_tags($model['task_id']);
        }
        if (isset($model['task_name'])) {
            $release->task_name = strip_tags($model['task_name']);
        }
        if (isset($model['pilot_id'])) {
            $release->pilot_id = strip_tags($model['pilot_id']);
        }
        if (isset($model['pilot_name'])) {
            $release->pilot_name = strip_tags($model['pilot_name']);
        }
        if (isset($model['team_id'])) {
            $release->team_id = strip_tags($model['team_id']);
        }
        if (isset($model['team_name'])) {
            $release->team_name = strip_tags($model['team_name']);
        }
        if (isset($model['start_spray_time'])) {
            $release->start_spray_time = strip_tags($model['start_spray_time']);
        }
        if (isset($model['stop_spray_time'])) {
            $release->stop_spray_time = strip_tags($model['stop_spray_time']);
        }
        if (isset($model['plan_area'])) {
            $release->plan_area = strip_tags($model['plan_area']);
        }
        if (isset($model['spraying_area'])) {
            $release->spraying_area = strip_tags($model['spraying_area']);
        }
        if (isset($model['set_flow_mu'])) {
            $release->set_flow_mu = strip_tags($model['set_flow_mu']);
        }
        if (isset($model['total_flow'])) {
            $release->total_flow = strip_tags($model['total_flow']);
        }
        if (isset($model['spray_mode'])) {
            $release->spray_mode = strip_tags($model['spray_mode']);
        }

        $release->save();
        return $release->id;
    }
    */

    public static function getAndEqualWhere($where = [], $start = 0, $limit = 20, $orderby = 'id', $sort = 1, $fields = '*')
    {
        $orderby_sort = [];
        if ($sort > 0) {
            $orderby_sort[$orderby] = SORT_DESC;
        } else {
            $orderby_sort[$orderby] = SORT_ASC;
        }

        if ($limit > 0) {
            return (new \yii\db\Query())
                ->select($fields)
                ->from(AgroMissionComplete::tableName())
                ->where($where)
                ->orderBy($orderby_sort)
                ->offset($start)
                ->limit($limit)
                ->all();
        } else {
            return (new \yii\db\Query())
                ->select($fields)
                ->from(AgroMissionComplete::tableName())
                ->where($where)
                ->orderBy($orderby_sort)
                ->all();
        }
    }

    public static function getAndWhere($where = [], $start = 0, $limit = 20, $orderby = 'id', $sort = 1, $fields = '*')
    {
        $orderby_sort = [];
        if ($sort > 0) {
            $orderby_sort[$orderby] = SORT_DESC;
        } else {
            $orderby_sort[$orderby] = SORT_ASC;
        }

        $params = [];
        $arr = [];
        foreach ($where as $v) {
            $arr[] = $v[0] . $v[1] . ' :' . $v[0];
            $params[':' . $v[0]] = $v[2];
        }
        $str = implode(' AND ', $arr);

        if ($limit > 0) {
            return (new \yii\db\Query())
                ->select($fields)
                ->from(AgroMissionComplete::tableName())
                ->where($str, $params)
                ->orderBy($orderby_sort)
                ->offset($start)
                ->limit($limit)
                ->all();
        } else {
            return (new \yii\db\Query())
                ->select($fields)
                ->from(AgroMissionComplete::tableName())
                ->where($str, $params)
                ->orderBy($orderby_sort)
                ->all();
        }
    }

    public static function bulkAdd($models) {
        $t = new AgroMissionComplete();
        // get all table field
        $keys = $t->attributes();
        // remove the first id column
        array_shift($keys);

        $result = \Yii::$app->db->createCommand()->batchInsert(
            AgroMissionComplete::tableName(),
            $keys,
            $models
        )->execute();

        return $result;
    }
}