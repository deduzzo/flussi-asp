<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "utenti".
 *
 * @property int $id
 * @property string|null $username
 * @property string|null $password
 * @property string|null $distretto
 * @property bool $attivo
 */
class Utenti extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'utenti';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['attivo'], 'boolean'],
            [['username', 'password', 'distretto'], 'string', 'max' => 100],
            [['username'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'password' => 'Password',
            'distretto' => 'Distretto',
            'attivo' => 'Attivo',
        ];
    }
}
