<?php

/**
 * @var yii\web\View $this
 * @var PollCategory $model
 * @var string $actionUrl
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\color\ColorInput;
use kartik\select2\Select2;

?>

<?php $form = ActiveForm::begin([
    'action' => $actionUrl,
]); ?>

<div class="row">
    <div class="col-md-8">
        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'description')->textarea(['rows' => 3]) ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($model, 'icon')->textInput([
            'maxlength' => true,
            'placeholder' => 'fa fa-users',
        ])->hint('FontAwesome or other CSS class for icon') ?>

        <?= $form->field($model, 'color')->widget(ColorInput::class, [
            'options' => ['placeholder' => '#ff0000'],
        ]) ?>

        <?= $form->field($model, 'sort_order')->input('number', ['min' => 0]) ?>

        <?= $form->field($model, 'status')->dropDownList(PollCategory::getStatusesList()) ?>
    </div>
</div>

<div class="form-group">
    <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => 'btn btn-success']) ?>
    <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-secondary']) ?>
</div>

<?php ActiveForm::end(); ?>
