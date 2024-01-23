<?php

return [
    'class' => \yii\symfonymailer\Mailer::class,
    'transport' => [
        'scheme' => 'tls',
        'host' => '',
        'username' => '',
        'password' => '',
        'port' => 465,
        'dsn' => 'native://default',
    ],
    'viewPath' => '@common/mail',
    // send all mails to a file by default. You have to set
    // 'useFileTransport' to false and configure transport
    // for the mailer to send real emails.
    'useFileTransport' => false,
];