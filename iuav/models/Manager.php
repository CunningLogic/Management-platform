<?php

namespace app\models;

use yii\db\ActiveRecord;

class Manager extends ActiveRecord
{
    public static function tableName()
    {
        return 'manager';
    }

    public static function get_all_manager($boss_uid) {
        return Manager::findAll(['boss_uid' => $boss_uid]);
    }

    public static function add_manager($boss_account, $boss_uid, $manager_account, $manager_level) {
        $row = new Manager;
        $row->boss_account = $boss_account;
        $row->boss_uid = $boss_uid;
        $row->account = $manager_account;
        $row->level = $manager_level;
        $row->save();
    }

    public static function del_manager($id) {
        Manager::deleteAll(['id' => $id]);
    }
}
