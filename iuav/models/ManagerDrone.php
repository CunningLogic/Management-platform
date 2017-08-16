<?php

namespace app\models;

use yii\db\ActiveRecord;

class ManagerDrone extends ActiveRecord
{
    public static function tableName() {
        return 'manager_drone';
    }

    public static function add_control($account, $hardware_id) {
        $row = new ManagerDrone();
        $row->account = $account;
        $row->hardware_id = $hardware_id;
        $row->save();
    }

    public static function has_control($account, $hardware_id) {
        return ManagerDrone::findOne(['account' => $account, 'hardware_id' => $hardware_id]);
    }
}

