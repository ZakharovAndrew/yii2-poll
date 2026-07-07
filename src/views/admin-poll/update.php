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
use yii\helpers\Url;

$this->title = 'Update Poll: ' . $model->question;
$this->params['breadcrumbs'][] = ['label' => 'Polls', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->question, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>

<div class="poll-update">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'answers' => $answers,
        'statusesList' => $statusesList,
        'categoriesList' => $categoriesList,
        'imagePositions' => $imagePositions,
        'actionUrl' => Url::to(['update', 'id' => $model->id]),
    ]) ?>
</div>
