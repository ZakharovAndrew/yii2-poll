<?php

namespace ZakharovAndrew\poll\controllers\admin;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use ZakharovAndrew\poll\models\Poll;
use ZakharovAndrew\poll\models\PollAnswer;
use ZakharovAndrew\poll\models\PollCategory;
use ZakharovAndrew\poll\models\PollSearch;

/**
 * Admin controller for managing polls.
 *
 * @author Andrew Zakharov
 * @since 1.0.0
 */
class PollController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['admin'], // Adjust to your RBAC role
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all polls.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new PollSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new poll.
     * If creation is successful, redirects to the index page.
     *
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Poll();
        $answers = $this->createEmptyAnswers();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // Save answers
            $this->saveAnswers($model->id, Yii::$app->request->post('PollAnswer', []));
            Yii::$app->session->setFlash('success', 'Poll created successfully.');
            return $this->redirect(['index']);
        }

        return $this->render('create', $this->getViewParams($model, $answers));
    }

    /**
     * Updates an existing poll.
     *
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if poll not found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $answers = $this->getExistingAnswers($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // Save answers
            $this->saveAnswers($model->id, Yii::$app->request->post('PollAnswer', []));
            Yii::$app->session->setFlash('success', 'Poll updated successfully.');
            return $this->redirect(['index']);
        }

        return $this->render('update', $this->getViewParams($model, $answers));
    }

    /**
     * Deletes an existing poll.
     *
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();
        Yii::$app->session->setFlash('success', 'Poll deleted successfully.');
        return $this->redirect(['index']);
    }

    /**
     * Displays poll statistics.
     *
     * @param int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionStats($id)
    {
        $model = $this->findModel($id);
        $stats = $model->getVoteStats();

        return $this->render('stats', [
            'model' => $model,
            'stats' => $stats,
        ]);
    }

    /**
     * Finds the Poll model based on its primary key value.
     *
     * @param int $id
     * @return Poll
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Poll::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested poll does not exist.');
    }

    /**
     * Returns an array of 4 empty PollAnswer objects.
     *
     * @return PollAnswer[]
     */
    protected function createEmptyAnswers()
    {
        $answers = [];
        for ($i = 0; $i < 4; $i++) {
            $answer = new PollAnswer();
            $answer->sort_order = $i;
            $answers[] = $answer;
        }
        return $answers;
    }

    /**
     * Returns existing answers for a poll, or empty placeholders up to 4.
     *
     * @param int $pollId
     * @return PollAnswer[]
     */
    protected function getExistingAnswers($pollId)
    {
        $existing = PollAnswer::find()
            ->where(['poll_id' => $pollId])
            ->orderBy(['sort_order' => SORT_ASC])
            ->all();

        // Ensure we always have 4 slots
        while (count($existing) < 4) {
            $answer = new PollAnswer();
            $answer->sort_order = count($existing);
            $existing[] = $answer;
        }

        return $existing;
    }

    /**
     * Saves answers from POST data.
     *
     * @param int $pollId
     * @param array $answersData Array from POST (PollAnswer[0][answer_text], etc.)
     */
    protected function saveAnswers($pollId, $answersData)
    {
        // Delete existing answers
        PollAnswer::deleteAll(['poll_id' => $pollId]);

        foreach ($answersData as $index => $data) {
            if (empty($data['answer_text'])) {
                continue; // Skip empty answers
            }
            $answer = new PollAnswer();
            $answer->poll_id = $pollId;
            $answer->answer_text = $data['answer_text'];
            $answer->sort_order = $data['sort_order'] ?? $index;
            $answer->save();
        }
    }

    /**
     * Returns common view parameters.
     *
     * @param Poll $model
     * @param PollAnswer[] $answers
     * @return array
     */
    protected function getViewParams($model, $answers)
    {
        return [
            'model' => $model,
            'answers' => $answers,
            'statusesList' => Poll::getStatusesList(),
            'categoriesList' => PollCategory::getActiveList(),
            'imagePositions' => Poll::getImagePositionsList(),
        ];
    }
}
