
<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\IuavFlightData;
use yii\grid\GridView;
//use yii\widgets\ListView;
use yii\data\ActiveDataProvider;
/* @var $this yii\web\View */
/* @var $model app\models\IuavFlightData */

//$this->title = $model->id;
//$this->registerJsFile('http://example.com/js/main.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
//var_dump($dataProvider);die;

$this->title = '飞机信息';
$this->params['breadcrumbs'][] = ['label' => 'Iuav Flight Datas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php 
    echo GridView::widget([

        'dataProvider' => $dataProvider,
        'caption' => '飞机信息',
        'captionOptions' => ['style' => 'font-size: 16px; font-weight: bold; color: #000; text-align: center;'],


        'columns' => [   
        [
            'class' => 'yii\grid\SerialColumn',
            // 你可以在这配置更多的属性
        ],
        [
            'label'=>'用户ID',
            'format'=>'raw',
            'value' => function ($data) {
            return $data->user_id;
            },
        ],
        [
            'label'=>'队伍ID',
            'format'=>'raw',
            'value' => function ($data) {
            return $data->team_id;
            },
        ],
        [
            'label'=>'老板ID',
            'format'=>'raw',
            'value' => function ($data) {
            return $data->boss_id;
            },
        ],
        [
            'label'=>'飞手编号',
            'format'=>'raw',
            'value' => function ($data) {
            return $data->pilot_num;
            },
        ],

        [
            'attribute'=>'飞机编号',
            'contentOptions' => [
            'style' => 'vertical-align: middle;'
            ],
            //'filter' => Html::activeTextInput($searchModel, 'url', ['class' => 'form-control']),
            'format'=>'raw',
            //"visible" => intval(Yii::$app->request->get("type")) == 1,
            //'class' => 'yii\grid\DataColumn', // 默认可省略
            //'value' => function ($data) {
            //return $data->user_id;
            'value' => function ($model, $key, $index, $column) {
                //var_dump($model);
                return Html::a($model->product_sn, 
                    [ 'plane/stamp', 'product_sn' => $model->product_sn ]);// 这里可以根据该表的其他字段进行关联获取
            },
        ],  

        [
            'label'=>'起落时间',
            //'filter' => Html::activeTextInput($searchModel, 'url', ['class' => 'form-control']),
            'format'=>'raw',
            //'class' => 'yii\grid\DataColumn', // 默认可省略
            //'value' => function ($data) {
            //return $data->user_id;

            'value' => function ($model, $key, $index, $column) {
                //var_dump($model);
                return Html::a(date("Y-m-d H:i:s",$model->session_num/1000), 
                    [ 'plane/stamp', 'product_sn' => $model->product_sn ]);
            },
        ],

        //定义操作
        [
            "class" => "yii\grid\ActionColumn",
            //"template" => "{more} {view} {update}",
            "template" => "{more} {view}",
            "header" => "操作",
            "buttons" => [
                "more" => function ($url, $model, $key) { 
                    //return Html::a("更多..", $url, ["title" => "更多..."] ); 
                    return Html::a("更多...", 
                        [ 'plane/stamp', 'product_sn' => $model->product_sn ]);
                },
            ],
        ],
        
        /*
        //增加按钮调用JS操作
        [
            "class" => "yii\grid\ActionColumn",
            "header" => "操作",
            "template" => "{view} {update} {update-status}",
            "buttons" => [
                "update-status" => function ($url, $model, $key) {
                    return Html::a("更新状态", "javascript:;", ["onclick"=>"update_status(this, ".$model->id.");"]); },
            ],
        ],
        */

        ],

    ]);

?>

