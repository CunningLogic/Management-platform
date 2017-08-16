<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\InvalidValueException;
use app\models\Agroagent;
use app\models\Rolepurview;
use yii\web\Session;


class AdminUser extends Component
{
    public function getRolePurvie()
    {
        $session = Yii::$app->session;       
        $role_id = $session->get('IUAVADMINROLEID');
        if ($role_id) {
            $where = array('role_id' => $role_id,'upper_purview_id' => '0','deleted' => '0');
            $fields = 'purview.id,purview.redirect_url,purview.redirect_name';
            $purviedData = Rolepurview::getPurviewWhere($where,$fields);
            return $purviedData;
        }
        return array();

    }
    public function checklogin($method='')
    {
        $session = Yii::$app->session;
        $loginTime = $session->get('ADMINUSERTIME');
        $role_id = $session->get('IUAVADMINROLEID');
        if ($loginTime) {
            $diff = time() - $loginTime;
            if ($diff > 600) {    
                $this->logout();           
                $this->logrefer();
            } else if ($diff > 300) {
                $session->set('ADMINUSERTIME', time());
            }
        }else{
            $this->logrefer();
        }
        if ($method && $role_id) {
            $where = array('role_id' => $role_id,'method' => $method,'deleted' => '0');
            $fields = 'role_purview.id';
            $purviedData = Rolepurview::getPurviewWhere($where,$fields);
            if (empty($purviedData)) {
               $this->logout();           
               $this->logrefer();
            }
            
        }
        return true;

    }

    public function login($id,$role_id=0)
    { 
        $session = Yii::$app->session; 
        $session->set('IUAVADMINUSERID', $id); 
        $session->set('IUAVADMINROLEID', $role_id);      
        $session->set('ADMINUSERTIME', time());    

    }

    public function logout()
    {
        $session = Yii::$app->session; 
        $session->remove('IUAVADMINUSERID');
        $session->remove('IUAVADMINROLEID');
        $session->remove('IUAVADMINUSERTIME');                
        return true; 
    }
    public function logrefer()
    {
        $url = 'http://'.$_SERVER['HTTP_HOST']."/admin/logout"; 
        header("Location:$url");
        exit();    
    }

    // 写入文件
    protected function add_log($msg, $type = 'djiuser')
    {
        $ip = $this->get_client_ip();
        $logfile = __DIR__.'/../runtime/logs/'.date('Ymd').'-'.$type.'.log';
        if ( ! file_exists($logfile))
        {
            file_put_contents($logfile, '');
        }
        $SERVER_ADDR = $_SERVER["SERVER_ADDR"];
        file_put_contents($logfile, date('Y/m/d H:i:s').":  $msg >>> $ip - $type  >> SERVER_ADDR=$SERVER_ADDR \r\n", FILE_APPEND);
    }

    protected function get_client_ip() {
        $ip = $_SERVER['REMOTE_ADDR'];
        return $ip;
    }    
}
