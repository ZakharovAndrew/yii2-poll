<?php

use yii\db\Migration;

/**
 * Handles the creation of table `poll_answer`.
 * 
 * @author Andrew Zakharov
 * @since 1.0.0
 */
class m260626_000002_create_poll_answer_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('poll_answer', [
            'id' => $this->primaryKey(),
            'poll_id' => $this->integer()->notNull(),
            'answer_text' => $this->text()->notNull(),
            'sort_order' => $this->integer()->defaultValue(0),
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        // Foreign key to poll table with cascade delete
        $this->addForeignKey(
            'fk_poll_answer_poll',
            'poll_answer',
            'poll_id',
            'poll',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Indexes for performance
        $this->createIndex('idx_poll_answer_poll', 'poll_answer', 'poll_id');
        $this->createIndex('idx_poll_answer_sort', 'poll_answer', 'sort_order');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('poll_answer');
    }
}
