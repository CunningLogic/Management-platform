
<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\IuavFlightData;
use yii\grid\GridView;
use yii\widgets\ListView;
use yii\data\ActiveDataProvider;
/* @var $this yii\web\View */
/* @var $model app\models\IuavFlightData */

//$this->title = $model->id;
//$this->registerJsFile('http://example.com/js/main.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

$url = Yii::$app->request->getReferrer(); //返回上一页的url地址

$this->title = '飞行数据';
$this->params['breadcrumbs'][] = ['label' => 'Iuav Flight Datas', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => '飞机信息', 'url' => ['view']];
$this->params['breadcrumbs'][] = ['label' => '起落信息', 'url' => $url];
$this->params['breadcrumbs'][] = $this->title; 
?>
<?php 
    echo GridView::widget([

        'dataProvider' => $dataProvider,
        'caption' => '起落时间为'.$session_num.'的飞行数据',
        'captionOptions' => ['style' => 'font-size: 16px; font-weight: bold; color: #000; text-align: center;'],

        'columns' => [

        //['class' => 'yii\grid\SerialColumn'],

        [
            'attribute'=>'时间戳',
            'format'=>'raw',
            "headerOptions" => ["width" => "155"],
            'value' => function ($data) {
                return date("Y-m-d H:i:s",$data->timestamp/1000);       
            },
        ],
        [
            'attribute'=>'flg',
            'format'=>'raw',
            'value' => function ($data) {
                return $data->frame_flag;
            },
        ],
        [
            'attribute'=>'index',
            'format'=>'raw',
            'value' => function ($data) {
                return $data->frame_index;
            },
        ],
/*        [
            'attribute'=>'飞手编号',
            'format'=>'raw',
            'value' => function ($data) {
                return $data->pilot_num;
            },
        ],*/
        [
            'attribute'=>'经度',
            'format'=>'raw',
            'value' => function ($data) {
                return $data->longi;
            },
        ],
        [
            'attribute'=>'纬度',
            'format'=>'raw',
            'value' => function ($data) {
                return $data->lati;
            },
        ],
        [
            'attribute'=>'高度',
            'format'=>'raw',
            'value' => function ($data) {
                return $data->alti;
            },
        ],
        [
            'attribute'=>'雷达高',
            'format'=>'raw',
            'value' => function ($data) {
                return $data->radar_height;
            },
        ],

        [
            'attribute'=>'速度X',
            'format'=>'raw',
            'value' => function ($data) {
                return $data->velocity_x;
            },
        ],
        [
            'attribute'=>'速度Y',
            'format'=>'raw',
            'value' => function ($data) {
                return $data->velocity_y;
            },
        ],
        [
            'attribute'=>'喷药',
            'format'=>'raw',
            'value' => function ($data) {
                return $data->spray_flag;
            },
        ],
        [
            'attribute'=>'喷幅',
            'format'=>'raw',
            'value' => function ($data) {
                return $data->farm_delta_y;
            },
        ],
        [
            'attribute'=>'亩数',
            'format'=>'raw',
            'value' => function ($data) {
                return $data->work_area;
            },
        ],
        [
            'attribute'=>'物种',
            'format'=>'raw',
            'value' => function ($data) {
                return $data->plant;
            },
        ],
        [
            'attribute'=>'模式',
            'format'=>'raw',
            'value' => function ($data) {
                return $data->farm_mode;
            },
        ],

        ],

    ]);

?>

