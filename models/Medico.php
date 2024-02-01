<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "medico".
 *
 * @property string|null $codice_regionale
 * @property string|null $cf
 * @property string|null $nominativo
 * @property string|null $mail
 * @property string|null $telefono
 * @property string|null $distretto
 *
 * @property Assistito[] $assistitiNar
 * @property Assistito[] $assistitiTs
 */
class medico extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'medico';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db2');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['codice_regionale', 'cf', 'nominativo', 'mail', 'telefono', 'distretto'], 'string'],
            [['cf'], 'unique'],
            [['codice_regionale'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'codice_regionale' => 'Codice Regionale',
            'cf' => 'Cf',
            'nominativo' => 'Nominativo',
            'mail' => 'Mail',
            'telefono' => 'Telefono',
            'distretto' => 'Distretto',
        ];
    }

    /**
     * Gets query for [[Assistitos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAssistitiNar()
    {
        return $this->hasMany(Assistito::class, ['codice_regionale_nar' => 'codice_regionale']);
    }

    /**
     * Gets query for [[Assistitos0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAssistitiTs()
    {
        return $this->hasMany(Assistito::class, ['codice_regionale_ts' => 'codice_regionale']);
    }
}
