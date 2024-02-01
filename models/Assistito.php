<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "assistito".
 *
 * @property string|null $codice_fiscale
 * @property string|null $codice_regionale_ts
 * @property string|null $codice_regionale_nar
 *
 * @property Medico $medicoNar
 * @property Medico $medicoTs
 */
class Assistito extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'assistito';
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
            [['codice_fiscale', 'codice_regionale_ts', 'codice_regionale_nar'], 'string'],
            [['codice_fiscale'], 'unique'],
            [['medicoNar'], 'exist', 'skipOnError' => true, 'targetClass' => Medico::class, 'targetAttribute' => ['codice_regionale_nar' => 'codice_regionale']],
            [['medicoTs'], 'exist', 'skipOnError' => true, 'targetClass' => Medico::class, 'targetAttribute' => ['codice_regionale_ts' => 'codice_regionale']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'codice_fiscale' => 'Codice Fiscale',
            'medicoTs' => 'Codice Regionale Ts',
            'medicoNar' => 'Codice Regionale Nar',
        ];
    }

    /**
     * Gets query for [[CodiceRegionaleNar]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMedicoNar()
    {
        return $this->hasOne(Medico::class, ['codice_regionale' => 'codice_regionale_nar']);
    }

    /**
     * Gets query for [[CodiceRegionaleTs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMedicoTs()
    {
        return $this->hasOne(Medico::class, ['codice_regionale' => 'codice_regionale_ts']);
    }
}
