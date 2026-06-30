<?php

namespace ZakharovAndrew\poll\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Poll answer model.
 *
 * @property int $id
 * @property int $poll_id
 * @property string $answer_text
 * @property int $sort_order
 * @property string $created_at
 *
 * @property Poll $poll
 * @property PollVote[] $votes
 *
 * @author Andrew Zakharov
 * @since 1.0.0
 */
class PollAnswer extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'poll_answer';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['poll_id', 'answer_text'], 'required'],
            [['poll_id', 'sort_order'], 'integer'],
            [['answer_text'], 'string'],
            [['created_at'], 'safe'],
            [['poll_id'], 'exist', 'targetClass' => Poll::class, 'targetAttribute' => 'id'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'poll_id' => 'Poll',
            'answer_text' => 'Answer Text',
            'sort_order' => 'Sort Order',
            'created_at' => 'Created At',
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
     * Gets the votes relation.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVotes()
    {
        return $this->hasMany(PollVote::class, ['answer_id' => 'id']);
    }

    /**
     * Returns the number of votes for this answer.
     *
     * @return int
     */
    public function getVotesCount()
    {
        return $this->getVotes()->count();
    }

    /**
     * Returns the percentage of votes for this answer relative to all votes in the poll.
     *
     * @return float
     */
    public function getVotesPercent()
    {
        $total = $this->poll ? $this->poll->getVotesCount() : 0;
        if ($total === 0) {
            return 0;
        }
        return round(($this->getVotesCount() / $total) * 100, 2);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        // Check if there are votes for this answer
        if ($this->getVotes()->exists()) {
            $this->addError('id', 'Cannot delete an answer that has votes.');
            return false;
        }

        return true;
    }
}
