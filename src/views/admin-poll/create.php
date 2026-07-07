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

$this->title = 'Create Poll';
$this->params['breadcrumbs'][] = ['label' => 'Polls', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="poll-create">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'answers' => $answers,
        'statusesList' => $statusesList,
        'categoriesList' => $categoriesList,
        'imagePositions' => $imagePositions,
        'actionUrl' => Url::to(['create']),
    ]) ?>
</div>
