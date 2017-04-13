<?php
return [
    'bootstrap' => ['gii'],
    'modules' => [
        'gii' => 'yii\gii\Module',
    ],
    'components' => [
    'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=10.10.73.33;dbname=Techo2_ProjectX',
            'username' => 'root',
            'password' => 'techo2',
            'charset' => 'utf8',
        ],
        'mongodb' => [
            'class' => 'yii\mongodb\Connection',
            'dsn' => 'mongodb://10.10.73.33:27017/Techo2_ProjectX',

        ],
     ]
];
