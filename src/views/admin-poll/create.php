<?php

/**
 * @var yii\web\View $this
 * @var \ZakharovAndrew\poll\models\Poll $model
 * @var \ZakharovAndrew\poll\models\PollAnswer[] $answers
 * @var array $statusesList
 * @var array $categoriesList
 * @var array $imagePositions
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use kartik\select2\Select2;
use kartik\file\FileInput;
use kartik\color\ColorInput;

$this->title = 'Create Poll';
$this->params['breadcrumbs'][] = ['label' => 'Polls', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="poll-create">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data'],
    ]); ?>

    <div class="row">
        <div class="col-md-8">
            <?= $form->field($model, 'question')->textarea(['rows' => 3]) ?>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'category_id')->widget(Select2::class, [
                        'data' => $categoriesList,
                        'options' => ['placeholder' => 'Select category...'],
                        'pluginOptions' => ['allowClear' => true],
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'priority')->input('number', ['min' => 0, 'max' => 100]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'start_date')->input('datetime-local') ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'end_date')->input('datetime-local') ?>
                </div>
            </div>

            <?= $form->field($model, 'status')->dropDownList($statusesList) ?>

            <hr>
            <h4>Answers</h4>
            <div class="poll-answers">
                <?php for ($i = 0; $i < 4; $i++): ?>
                    <div class="row">
                        <div class="col-md-11">
                            <?= $form->field($answers[$i], "[$i]answer_text")->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-md-1" style="padding-top: 30px;">
                            <?= Html::activeHiddenInput($answers[$i], "[$i]sort_order", ['value' => $i]) ?>
                            <?= Html::activeHiddenInput($answers[$i], "[$i]id") ?>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Image</div>
                <div class="card-body">
                    <?= $form->field($model, 'image_url')->widget(FileInput::class, [
                        'options' => ['accept' => 'image/*'],
                        'pluginOptions' => [
                            'showUpload' => false,
                            'showRemove' => false,
                            'allowedFileExtensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                        ],
                    ]) ?>
                    <?= $form->field($model, 'image_position')->dropDownList($imagePositions) ?>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">Access Control</div>
                <div class="card-body">
                    <?= $form->field($model, 'allowed_statuses')->widget(Select2::class, [
                        'data' => $statusesList,
                        'options' => [
                            'placeholder' => 'Select allowed statuses...',
                            'multiple' => true,
                        ],
                        'pluginOptions' => ['allowClear' => true],
                    ]) ?>
                    <?= $form->field($model, 'denied_statuses')->widget(Select2::class, [
                        'data' => $statusesList,
                        'options' => [
                            'placeholder' => 'Select denied statuses...',
                            'multiple' => true,
                        ],
                        'pluginOptions' => ['allowClear' => true],
                    ]) ?>
                    <?= $form->field($model, 'show_results_after_vote')->checkbox() ?>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">Widget Appearance (JSON)</div>
                <div class="card-body">
                    <?= $form->field($model, 'widget_config')->textarea([
                        'rows' => 6,
                        'placeholder' => '{"background":{"color":"#f8f9fa"},"answers":{"colors":["#007bff","#28a745"]}}',
                    ])->hint('JSON configuration for widget styling. Leave empty for defaults.') ?>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
        <?= Html::a('Cancel', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
