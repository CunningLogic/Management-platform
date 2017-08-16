<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\IuavFlightDataSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="iuav-flight-data-search">

    <?php $form = ActiveForm::begin([
        'action' => ['result'],
        'method' => 'post',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'user_id') ?>

    <?= $form->field($model, 'team_id') ?>

    <?= $form->field($model, 'version') ?>

    <?= $form->field($model, 'timestamp') ?>

    <?php // echo $form->field($model, 'longi') ?>

    <?php // echo $form->field($model, 'lati') ?>

    <?php // echo $form->field($model, 'alti') ?>

    <?php // echo $form->field($model, 'product_sn') ?>

    <?php // echo $form->field($model, 'spray_flag') ?>

    <?php // echo $form->field($model, 'motor_status') ?>

    <?php // echo $form->field($model, 'radar_height') ?>

    <?php // echo $form->field($model, 'velocity_x') ?>

    <?php // echo $form->field($model, 'velocity_y') ?>

    <?php // echo $form->field($model, 'farm_delta_y') ?>

    <?php // echo $form->field($model, 'farm_mode') ?>

    <?php // echo $form->field($model, 'pilot_num') ?>

    <?php // echo $form->field($model, 'session_num') ?>

    <?php // echo $form->field($model, 'frame_index') ?>

    <?php // echo $form->field($model, 'flight_version') ?>

    <?php // echo $form->field($model, 'plant') ?>

    <?php // echo $form->field($model, 'create_time') ?>

    <?php // echo $form->field($model, 'work_area') ?>

    <?php // echo $form->field($model, 'ext1') ?>

    <?php // echo $form->field($model, 'ext2') ?>

    <?php // echo $form->field($model, 'upper_uid') ?>

    <?php // echo $form->field($model, 'frame_flag') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
