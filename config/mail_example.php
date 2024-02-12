<?php
$user = $params['adminEmail'];
$password = '';
$host = 'posta.asp.messina.it';

$user = urlencode($user);
$password = urlencode($password); // Codifica la password
return [
    'class' => \yii\symfonymailer\Mailer::class,
    'transport' => [
        "dsn" => "smtp://$user:$password@$host?encryption=tls&auth_mode=login&verify_peer=false",
    ],
    'useFileTransport' => false,
];