# yii2-poll

[![Latest Stable Version](https://poser.pugx.org/zakharov-andrew/yii2-poll/v/stable)](https://packagist.org/packages/zakharov-andrew/yii2-poll)
[![Total Downloads](https://poser.pugx.org/zakharov-andrew/yii2-poll/downloads)](https://packagist.org/packages/zakharov-andrew/yii2-poll)
[![License](https://poser.pugx.org/zakharov-andrew/yii2-poll/license)](https://packagist.org/packages/zakharov-andrew/yii2-poll)
[![Yii2](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](http://www.yiiframework.com/)

Yii2 module for polls with advanced features

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
