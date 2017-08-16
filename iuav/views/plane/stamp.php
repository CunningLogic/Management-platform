
<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\IuavFlightData;
use yii\grid\GridView;
use yii\widgets\ListView;
use yii\data\ActiveDataProvider;

$this->title = '起落信息';
$this->params['breadcrumbs'][] = ['label' => 'Iuav Flight Datas', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => '飞机信息', 'url' => ['view']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php 
    global $sn; $sn = $product_sn; //为了接受传递过来的飞机编号啊
    echo GridView::widget([
        //'filterModel' => $searchModel,
        'dataProvider' => $dataProvider,
        'caption' => '飞机编号'.$product_sn.' '.'起落信息',
        'captionOptions' => ['style' => 'font-size: 16px; font-weight: bold; color: #000; text-align: center;'],

        'columns' => [
        [
            'class' => 'yii\grid\SerialColumn',
            // 你可以在这配置更多的属性
        ],

        // 更多复杂列
        [
            'label'=>'起落时间',
            //'filter' => Html::activeTextInput($searchModel, 'url', ['class' => 'form-control']),
            'format'=>'raw',
            //'class' => 'yii\grid\DataColumn', // 默认可省略
            //'value' => function ($data) {
                //return $data->user_id;
            
            'value' => function ($model, $key, $index, $column) {
                    global $sn;
                    //var_dump($model);
                    return Html::a(date("Y-m-d H:i:s",$model->session_num/1000), 
                        [ 'plane/info', 'session_num' => $model->session_num, 'product_sn' => $sn]);
            },
        ],
        [
            'label'=>'飞行数据',
            //'filter' => Html::activeTextInput($searchModel, 'url', ['class' => 'form-control']),
            'format'=>'raw',
            //'class' => 'yii\grid\DataColumn', // 默认可省略
            'value' => function ($data) {
                return '经度: '.$data->longi.', '.'维度: '.$data->lati.', '.'高度: '.$data->alti;
            },
        ],
        /*
        //设置列的宽度
        [
            "attribute" => "title",
            "value" => "lati",
            "headerOptions" => ["width" => "200"],//设置宽度
        ],
        */
        //定义操作
        [
            "class" => "yii\grid\ActionColumn",
            "template" => "{more} {view}",
            "header" => "操作",
            "buttons" => [
                "more" => function ($url, $model, $key) { 
                    //return Html::a("更多..", $url, ["title" => "更多..."] ); 
                    return Html::a("更多...", 
                        [ 'plane/info', 'session_num' => $model->session_num ]);
                },
            ],
        ],


        ],

    ]);

?>

