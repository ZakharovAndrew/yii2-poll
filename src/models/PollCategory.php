<?php

namespace ZakharovAndrew\poll\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * Poll category model.
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string|null $icon
 * @property string|null $color
 * @property int $sort_order
 * @property int $status (1=active, 0=inactive)
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Poll[] $polls
 *
 * @author Andrew Zakharov
 * @since 1.0.0
 */
class PollCategory extends ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'poll_category';
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
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['description'], 'string'],
            [['icon'], 'string', 'max' => 100],
            [['color'], 'string', 'max' => 20],
            [['sort_order', 'status'], 'integer'],
            [['status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE]],
            [['color'], 'match', 'pattern' => '/^#[a-f0-9]{6}$/i', 'message' => 'Color must be a valid HEX code (e.g., #ff0000).'],
            [['icon'], 'match', 'pattern' => '/^[a-zA-Z0-9\-_ ]+$/', 'message' => 'Icon must contain only letters, numbers, spaces, dashes, and underscores.'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
            'icon' => 'Icon',
            'color' => 'Color',
            'sort_order' => 'Sort Order',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets the polls relation.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPolls()
    {
        return $this->hasMany(Poll::class, ['category_id' => 'id']);
    }

    /**
     * Gets only active polls in this category.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getActivePolls()
    {
        return $this->getPolls()
            ->where(['status' => Poll::STATUS_ACTIVE])
            ->andWhere(['or',
                ['start_date' => null],
                ['<=', 'start_date', date('Y-m-d H:i:s')]
            ])
            ->andWhere(['or',
                ['end_date' => null],
                ['>=', 'end_date', date('Y-m-d H:i:s')]
            ]);
    }

    /**
     * Returns the number of polls in this category.
     *
     * @return int
     */
    public function getPollsCount()
    {
        return $this->getPolls()->count();
    }

    /**
     * Checks if the category is active.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Returns list of statuses for dropdown.
     *
     * @return array
     */
    public static function getStatusesList()
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
        ];
    }

    /**
     * Returns list of all active categories for dropdown.
     *
     * @return array (id => name)
     */
    public static function getActiveList()
    {
        return self::find()
            ->where(['status' => self::STATUS_ACTIVE])
            ->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])
            ->select(['name', 'id'])
            ->indexBy('id')
            ->column();
    }

    /**
     * Returns full list with labels including color/icon (for admin select).
     *
     * @return array (id => formatted label)
     */
    public static function getFormattedList()
    {
        $categories = self::find()
            ->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])
            ->all();

        $list = [];
        foreach ($categories as $cat) {
            $label = $cat->name;
            if ($cat->icon) {
                $label = "<i class='{$cat->icon}'></i> {$label}";
            }
            if ($cat->color) {
                $label = "<span style='color:{$cat->color}'>{$label}</span>";
            }
            $list[$cat->id] = $label;
        }
        return $list;
    }
}
