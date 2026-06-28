<?php

namespace ZakharovAndrew\poll\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\db\Expression;
use yii\web\IdentityInterface;

/**
 * Poll model.
 *
 * @property int $id
 * @property int|null $category_id
 * @property string $question
 * @property string|null $image_url
 * @property string $image_position ('before' or 'after')
 * @property int $status (1=active, 0=inactive, 2=closed)
 * @property int $priority
 * @property string|null $start_date
 * @property string|null $end_date
 * @property string|null $allowed_statuses (JSON)
 * @property string|null $denied_statuses (JSON)
 * @property string|null $widget_config (JSON)
 * @property bool $show_results_after_vote
 * @property int|null $created_by
 * @property string $created_at
 * @property string $updated_at
 *
 * @property PollCategory $category
 * @property IdentityInterface|null $creator
 * @property PollAnswer[] $answers
 * @property PollVote[] $votes
 * @property PollConditionalRule[] $conditionalRules
 *
 * @author Andrew Zakharov
 * @since 1.0.0
 */
class Poll extends ActiveRecord
{
    // Status constants
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_CLOSED = 2;

    // Image position constants
    const IMAGE_POSITION_BEFORE = 'before';
    const IMAGE_POSITION_AFTER = 'after';

    /**
     * @var string|null Cached user class name
     */
    protected static $_userClass;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'poll';
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
                // Fallback to the module's user model if available
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
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
            ],
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => null,
                // If user is not logged in, set to null instead of throwing error
                'defaultValue' => null,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $userClass = static::getUserClass();

        return [
            [['question'], 'required'],
            [['question'], 'string'],
            [['category_id', 'status', 'priority', 'created_by'], 'integer'],
            [['start_date', 'end_date'], 'safe'],
            [['image_url'], 'string', 'max' => 255],
            [['image_position'], 'in', 'range' => [self::IMAGE_POSITION_BEFORE, self::IMAGE_POSITION_AFTER]],
            [['status'], 'in', 'range' => [self::STATUS_INACTIVE, self::STATUS_ACTIVE, self::STATUS_CLOSED]],
            [['allowed_statuses', 'denied_statuses', 'widget_config'], 'safe'],
            [['show_results_after_vote'], 'boolean'],
            [['allowed_statuses', 'denied_statuses'], 'validateStatuses'],
            ['end_date', 'validateEndDate'],
            ['created_by', 'exist', 'targetClass' => $userClass, 'targetAttribute' => 'id'],
        ];
    }

    /**
     * Validates statuses JSON fields.
     *
     * @param string $attribute
     */
    public function validateStatuses($attribute)
    {
        $value = $this->$attribute;
        if (empty($value)) {
            return;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->addError($attribute, 'Invalid JSON format.');
                return;
            }
            $value = $decoded;
        }

        if (!is_array($value)) {
            $this->addError($attribute, 'Must be an array.');
            return;
        }

        foreach ($value as $item) {
            if (!is_string($item)) {
                $this->addError($attribute, 'Each status must be a string.');
                return;
            }
        }
    }

    /**
     * Validates that end_date is after start_date if both are set.
     *
     * @param string $attribute
     */
    public function validateEndDate($attribute)
    {
        if ($this->start_date && $this->end_date) {
            $start = strtotime($this->start_date);
            $end = strtotime($this->end_date);
            if ($end <= $start) {
                $this->addError($attribute, 'End date must be after start date.');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'category_id' => 'Category',
            'question' => 'Question',
            'image_url' => 'Image',
            'image_position' => 'Image Position',
            'status' => 'Status',
            'priority' => 'Priority',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'allowed_statuses' => 'Allowed Statuses',
            'denied_statuses' => 'Denied Statuses',
            'widget_config' => 'Widget Configuration',
            'show_results_after_vote' => 'Show Results After Vote',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets the category relation.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(PollCategory::class, ['id' => 'category_id']);
    }

    /**
     * Gets the creator relation.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreator()
    {
        $userClass = static::getUserClass();
        return $this->hasOne($userClass, ['id' => 'created_by']);
    }

    /**
     * Gets the answers relation.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAnswers()
    {
        return $this->hasMany(PollAnswer::class, ['poll_id' => 'id'])
            ->orderBy(['sort_order' => SORT_ASC]);
    }

    /**
     * Gets the votes relation.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVotes()
    {
        return $this->hasMany(PollVote::class, ['poll_id' => 'id']);
    }

    /**
     * Gets the conditional rules where this poll is the parent.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getConditionalRules()
    {
        return $this->hasMany(PollConditionalRule::class, ['poll_id' => 'id']);
    }

    /**
     * Gets the count of votes for this poll.
     *
     * @return int
     */
    public function getVotesCount()
    {
        return $this->getVotes()->count();
    }

    /**
     * Decodes a JSON statuses field into an array.
     *
     * @param string|null $value
     * @return array
     */
    protected function decodeStatuses($value)
    {
        if (empty($value)) {
            return [];
        }
        if (is_array($value)) {
            return $value;
        }
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Returns allowed statuses as array.
     *
     * @return array
     */
    public function getAllowedStatuses()
    {
        return $this->decodeStatuses($this->allowed_statuses);
    }

    /**
     * Returns denied statuses as array.
     *
     * @return array
     */
    public function getDeniedStatuses()
    {
        return $this->decodeStatuses($this->denied_statuses);
    }

    /**
     * Sets allowed statuses from array.
     *
     * @param array $statuses
     */
    public function setAllowedStatuses($statuses)
    {
        $this->allowed_statuses = empty($statuses) ? null : json_encode(array_values($statuses));
    }

    /**
     * Sets denied statuses from array.
     *
     * @param array $statuses
     */
    public function setDeniedStatuses($statuses)
    {
        $this->denied_statuses = empty($statuses) ? null : json_encode(array_values($statuses));
    }

    /**
     * Returns widget configuration as array.
     *
     * @return array
     */
    public function getWidgetConfig()
    {
        if (empty($this->widget_config)) {
            return Yii::$app->getModule('poll')->getDefaultWidgetConfig();
        }
        $config = json_decode($this->widget_config, true);
        $default = Yii::$app->getModule('poll')->getDefaultWidgetConfig();
        return is_array($config) ? array_merge($default, $config) : $default;
    }

    /**
     * Sets widget configuration from array.
     *
     * @param array $config
     */
    public function setWidgetConfig($config)
    {
        $this->widget_config = json_encode($config);
    }

    /**
     * Checks if the poll is currently active (status + dates).
     *
     * @return bool
     */
    public function isActive()
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }

        $now = new \DateTime();

        if ($this->start_date !== null) {
            $start = new \DateTime($this->start_date);
            if ($now < $start) {
                return false;
            }
        }

        if ($this->end_date !== null) {
            $end = new \DateTime($this->end_date);
            if ($now > $end) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if the poll is available for a given user.
     *
     * @param int|null $userId
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    public function isAvailableForUser($userId = null)
    {
        // If not logged in, check guest access
        if ($userId === null) {
            return $this->isAvailableForGuest();
        }

        // Get user model using dynamic class
        $userClass = static::getUserClass();
        $user = $userClass::findOne($userId);
        if (!$user) {
            return false;
        }

        // Check status-based access
        $userStatus = $this->getUserStatusKey($user);
        $allowed = $this->getAllowedStatuses();
        $denied = $this->getDeniedStatuses();

        // Deny rules have priority
        if (!empty($denied) && in_array($userStatus, $denied)) {
            return false;
        }

        // If allow list is specified, user must be in it
        if (!empty($allowed)) {
            return in_array($userStatus, $allowed);
        }

        // Check role-based access (if the user class supports roles)
        if ($this->isRoleBasedAccessSupported()) {
            return $this->checkRoleAccess($user);
        }

        // No restrictions
        return true;
    }

    /**
     * Checks access for guest users.
     *
     * @return bool
     */
    protected function isAvailableForGuest()
    {
        $allowed = $this->getAllowedStatuses();
        $denied = $this->getDeniedStatuses();

        if (!empty($denied) && in_array('guest', $denied)) {
            return false;
        }

        if (empty($allowed)) {
            return true;
        }

        return in_array('guest', $allowed);
    }

    /**
     * Gets the status key for a user.
     * Tries to map known status constants, otherwise uses the raw status value.
     *
     * @param IdentityInterface $user
     * @return string
     */
    protected function getUserStatusKey($user)
    {
        // If user is from zakharov-andrew/yii2-user, use its constants
        if (class_exists('ZakharovAndrew\user\models\User') && $user instanceof \ZakharovAndrew\user\models\User) {
            $statusMap = [
                \ZakharovAndrew\user\models\User::STATUS_ACTIVE => 'active',
                \ZakharovAndrew\user\models\User::STATUS_INACTIVE => 'inactive',
                \ZakharovAndrew\user\models\User::STATUS_BLOCKED => 'blocked',
            ];
            if (isset($user->status) && isset($statusMap[$user->status])) {
                return $statusMap[$user->status];
            }
        }

        // Fallback: cast status to string
        return (string) $user->status;
    }

    /**
     * Checks if role-based access is available.
     *
     * @return bool
     */
    protected function isRoleBasedAccessSupported()
    {
        // Check if the user module is available and the user class has getRoles method
        return class_exists('ZakharovAndrew\user\models\Role') &&
               method_exists(static::getUserClass(), 'getRoles');
    }

    /**
     * Checks role-based access using zakharov-andrew/yii2-user.
     *
     * @param IdentityInterface $user
     * @return bool
     */
    protected function checkRoleAccess($user)
    {
        // Get roles assigned to this poll (stored in poll_roles table)
        $pollRoles = PollRoles::find()
            ->where(['poll_id' => $this->id])
            ->select('role_id')
            ->column();

        if (empty($pollRoles)) {
            return true; // No role restrictions
        }

        // Get user's role IDs
        $userRoles = $user->getRoles(); // Assumes method exists
        $userRoleIds = array_keys($userRoles);

        return !empty(array_intersect($userRoleIds, $pollRoles));
    }

    /**
     * Returns the number of votes for each answer.
     *
     * @return array ['answer_id' => count]
     */
    public function getAnswerVoteCounts()
    {
        return PollVote::find()
            ->select(['answer_id', 'COUNT(*) as count'])
            ->where(['poll_id' => $this->id])
            ->groupBy('answer_id')
            ->indexBy('answer_id')
            ->column();
    }

    /**
     * Returns total votes count with percentages for each answer.
     *
     * @return array
     */
    public function getVoteStats()
    {
        $counts = $this->getAnswerVoteCounts();
        $total = array_sum($counts);

        $stats = [];
        foreach ($this->answers as $answer) {
            $count = $counts[$answer->id] ?? 0;
            $stats[$answer->id] = [
                'answer' => $answer,
                'count' => $count,
                'percent' => $total > 0 ? round(($count / $total) * 100, 2) : 0,
            ];
        }

        return $stats;
    }

    /**
     * Returns visible questions based on user's previous answers.
     * Since this poll is a single question, it always returns itself.
     * But for multi-question polls, this would be overridden.
     *
     * @param array $answers (question_id => answer_id)
     * @return Poll[]|array
     */
    public function getVisibleQuestions($answers = [])
    {
        // For now, just return this poll itself.
        return [$this];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Auto-close if end_date is passed
            if ($this->status === self::STATUS_ACTIVE && $this->end_date) {
                $now = new \DateTime();
                $end = new \DateTime($this->end_date);
                if ($now > $end) {
                    $this->status = self::STATUS_CLOSED;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Returns list of statuses for dropdown.
     *
     * @return array
     */
    public static function getStatusesList()
    {
        return [
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_CLOSED => 'Closed',
        ];
    }

    /**
     * Returns list of image positions for dropdown.
     *
     * @return array
     */
    public static function getImagePositionsList()
    {
        return [
            self::IMAGE_POSITION_BEFORE => 'Before question',
            self::IMAGE_POSITION_AFTER => 'After question',
        ];
    }
}
