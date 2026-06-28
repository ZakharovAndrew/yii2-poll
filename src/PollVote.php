<?php

namespace ZakharovAndrew\poll\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * Poll vote model.
 *
 * @property int $id
 * @property int $poll_id
 * @property int $answer_id
 * @property int|null $user_id
 * @property string|null $ip_address
 * @property string|null $session_id
 * @property string $created_at
 *
 * @property Poll $poll
 * @property PollAnswer $answer
 * @property IdentityInterface|null $user
 *
 * @author Andrew Zakharov
 * @since 1.0.0
 */
class PollVote extends ActiveRecord
{
    /**
     * @var string|null Cached user class name
     */
    protected static $_userClass;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'poll_vote';
    }

    /**
     * Returns the user class name from application configuration.
     *
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public static function getUserClass()
    {
        if (static::$_userClass === null) {
            $class = Yii::$app->user->identityClass;
            if ($class === null) {
                if (class_exists('ZakharovAndrew\user\models\User')) {
                    $class = 'ZakharovAndrew\user\models\User';
                } else {
                    throw new \yii\base\InvalidConfigException('User identity class is not configured.');
                }
            }
            static::$_userClass = $class;
        }
        return static::$_userClass;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $userClass = static::getUserClass();

        return [
            [['poll_id', 'answer_id'], 'required'],
            [['poll_id', 'answer_id', 'user_id'], 'integer'],
            [['ip_address'], 'string', 'max' => 45],
            [['session_id'], 'string', 'max' => 255],
            [['created_at'], 'safe'],
            [['poll_id', 'answer_id'], 'exist', 'targetClass' => Poll::class, 'targetAttribute' => 'id'],
            [['answer_id'], 'exist', 'targetClass' => PollAnswer::class, 'targetAttribute' => 'id'],
            ['user_id', 'exist', 'targetClass' => $userClass, 'targetAttribute' => 'id'],
            // Custom validation: ensure user hasn't voted already (handled by unique indexes, but can add extra check)
            ['poll_id', 'validateUniqueVote'],
        ];
    }

    /**
     * Validates that the user/ip/session hasn't already voted for this poll.
     *
     * @param string $attribute
     */
    public function validateUniqueVote($attribute)
    {
        if ($this->hasErrors()) {
            return;
        }

        $query = self::find()->where(['poll_id' => $this->poll_id]);

        if ($this->user_id !== null) {
            $query->andWhere(['user_id' => $this->user_id]);
        } elseif ($this->ip_address !== null) {
            $query->andWhere(['ip_address' => $this->ip_address]);
        } elseif ($this->session_id !== null) {
            $query->andWhere(['session_id' => $this->session_id]);
        } else {
            return; // No identifier, can't check
        }

        // Exclude current record if updating (should rarely happen)
        if (!$this->isNewRecord) {
            $query->andWhere(['!=', 'id', $this->id]);
        }

        if ($query->exists()) {
            $this->addError($attribute, 'You have already voted in this poll.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'poll_id' => 'Poll',
            'answer_id' => 'Answer',
            'user_id' => 'User',
            'ip_address' => 'IP Address',
            'session_id' => 'Session ID',
            'created_at' => 'Voted At',
        ];
    }

    /**
     * Gets the poll relation.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPoll()
    {
        return $this->hasOne(Poll::class, ['id' => 'poll_id']);
    }

    /**
     * Gets the answer relation.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAnswer()
    {
        return $this->hasOne(PollAnswer::class, ['id' => 'answer_id']);
    }

    /**
     * Gets the user relation.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        $userClass = static::getUserClass();
        return $this->hasOne($userClass, ['id' => 'user_id']);
    }

    /**
     * Checks if a user (or guest) has already voted for a given poll.
     *
     * @param int $pollId
     * @param int|null $userId
     * @param string|null $ip
     * @param string|null $sessionId
     * @return bool
     */
    public static function hasVoted($pollId, $userId = null, $ip = null, $sessionId = null)
    {
        $query = self::find()->where(['poll_id' => $pollId]);

        if ($userId !== null) {
            $query->andWhere(['user_id' => $userId]);
        } elseif ($ip !== null) {
            $query->andWhere(['ip_address' => $ip]);
        } elseif ($sessionId !== null) {
            $query->andWhere(['session_id' => $sessionId]);
        } else {
            return false; // No identifier provided
        }

        return $query->exists();
    }

    /**
     * Returns the vote count for a specific poll.
     *
     * @param int $pollId
     * @return int
     */
    public static function getTotalVotes($pollId)
    {
        return self::find()->where(['poll_id' => $pollId])->count();
    }

    /**
     * Returns the vote count for a specific answer.
     *
     * @param int $answerId
     * @return int
     */
    public static function getVotesForAnswer($answerId)
    {
        return self::find()->where(['answer_id' => $answerId])->count();
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Automatically set IP address if not provided and user is guest
            if ($insert && $this->user_id === null && $this->ip_address === null) {
                $this->ip_address = Yii::$app->request->userIP ?? null;
            }
            // Automatically set session ID if not provided
            if ($insert && $this->session_id === null) {
                $this->session_id = Yii::$app->session->getId();
            }
            return true;
        }
        return false;
    }
}
