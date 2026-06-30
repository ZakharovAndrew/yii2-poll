<?php

namespace ZakharovAndrew\poll\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Poll roles junction model.
 *
 * @property int $id
 * @property int $poll_id
 * @property int $role_id
 * @property int $subject_id
 * @property string $created_at
 *
 * @property Poll $poll
 * @property mixed|null $role (dynamic class, usually from user module)
 *
 * @author Andrew Zakharov
 * @since 1.0.0
 */
class PollRoles extends ActiveRecord
{
    /**
     * @var string|null Cached role class name
     */
    protected static $_roleClass;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'poll_roles';
    }

    /**
     * Returns the role class name.
     * Tries to use Role model from zakharov-andrew/yii2-user if available.
     *
     * @return string|null
     */
    public static function getRoleClass()
    {
        if (static::$_roleClass === null) {
            if (class_exists('ZakharovAndrew\user\models\Role')) {
                static::$_roleClass = 'ZakharovAndrew\user\models\Role';
            } else {
                // You can customize this fallback or return null
                static::$_roleClass = null;
            }
        }
        return static::$_roleClass;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['poll_id', 'role_id'], 'required'],
            [['poll_id', 'role_id', 'subject_id'], 'integer'],
            [['created_at'], 'safe'],
            // [['poll_id', 'role_id'], 'unique', 'targetAttribute' => ['poll_id', 'role_id']],
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
            'role_id' => 'Role',
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
     * Gets the role relation (dynamic).
     *
     * @return \yii\db\ActiveQuery|null
     */
    public function getRole()
    {
        $roleClass = static::getRoleClass();
        if ($roleClass === null) {
            return null;
        }
        return $this->hasOne($roleClass, ['id' => 'role_id']);
    }

    /**
     * Checks if role-based access is available.
     *
     * @return bool
     */
    public static function isRoleBasedAccessAvailable()
    {
        return static::getRoleClass() !== null;
    }
}
