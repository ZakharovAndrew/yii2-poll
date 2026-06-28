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

Widget with Options

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

## 👥 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

**yii2-poll** it is available under a MIT License. Detailed information can be found in the `LICENSE.md`.
