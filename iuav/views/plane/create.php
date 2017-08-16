<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\IuavFlightData */

$this->title = 'Create Iuav Flight Data';
$this->params['breadcrumbs'][] = ['label' => 'Iuav Flight Datas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="iuav-flight-data-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
