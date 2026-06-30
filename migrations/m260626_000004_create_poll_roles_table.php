<?php

use yii\db\Migration;

/**
 * Handles the creation of table `poll_roles`.
 * 
 * @author Andrew Zakharov
 * @since 1.0.0
 */
class m260626_000004_create_poll_roles_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('poll_roles', [
            'id' => $this->primaryKey(),
            'poll_id' => $this->integer()->notNull(),
            'role_id' => $this->integer()->notNull(),
            'subject_id' => $this->integer(),
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        // Foreign key to poll table
        $this->addForeignKey(
            'fk_poll_roles_poll',
            'poll_roles',
            'poll_id',
            'poll',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Performance indexes
        $this->createIndex('idx_poll_roles_poll', 'poll_roles', 'poll_id');
        $this->createIndex('idx_poll_roles_role', 'poll_roles', 'role_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('poll_roles');
    }
}
