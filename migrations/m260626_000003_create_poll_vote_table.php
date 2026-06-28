<?php

use yii\db\Migration;

/**
 * Handles the creation of table `poll_vote`.
 * 
 * @author Andrew Zakharov
 * @since 1.0.0
 */
class m260626_000003_create_poll_vote_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('poll_vote', [
            'id' => $this->primaryKey(),
            'poll_id' => $this->integer()->notNull(),
            'answer_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->null(),
            'ip_address' => $this->string(45)->null(),
            'session_id' => $this->string(255)->null(),
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        // Foreign keys
        $this->addForeignKey(
            'fk_poll_vote_poll',
            'poll_vote',
            'poll_id',
            'poll',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Unique indexes to prevent duplicate votes
        // For authenticated users (user_id is not null)
        $this->createIndex('unique_poll_user_vote', 'poll_vote', ['poll_id', 'user_id'], true);
        // For guest votes by IP
        $this->createIndex('unique_poll_ip_vote', 'poll_vote', ['poll_id', 'ip_address'], true);
        // For guest votes by session (fallback)
        $this->createIndex('unique_poll_session_vote', 'poll_vote', ['poll_id', 'session_id'], true);

        // Performance indexes
        $this->createIndex('idx_poll_vote_poll', 'poll_vote', 'poll_id');
        $this->createIndex('idx_poll_vote_answer', 'poll_vote', 'answer_id');
        $this->createIndex('idx_poll_vote_user', 'poll_vote', 'user_id');
        $this->createIndex('idx_poll_vote_ip', 'poll_vote', 'ip_address');
        $this->createIndex('idx_poll_vote_session', 'poll_vote', 'session_id');
        $this->createIndex('idx_poll_vote_created', 'poll_vote', 'created_at');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('poll_vote');
    }
}
