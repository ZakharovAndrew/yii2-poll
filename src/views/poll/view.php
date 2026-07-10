<?php

/**
 * @var yii\web\View $this
 * @var \ZakharovAndrew\poll\models\Poll $model
 * @var array $stats
 */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use ZakharovAndrew\poll\models\Poll;

$this->title = $model->question;
$this->params['breadcrumbs'][] = ['label' => 'Polls', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="poll-view">
    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data-confirm' => 'Are you sure you want to delete this poll?',
            'data-method' => 'post',
        ]) ?>
        <?= Html::a('View Results', ['stats', 'id' => $model->id], ['class' => 'btn btn-info']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute' => 'category_id',
                'value' => $model->category ? $model->category->name : '-',
            ],
            'question:ntext',
            [
                'attribute' => 'image_url',
                'format' => 'raw',
                'value' => $model->image_url ? Html::img($model->image_url, ['style' => 'max-width:200px;']) : null,
            ],
            [
                'attribute' => 'image_position',
                'value' => $model->image_position === 'before' ? 'Before question' : 'After question',
            ],
            [
                'attribute' => 'status',
                'value' => Poll::getStatusesList()[$model->status] ?? 'Unknown',
            ],
            'priority',
            [
                'attribute' => 'start_date',
                'format' => 'datetime',
            ],
            [
                'attribute' => 'end_date',
                'format' => 'datetime',
            ],
            [
                'attribute' => 'allowed_statuses',
                'value' => implode(', ', $model->getAllowedStatuses()) ?: 'None',
            ],
            [
                'attribute' => 'denied_statuses',
                'value' => implode(', ', $model->getDeniedStatuses()) ?: 'None',
            ],
            'show_results_after_vote:boolean',
            [
                'attribute' => 'created_by',
                'value' => $model->creator ? ($model->creator->username ?? $model->creator->id) : '-',
            ],
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>

    <h3>Answers</h3>
    <?php if ($model->answers): ?>
        <ul>
            <?php foreach ($model->answers as $answer): ?>
                <li>
                    <?= Html::encode($answer->answer_text) ?>
                    (<?= $answer->getVotesCount() ?> votes)
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No answers defined.</p>
    <?php endif; ?>
</div>
