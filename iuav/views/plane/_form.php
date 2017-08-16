<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\IuavFlightData */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="iuav-flight-data-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'user_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'team_id')->textInput() ?>

    <?= $form->field($model, 'version')->textInput() ?>

    <?= $form->field($model, 'timestamp')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'longi')->textInput() ?>

    <?= $form->field($model, 'lati')->textInput() ?>

    <?= $form->field($model, 'alti')->textInput() ?>

    <?= $form->field($model, 'product_sn')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'spray_flag')->textInput() ?>

    <?= $form->field($model, 'motor_status')->textInput() ?>

    <?= $form->field($model, 'radar_height')->textInput() ?>

    <?= $form->field($model, 'velocity_x')->textInput() ?>

    <?= $form->field($model, 'velocity_y')->textInput() ?>

    <?= $form->field($model, 'farm_delta_y')->textInput() ?>

    <?= $form->field($model, 'farm_mode')->textInput() ?>

    <?= $form->field($model, 'pilot_num')->textInput() ?>

    <?= $form->field($model, 'session_num')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'frame_index')->textInput() ?>

    <?= $form->field($model, 'flight_version')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'plant')->textInput() ?>

    <?= $form->field($model, 'create_time')->textInput() ?>

    <?= $form->field($model, 'work_area')->textInput() ?>

    <?= $form->field($model, 'ext1')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'ext2')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'upper_uid')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'frame_flag')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
