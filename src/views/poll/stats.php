<?php

/**
 * @var yii\web\View $this
 * @var \ZakharovAndrew\poll\models\Poll $model
 * @var array $stats
 */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use ZakharovAndrew\poll\models\Poll;

$this->title = 'Statistics: ' . $model->question;
$this->params['breadcrumbs'][] = ['label' => 'Polls', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->question, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Statistics';
?>

<div class="poll-stats">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Vote Summary</div>
                <div class="card-body">
                    <p><strong>Total votes:</strong> <?= $model->getVotesCount() ?></p>
                    <p><strong>Status:</strong> <?= Poll::getStatusesList()[$model->status] ?? 'Unknown' ?></p>
                    <p><strong>Created:</strong> <?= Yii::$app->formatter->asDatetime($model->created_at) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Answers Distribution</div>
                <div class="card-body">
                    <?php if ($stats): ?>
                        <?php foreach ($stats as $stat): ?>
                            <div class="progress mb-2" style="height: 30px;">
                                <div class="progress-bar" role="progressbar"
                                     style="width: <?= $stat['percent'] ?>%; background-color: <?= $model->getWidgetConfig()['answers']['colors'][array_search($stat['answer']->id, array_keys($stats))] ?? '#007bff' ?>;"
                                     aria-valuenow="<?= $stat['percent'] ?>" aria-valuemin="0" aria-valuemax="100">
                                    <?= Html::encode($stat['answer']->answer_text) ?> (<?= $stat['percent'] ?>%)
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No votes yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Detailed Votes</div>
                <div class="card-body">
                    <?= GridView::widget([
                        'dataProvider' => new \yii\data\ActiveDataProvider([
                            'query' => $model->getVotes()->orderBy(['created_at' => SORT_DESC]),
                            'pagination' => ['pageSize' => 20],
                        ]),
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            [
                                'attribute' => 'user_id',
                                'value' => function ($vote) {
                                    return $vote->user ? ($vote->user->username ?? $vote->user_id) : 'Guest';
                                },
                            ],
                            [
                                'attribute' => 'answer_id',
                                'value' => function ($vote) {
                                    return $vote->answer ? $vote->answer->answer_text : 'Unknown';
                                },
                            ],
                            'ip_address',
                            'created_at:datetime',
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group mt-3">
        <?= Html::a('Export CSV', ['export-stats', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Back to Polls', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>
</div>
