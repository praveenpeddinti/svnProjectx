<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php'),
    require(__DIR__ . '/../../common/config/translation-en.php')
);

return [
    
    'id' => 'app-console',
      'language' => 'en',
    //'sourceLanguage' => 'fr',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'console\controllers',
    'components' => [
       'i18n' => [
        'translations' => [
            'app*' => [
                'class' => 'yii\i18n\PhpMessageSource',
                'basePath' => __DIR__ . '/../../common/messages',
               // 'sourceLanguage' => 'fr',
                'fileMap' => [
                    'app' => 'translation.php',
                    'app/error' => 'error.php',
                ],
            ],
        ],
    ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
    ],
    'params' => $params,
];
