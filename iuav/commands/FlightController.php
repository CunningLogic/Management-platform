<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use yii\console\Controller;
use app\models\Agroteam;
use yii;
use app\models\Agroflight;
use app\models\Iuavflightdata;
use app\models\Agroflyer;
use app\models\Agroactiveinfo;
use app\models\Agroflyerworkinfo;
/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class FlightController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     */
    public function actionIndex()
    {
        $logfile='commands_flight';
        $model = array(); 
        $this->add_log(json_encode($model),$logfile);
        $flightData = Agroflight::getAndEqualWhere($model,0,1,'id',1,'flight_data_id');
        if (empty($flightData)) {
            $model['id'] = 0;  
        }else{
            $model['id'] = $flightData[0]['flight_data_id'];   //获得当前飞行记录的最新id
        }
        $model['frame_flag'] = 1;
        $this->add_log(json_encode($model),$logfile);
        $fields = '*';
        $iuavFlightData = Iuavflightdata::getIDWhere($model,$fields, 0, 100);
        if ($iuavFlightData && is_array($iuavFlightData)) {
           foreach ($iuavFlightData as $key => $value) {
              $model = array();
              $model['flight_data_id'] = $value['id'];
              $model['uid'] = $value['user_id'];
              if($value['team_id'] > 0) {
                  $teamData = Agroteam::getIdData(array('id' => $value['team_id']),'uid, name');
                  if($teamData) {
                      $model['team_name'] = $teamData['0']['name'];
                  }
                  if ($value['boss_id'] ) {
                      $model['upper_uid'] = $value['boss_id'];
                  } else {
                      $model['upper_uid'] = $teamData['0']['uid'];
                  }
              } else {
                  $model['upper_uid'] = $value['user_id'];
              }
              $where['bossid'] = $model['upper_uid'];
              $where['deleted'] = 0;
              $where['flyerid'] = $value['user_id'];
              $model['flyer_name'] = Agroflyer::getNameByID($where);
              $model['team_id'] = $value['team_id'];
              $model['version'] = $value['version'];
              $model['timestamp'] = $value['timestamp'];
              $model['longi'] = $value['longi'];
              $model['lati'] = $value['lati'];
              $location = $this->regeo( $model['longi'],$model['lati']);
              $model['location'] = $location;
              $model['product_sn'] = $value['product_sn'];
              //通过product_sn来找到飞机名称
              $where['product_sn'] = $model['product_sn'];
              $model['nickname'] = Agroactiveinfo::getNameByID($where);

              $model['session_num'] = $value['session_num'];
              $model['work_area'] = $value['work_area'];
/*              //通过session_num, timestamp, product_sn找到第一条记录
              $first = Iuavflightdata::findOne(array('product_sn'=>$model['product_sn'], 'session_num'=>$value['session_num'], 'timestamp'=>$value['session_num']));
              if ($first) {
                  $model['work_area'] = $value['work_area'] - $first->work_area;
              }*/
              $model['farm_delta_y'] = $value['farm_delta_y'];
              $model['flight_version'] = $value['flight_version'];
              $model['plant'] = $value['plant'];
              $work_time = $value['timestamp']- $value['session_num'];
              $model['work_time'] = $work_time;
              $session_num = $value['session_num']/1000;             
              $start_end = date("H:i:s",$session_num + 8 * 3600)."-".date("H:i:s",$value['timestamp']/1000 + 8 * 3600); //加上8个小时，转换成北京时间
              $model['start_end'] = $start_end;
              $model['create_date'] = date("Ymd",$session_num);
              Agroflight::add($model);
              //每增加一条飞行记录，则更新此飞手的飞行信息：总的作业时长，总的喷洒面积，总的作业次数。
              $flyerWhere['upper_uid'] = $model['upper_uid'];
              $flyerWhere['team_id'] = $model['team_id'];
              $flyerWhere['uid'] = $model['uid'];
              $flyerWhere['deleted'] = '0';
              $flyerInfo = Agroflyerworkinfo::findOne($flyerWhere); 
              if($flyerInfo) {
                  $flyerInfo->all_times = $flyerInfo->all_times + 1;
                  $flyerInfo->all_area = $flyerInfo->all_area + $model['work_area'];
                  $flyerInfo->all_time = $flyerInfo->all_time + $model['work_time'];
                  $flyerInfo->updated_at = date('Y-m-d H:i:s', time());
                  $flyerInfo->save();
              } else {
                  Agroflyerworkinfo::add($model);
              }
           }
        }  
    }
    public function regeo($longi,$lati)
    {
        $post_data = array();
        $post_data['location']  = $longi.','.$lati;
        $post_data['key']  = 'b1501370e873f5784f75d43d061c181a';
        $url = "http://restapi.amap.com/v3/geocode/regeo";       
        $result = $this->send_post($url,$post_data);
        if ($result) {
          $resultData = json_decode($result,true);          
          if ($resultData['status'] == '1' && $resultData['info'] == 'OK') {
              return $resultData['regeocode']['formatted_address'];
          }
        }
        return '';
    }
    public function send_post($url,$post_data)
    {
       try {         
           $postdata = http_build_query($post_data);  
           $url =  $url.'?'.$postdata;
            $options = array(
                'http' => array(                
                    'timeout' => 15*60,
                  )
              );
            $context = stream_context_create($options);
            $result = file_get_contents($url,false,$context);
            return $result;
       } catch (Exception $e) {
           return '';
       }
        
    }
     // 写入文件
    protected function add_log($msg, $type = 'site_login')
    {
        $ip = '';
        $logfile = __DIR__.'/../runtime/logs/'.date('Ymd').'-'.$type.'.log';
        if ( ! file_exists($logfile))
        {
            file_put_contents($logfile, '');
        }
        $SERVER_ADDR = '';
        file_put_contents($logfile, date('Y/m/d H:i:s').":  $msg >>> $ip - $type  >> SERVER_ADDR=$SERVER_ADDR \r\n", FILE_APPEND);
    }
}
