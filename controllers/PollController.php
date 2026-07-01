<?php

namespace ZakharovAndrew\poll\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use ZakharovAndrew\poll\models\Poll;
use ZakharovAndrew\poll\models\PollVote;
use ZakharovAndrew\poll\services\PollService;

/**
 * Default poll controller for public actions.
 *
 * Handles viewing polls, voting via AJAX, and retrieving results.
 *
 * @author Andrew Zakharov
 * @since 1.0.0
 */
class PollController extends Controller
{
    /**
     * @var PollService Poll service instance
     */
    protected $pollService;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->pollService = Yii::$container->get('pollService');
    }

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
                        'actions' => ['view', 'vote', 'results', 'get-visible-questions'],
                        'roles' => ['?', '@'], // both guests and authenticated users
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'vote' => ['POST'],
                    'get-visible-questions' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Displays a single poll on a dedicated page.
     *
     * @param int $id Poll ID
     * @return string
     * @throws NotFoundHttpException if poll not found or not active
     */
    public function actionView($id)
    {
        $poll = $this->findPoll($id);

        // Check if poll is active and available for current user
        if (!$poll->isActive()) {
            throw new NotFoundHttpException('This poll is not active.');
        }

        if (!$poll->isAvailableForUser(Yii::$app->user->id)) {
            throw new NotFoundHttpException('You do not have access to this poll.');
        }

        // Check if user has already voted
        $hasVoted = PollVote::hasVoted(
            $poll->id,
            Yii::$app->user->id,
            Yii::$app->request->userIP,
            Yii::$app->session->getId()
        );

        // Get vote statistics if user has voted or results are public
        $stats = null;
        if ($hasVoted || $poll->show_results_after_vote) {
            $stats = $poll->getVoteStats();
        }

        return $this->render('view', [
            'poll' => $poll,
            'hasVoted' => $hasVoted,
            'stats' => $stats,
        ]);
    }

    /**
     * Handles voting via AJAX.
     *
     * @param int $id Poll ID
     * @return array JSON response
     * @throws NotFoundHttpException if poll not found
     */
    public function actionVote($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $poll = $this->findPoll($id);

        // Check if poll is active and available
        if (!$poll->isActive()) {
            return ['success' => false, 'message' => 'This poll is no longer active.'];
        }

        if (!$poll->isAvailableForUser(Yii::$app->user->id)) {
            return ['success' => false, 'message' => 'You do not have permission to vote on this poll.'];
        }

        // Check if user has already voted
        if (PollVote::hasVoted(
            $poll->id,
            Yii::$app->user->id,
            Yii::$app->request->userIP,
            Yii::$app->session->getId()
        )) {
            return ['success' => false, 'message' => 'You have already voted in this poll.'];
        }

        $answerId = Yii::$app->request->post('answer_id');
        if (!$answerId) {
            return ['success' => false, 'message' => 'No answer selected.'];
        }

        // Validate that the answer belongs to this poll
        $answer = \ZakharovAndrew\poll\models\PollAnswer::findOne(['id' => $answerId, 'poll_id' => $poll->id]);
        if (!$answer) {
            return ['success' => false, 'message' => 'Invalid answer.'];
        }

        // Save vote
        $vote = new PollVote();
        $vote->poll_id = $poll->id;
        $vote->answer_id = $answerId;
        $vote->user_id = Yii::$app->user->id;
        $vote->ip_address = Yii::$app->request->userIP;
        $vote->session_id = Yii::$app->session->getId();

        if (!$vote->save()) {
            return ['success' => false, 'message' => 'Failed to save vote: ' . implode(', ', $vote->getFirstErrors())];
        }

        // Get updated statistics
        $stats = $poll->getVoteStats();

        // render the widget partial
        $html = $this->renderPartial('_poll_result', [
            'poll' => $poll,
            'stats' => $stats,
            'hasVoted' => true,
        ]);

        return [
            'success' => true,
            'message' => 'Your vote has been recorded.',
            'html' => $html,
            'stats' => $stats,
        ];
    }

    /**
     * Displays poll results (optionally without voting).
     *
     * @param int $id Poll ID
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionResults($id)
    {
        $poll = $this->findPoll($id);

        if (!$poll->isActive() && $poll->status !== Poll::STATUS_CLOSED) {
            throw new NotFoundHttpException('Poll not found.');
        }

        $stats = $poll->getVoteStats();

        return $this->render('results', [
            'poll' => $poll,
            'stats' => $stats,
        ]);
    }

    /**
     * Returns visible questions based on previous answers (for conditional logic).
     * Used via AJAX.
     *
     * @return array JSON response
     */
    public function actionGetVisibleQuestions()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $pollId = Yii::$app->request->post('poll_id');
        $answers = Yii::$app->request->post('answers', []);

        if (!$pollId) {
            return ['success' => false, 'message' => 'Poll ID required.'];
        }

        $poll = $this->findPoll($pollId);
        if (!$poll->isActive()) {
            return ['success' => false, 'message' => 'Poll not active.'];
        }

        // Get visible question IDs based on answers
        $visibleQuestions = $poll->getVisibleQuestions($answers);

        return [
            'success' => true,
            'visibleQuestions' => array_map(function($q) { return $q->id; }, $visibleQuestions),
        ];
    }

    /**
     * Finds the Poll model based on its primary key value.
     *
     * @param int $id
     * @return Poll
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findPoll($id)
    {
        $poll = Poll::findOne($id);
        if ($poll === null) {
            throw new NotFoundHttpException('The requested poll does not exist.');
        }
        return $poll;
    }
}
