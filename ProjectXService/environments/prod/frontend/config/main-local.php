<?php

$config = [
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'lEZVlCDuBSjlJM4qzv8W-CZg9ij6lUUG',
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=10.10.73.77;dbname=Techo2_ProjectX',
            'username' => 'root',
            'password' => 'Techo2',
            'charset' => 'utf8',
        ],
        'mongodb' => [
            'class' => 'yii\mongodb\Connection',
            'dsn' => 'mongodb://10.10.73.77:27017/Techo2_ProjectX',
  
    ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            //'viewPath' => '@common/mail',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            //'useFileTransport' => true,
            'useFileTransport'=>false,
           'transport' => [
               'class' => 'Swift_SmtpTransport',
               'host' => 'smtp.gmail.com',
               'username' => 'rockrule.rastogi69@gmail.com',
               'password' => 'cleopatra95432689',
               'port' => '465',
               'encryption' => 'ssl',
           ],
        ],
    ],
];
///test jenkins
if (!YII_ENV_TEST) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs'=>['127.0.0.1', '::1', '10.10.73.21']
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs'=>['127.0.0.1', '::1', '10.10.73.21']
    ];
}

return $config;
