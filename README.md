# Yii2 Poll

[![Latest Stable Version](https://poser.pugx.org/zakharov-andrew/yii2-poll/v/stable)](https://packagist.org/packages/zakharov-andrew/yii2-poll)
[![Total Downloads](https://poser.pugx.org/zakharov-andrew/yii2-poll/downloads)](https://packagist.org/packages/zakharov-andrew/yii2-poll)
[![License](https://poser.pugx.org/zakharov-andrew/yii2-poll/license)](https://packagist.org/packages/zakharov-andrew/yii2-poll)
[![Yii2](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](http://www.yiiframework.com/)

Yii2 module for polls with advanced features: conditional questions, categories, priorities, role/status-based access control, AJAX voting, and customizable appearance.

## 🚀 Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
$ composer require zakharov-andrew/yii2-poll
```
or add

```
"zakharov-andrew/yii2-poll": "*"
```

to the ```require``` section of your ```composer.json``` file.

Subsequently, run

```
./yii migrate/up --migrationPath=@vendor/zakharov-andrew/yii2-poll/migrations
```

in order to create the settings table in your database.

Or add to console config

```php
return [
    // ...
    'controllerMap' => [
        // ...
        'migrate' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationPath' => [
                '@console/migrations', // Default migration folder
                '@vendor/zakharov-andrew/yii2-poll/src/migrations'
            ]
        ]
        // ...
    ]
    // ...
];
```

## ⚙️ Configuration

The module automatically registers itself via the Bootstrap class, so you don't need to add it to the `modules` section unless you want to override default settings.

To customize the module, add it to your `config/web.php`:

```php
'modules' => [
    'poll' => [
        'class' => 'ZakharovAndrew\poll\Module',
        'defaultRotationStrategy' => 'priority', // 'priority', 'random', 'smart'
        'showCategoryInfo' => true,
        'enableLogging' => YII_DEBUG,
        'cacheConfig' => [
            'duration' => 3600,
        ],
        'defaultWidgetConfig' => [
            // ... see default settings in Module.php
        ],
    ],
],
```

## 🛠 Usage

### Basic Widget

Simply place the widget anywhere in your view:

```php
<?= \ZakharovAndrew\poll\widgets\PollWidget::widget() ?>
```

### Widget with Options

```php
<?= \ZakharovAndrew\poll\widgets\PollWidget::widget([
    'categoryId' => 1,                       // Show only polls from this category
    'excludeCategoryId' => 2,                // Exclude a category
    'rotationStrategy' => 'smart',           // Override module default
    'showCategoryInfo' => true,              // Show category badge
    'limit' => 1,                            // Number of polls to display
    'pollId' => 5,                           // Show a specific poll (ignores rotation)
]) ?>
```

### AJAX Voting

Voting is handled via AJAX by default – no page reload required. The widget automatically includes the necessary JavaScript.

## 🌟 Features

- One poll per widget – clean, focused user experience.
- Multiple polls – automatic rotation between polls.
- Up to 4 answers per poll (configurable).
- Image support – attach an image to the question, placed before or after.
- Date range – set start_date and end_date; polls are active only within that period (unlimited if not set).
- Status-based access – allow or deny access based on user status (active, blocked, guest, etc.) using JSON fields allowed_statuses and denied_statuses.
- Role-based access – integrate with zakharov-andrew/yii2-user to restrict polls to specific roles.
- Conditional questions – show follow-up questions based on previous answers.
- Categories – organize polls into groups with icons and colors.
- Priority – assign numeric priority (higher = more important) to control display order.
- Rotation strategies:
- - `priority` – sorted by priority descending.
- - `random` – random selection.
- - `smart` – priority + votes count (fewer votes = higher priority).
- Customizable appearance – background color/image, question styles, answer colors, result bar styles, container styling.
- AJAX voting – seamless user experience.
- Statistics – view vote counts and percentages.
- Unique vote protection – prevents multiple votes per user (by user_id, ip_address, or session_id).
- Snooze – users can postpone a poll (optional, can be implemented).
- Progress indicator – shows how many polls are completed (optional).

## 👥 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

**yii2-poll** it is available under a MIT License. Detailed information can be found in the `LICENSE.md`.
