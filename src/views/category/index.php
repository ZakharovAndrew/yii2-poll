<?php

/**
 * @var yii\web\View $this
 * @var PollCategory[] $categories
 */

use yii\helpers\Html;
use yii\grid\GridView;
use ZakharovAndrew\poll\models\PollCategory;

$this->title = 'Categories';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="category-index">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><?= Html::encode($this->title) ?></h1>
        <?= Html::a('Create Category', ['create'], ['class' => 'btn btn-success']) ?>
    </div>

    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> Categories help organize polls. You can assign categories to polls when creating or editing them.
    </div>

    <?= GridView::widget([
        'dataProvider' => new \yii\data\ArrayDataProvider([
            'allModels' => $categories,
            'pagination' => false,
        ]),
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'name',
                'format' => 'raw',
                'value' => function ($model) {
                    $color = $model->color ? "style='color: {$model->color}'" : '';
                    $icon = $model->icon ? "<i class='{$model->icon}'></i> " : '';
                    return "<span {$color}>{$icon}{$model->name}</span>";
                }
            ],
            'description:ntext',
            [
                'attribute' => 'icon',
                'value' => function ($model) {
                    return $model->icon ? "<code>{$model->icon}</code>" : '';
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'color',
                'format' => 'raw',
                'value' => function ($model) {
                    if ($model->color) {
                        return "<span style='display:inline-block;width:20px;height:20px;background:{$model->color};border-radius:4px;'></span> {$model->color}";
                    }
                    return '';
                }
            ],
            [
                'attribute' => 'polls_count',
                'label' => 'Polls',
                'value' => function ($model) {
                    return $model->getPolls()->count();
                }
            ],
            [
                'attribute' => 'sort_order',
                'headerOptions' => ['style' => 'width:100px'],
            ],
            [
                'attribute' => 'status',
                'value' => function ($model) {
                    $label = $model->isActive() ? 'Active' : 'Inactive';
                    $class = $model->isActive() ? 'success' : 'secondary';
                    return Html::tag('span', $label, ['class' => 'badge bg-' . $class]);
                },
                'format' => 'raw',
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update} {delete}',
                'headerOptions' => ['style' => 'width:100px; text-align:center;'],
                'contentOptions' => ['style' => 'text-align:center;'],
            ],
        ],
    ]); ?>
</div>
