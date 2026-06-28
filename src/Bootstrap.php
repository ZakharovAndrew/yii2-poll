<?php

namespace ZakharovAndrew\poll;

use Yii;
use yii\base\BootstrapInterface;
use yii\console\Application as ConsoleApplication;
use yii\web\Application as WebApplication;

/**
 * Bootstrap class for automatic module registration
 * 
 * Registers the poll module, URL rules, and console commands.
 * 
 * @author Andrew Zakharov
 * @since 1.0.0
 */
class Bootstrap implements BootstrapInterface
{
    /**
     * {@inheritdoc}
     */
    public function bootstrap($app)
    {
        // Register the module if not already registered
        if (!$app->hasModule('poll')) {
            $app->setModule('poll', [
                'class' => Module::class,
            ]);
        }

        // Register URL rules for web application
        if ($app instanceof WebApplication) {
            $this->registerUrlRules($app);
        }

        // Register console commands for console application
        if ($app instanceof ConsoleApplication) {
            $this->registerConsoleCommands($app);
        }
    }

    /**
     * Registers URL rules for the poll module
     * 
     * @param WebApplication $app
     */
    protected function registerUrlRules(WebApplication $app)
    {
        $rules = [
            // Admin panel routes
            'poll/admin' => 'poll/admin/poll/index',
            'poll/admin/categories' => 'poll/admin/category/index',
            'poll/admin/<controller:\w+>/<action:\w+>' => 'poll/admin/<controller>/<action>',

            // Public routes
            'poll/<id:\d+>' => 'poll/default/poll/view',
            'poll/vote/<id:\d+>' => 'poll/default/poll/vote',
            'poll/results/<id:\d+>' => 'poll/default/poll/results',
            'poll/get-visible-questions' => 'poll/default/poll/get-visible-questions',
        ];

        $app->getUrlManager()->addRules($rules, false);
    }

    /**
     * Registers console commands for the poll module
     * 
     * @param ConsoleApplication $app
     */
    protected function registerConsoleCommands(ConsoleApplication $app)
    {
        $app->controllerMap['poll'] = [
            'class' => 'ZakharovAndrew\poll\console\PollController',
        ];
    }
}
