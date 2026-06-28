<?php

namespace ZakharovAndrew\poll;

use Yii;
use yii\i18n\PhpMessageSource;

/**
 * Poll module for Yii2
 * 
 * Provides advanced polling functionality with conditional questions,
 * categories, priorities, role/status-based access control, and AJAX voting.
 * 
 * @author Andrew Zakharov
 * @since 1.0.0
 */
class Module extends \yii\base\Module
{
    /**
     * Module version
     */
    const VERSION = '1.0.0';
    
    /**
     * @var string Module ID
     */
    public $id = 'poll';
    
    /**
     * @var string Default controller route
     */
    public $defaultRoute = 'default/poll';
    
    /**
     * @var string|null URL prefix (optional)
     */
    public $urlPrefix = null;
    
    /**
     * @var string Default rotation strategy for polls
     * Available: 'priority', 'random', 'smart'
     */
    public $defaultRotationStrategy = 'priority';
    
    /**
     * @var int Default number of polls to display in widget
     */
    public $defaultLimit = 1;
    
    /**
     * @var bool Whether to show category info in the widget
     */
    public $showCategoryInfo = false;
    
    /**
     * @var bool Enable logging of poll actions
     */
    public $enableLogging = false;
    
    /**
     * @var array Cache configuration
     */
    public $cacheConfig = [
        'duration' => 3600, // 1 hour
        'dependency' => null,
    ];
    
    /**
     * @var array Available rotation strategies with labels
     */
    public $availableRotationStrategies = [
        'priority' => 'By priority',
        'random' => 'Random',
        'smart' => 'Smart (priority + votes)',
    ];
    
    /**
     * @var array Default widget appearance settings
     */
    public $defaultWidgetConfig = [
        'background' => [
            'color' => '#f8f9fa',
            'image' => null,
            'position' => 'center',
            'repeat' => 'no-repeat',
        ],
        'question' => [
            'color' => '#333333',
            'size' => '18px',
            'alignment' => 'left',
        ],
        'answers' => [
            'colors' => ['#007bff', '#28a745', '#ffc107', '#dc3545'],
            'hoverColor' => '#0056b3',
            'fontColor' => '#ffffff',
            'fontSize' => '16px',
        ],
        'results' => [
            'barColor' => '#007bff',
            'barHeight' => '25px',
            'showPercentages' => true,
            'showVotesCount' => true,
        ],
        'container' => [
            'padding' => '20px',
            'borderRadius' => '8px',
            'boxShadow' => '0 2px 4px rgba(0,0,0,0.1)',
        ],
    ];
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        
        $this->registerTranslations();
        $this->registerAliases();
        $this->registerDependencies();
        
        // Setup logging if enabled
        if ($this->enableLogging) {
            $this->setupLogging();
        }
        
        Yii::debug('Poll module initialized', __METHOD__);
    }
    
    /**
     * Registers translation messages for the module
     */
    protected function registerTranslations()
    {
        if (!isset(Yii::$app->get('i18n')->translations['poll*'])) {
            Yii::$app->get('i18n')->translations['poll*'] = [
                'class' => PhpMessageSource::class,
                'basePath' => __DIR__ . '/messages',
                'sourceLanguage' => 'en-US',
                'fileMap' => [
                    'poll' => 'poll.php',
                    'poll/category' => 'category.php',
                    'poll/widget' => 'widget.php',
                ],
            ];
        }
    }
    
    /**
     * Registers path aliases for the module
     */
    protected function registerAliases()
    {
        Yii::setAlias('@poll', __DIR__);
        Yii::setAlias('@poll/web', __DIR__ . '/web');
        Yii::setAlias('@poll/assets', __DIR__ . '/assets');
        Yii::setAlias('@poll/messages', __DIR__ . '/messages');
        Yii::setAlias('@poll/migrations', __DIR__ . '/migrations');
        Yii::setAlias('@poll/views', __DIR__ . '/views');
    }
    
    /**
     * Registers services in dependency injection container
     */
    protected function registerDependencies()
    {
        // Register poll service
        Yii::$container->setSingleton('pollService', [
            'class' => 'ZakharovAndrew\poll\services\PollService',
        ]);
        
        // Register category service
        Yii::$container->setSingleton('pollCategoryService', [
            'class' => 'ZakharovAndrew\poll\services\CategoryService',
        ]);
    }
    
    /**
     * Sets up logging configuration for the module
     */
    protected function setupLogging()
    {
        $targets = Yii::$app->getLog()->targets;
        
        // Add file target for poll module
        $targets['poll'] = [
            'class' => 'yii\log\FileTarget',
            'levels' => ['error', 'warning', 'info'],
            'categories' => ['poll*'],
            'logFile' => '@runtime/logs/poll.log',
            'logVars' => ['_GET', '_POST', '_FILES'],
            'maxFileSize' => 1024 * 2,
            'maxLogFiles' => 20,
        ];
        
        Yii::$app->getLog()->targets = $targets;
    }
    
    /**
     * Returns default widget configuration
     * 
     * @return array
     */
    public function getDefaultWidgetConfig()
    {
        return $this->defaultWidgetConfig;
    }
    
    /**
     * Returns available rotation strategies
     * 
     * @return array
     */
    public function getAvailableRotationStrategies()
    {
        return $this->availableRotationStrategies;
    }
    
    /**
     * Checks if a rotation strategy is valid
     * 
     * @param string $strategy
     * @return bool
     */
    public function isValidRotationStrategy($strategy)
    {
        return isset($this->availableRotationStrategies[$strategy]);
    }
    
    /**
     * Translates a message for the poll module
     * 
     * @param string $message
     * @param array $params
     * @param string $category
     * @return string
     */
    public static function t($message, $params = [], $category = 'poll')
    {
        return Yii::t($category, $message, $params);
    }
}
