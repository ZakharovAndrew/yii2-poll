<?php

namespace ZakharovAndrew\poll\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use ZakharovAndrew\poll\models\Poll;
use ZakharovAndrew\poll\models\PollAnswer;
use ZakharovAndrew\poll\models\PollCategory;
use ZakharovAndrew\poll\models\PollSearch;

/**
 * Admin poll controller for managing polls.
 *
 * @author Andrew Zakharov
 * @since 1.0.0
 */
class AdminPollController extends Controller
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
     * Displays a single poll with stats.
     *
     * @param int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $poll = $this->findModel($id);
        $stats = $poll->getVoteStats();

        return $this->render('view', [
            'poll' => $poll,
            'stats' => $stats,
        ]);
    }

    /**
     * Creates a new poll.
     *
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $poll = new Poll();
        // Initialize 4 empty answers by default
        $answers = array_fill(0, 4, new PollAnswer());

        if ($poll->load(Yii::$app->request->post()) && $poll->save()) {
            // Save answers
            $answersData = Yii::$app->request->post('PollAnswer', []);
            foreach ($answersData as $index => $data) {
                if (!empty($data['answer_text'])) {
                    $answer = new PollAnswer();
                    $answer->poll_id = $poll->id;
                    $answer->answer_text = $data['answer_text'];
                    $answer->sort_order = $data['sort_order'] ?? $index;
                    $answer->save();
                }
            }

            Yii::$app->session->setFlash('success', 'Poll created successfully.');
            return $this->redirect(['view', 'id' => $poll->id]);
        }

        return $this->render('form', [
            'poll' => $poll,
            'answers' => $answers,
            'categories' => PollCategory::getActiveList(),
        ]);
    }

    /**
     * Updates an existing poll.
     *
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $poll = $this->findModel($id);
        $answers = $poll->answers;
        // Ensure we have at least 4 slots for UI
        while (count($answers) < 4) {
            $answers[] = new PollAnswer();
        }

        if ($poll->load(Yii::$app->request->post()) && $poll->save()) {
            // Update answers
            $answersData = Yii::$app->request->post('PollAnswer', []);
            $existingIds = [];
            foreach ($answersData as $index => $data) {
                if (!empty($data['id']) && ($answer = PollAnswer::findOne($data['id'])) && $answer->poll_id == $poll->id) {
                    $answer->answer_text = $data['answer_text'];
                    $answer->sort_order = $data['sort_order'] ?? $index;
                    $answer->save();
                    $existingIds[] = $answer->id;
                } elseif (!empty($data['answer_text'])) {
                    $answer = new PollAnswer();
                    $answer->poll_id = $poll->id;
                    $answer->answer_text = $data['answer_text'];
                    $answer->sort_order = $data['sort_order'] ?? $index;
                    $answer->save();
                    $existingIds[] = $answer->id;
                }
            }
            // Delete removed answers (those not in the posted data)
            PollAnswer::deleteAll(['and', ['poll_id' => $poll->id], ['not in', 'id', $existingIds]]);

            Yii::$app->session->setFlash('success', 'Poll updated successfully.');
            return $this->redirect(['view', 'id' => $poll->id]);
        }

        return $this->render('form', [
            'poll' => $poll,
            'answers' => $answers,
            'categories' => PollCategory::getActiveList(),
        ]);
    }

    /**
     * Deletes a poll.
     *
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $poll = $this->findModel($id);

        if ($poll->getVotes()->exists()) {
            Yii::$app->session->setFlash('error', 'Cannot delete a poll that has votes.');
            return $this->redirect(['index']);
        }

        $poll->delete();
        Yii::$app->session->setFlash('success', 'Poll deleted successfully.');
        return $this->redirect(['index']);
    }

    /**
     * Finds the Poll model based on its primary key value.
     *
     * @param int $id
     * @return Poll
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($poll = Poll::findOne($id)) !== null) {
            return $poll;
        }
        throw new NotFoundHttpException('The requested poll does not exist.');
    }
}
