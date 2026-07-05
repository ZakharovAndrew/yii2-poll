<?php

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var \ZakharovAndrew\poll\models\PollSearch $searchModel
 */

use yii\helpers\Html;
use yii\grid\GridView;
use ZakharovAndrew\poll\models\Poll;
use ZakharovAndrew\poll\models\PollCategory;

$this->title = 'Polls';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="poll-index">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><?= Html::encode($this->title) ?></h1>
        <?= Html::a('Create Poll', ['create'], ['class' => 'btn btn-success']) ?>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'id',
                'headerOptions' => ['style' => 'width:80px'],
            ],
            [
                'attribute' => 'category_id',
                'value' => function ($model) {
                    return $model->category ? $model->category->name : '-';
                },
                'filter' => PollCategory::getActiveList(),
                'format' => 'raw',
            ],
            [
                'attribute' => 'question',
                'value' => function ($model) {
                    return Html::encode(mb_substr($model->question, 0, 60) . (mb_strlen($model->question) > 60 ? '…' : ''));
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'status',
                'value' => function ($model) {
                    $statuses = Poll::getStatusesList();
                    $label = $statuses[$model->status] ?? 'Unknown';
                    $class = '';
                    if ($model->status == Poll::STATUS_ACTIVE) {
                        $class = 'success';
                    } elseif ($model->status == Poll::STATUS_INACTIVE) {
                        $class = 'secondary';
                    } elseif ($model->status == Poll::STATUS_CLOSED) {
                        $class = 'danger';
                    }
                    return Html::tag('span', $label, ['class' => 'badge bg-' . $class]);
                },
                'filter' => Poll::getStatusesList(),
                'format' => 'raw',
            ],
            [
                'attribute' => 'priority',
                'headerOptions' => ['style' => 'width:100px'],
            ],
            [
                'attribute' => 'start_date',
                'format' => 'datetime',
                'filter' => false,
            ],
            [
                'attribute' => 'end_date',
                'format' => 'datetime',
                'filter' => false,
            ],
            [
                'attribute' => 'created_by',
                'value' => function ($model) {
                    if ($model->creator) {
                        // Adjust attribute name according to your user model (e.g., username, fullname)
                        return Html::encode($model->creator->username ?? $model->creator->id);
                    }
                    return '-';
                },
                'filter' => false, // You can implement a dropdown with users if needed
                'format' => 'raw',
            ],
            [
                'attribute' => 'created_at',
                'format' => 'datetime',
                'filter' => false,
                'headerOptions' => ['style' => 'width:180px'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete} {stats}',
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-eye"></i>', $url, [
                            'class' => 'btn btn-sm btn-outline-primary',
                            'title' => 'View',
                        ]);
                    },
                    'update' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-edit"></i>', $url, [
                            'class' => 'btn btn-sm btn-outline-secondary',
                            'title' => 'Update',
                        ]);
                    },
                    'delete' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-trash"></i>', $url, [
                            'class' => 'btn btn-sm btn-outline-danger',
                            'title' => 'Delete',
                            'data-confirm' => 'Are you sure you want to delete this poll?',
                            'data-method' => 'post',
                        ]);
                    },
                    'stats' => function ($url, $model, $key) {
                        return Html::a('<i class="fas fa-chart-bar"></i>', ['stats', 'id' => $model->id], [
                            'class' => 'btn btn-sm btn-outline-info',
                            'title' => 'Statistics',
                        ]);
                    },
                ],
                'headerOptions' => ['style' => 'width:150px; text-align:center;'],
                'contentOptions' => ['style' => 'text-align:center;'],
            ],
        ],
    ]); ?>
</div>
