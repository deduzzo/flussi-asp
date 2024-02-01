<?php

return [
    'class' => 'yii\db\Connection',
    // dsn for sqlite
    'dsn' => 'sqlite:'.dirname(__DIR__).'/db/assistiti.db',


    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
