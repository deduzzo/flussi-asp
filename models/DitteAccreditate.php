<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "ditte_accreditate".
 *
 * @property int $id
 * @property string|null $denominazione
 * @property string|null $email
 * @property string|null $indirizzo_sede
 * @property string|null $recapiti
 * @property int|null $column_name
 * @property bool $attiva
 *
 * @property AdiPic[] $adiPics
 */
class DitteAccreditate extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ditte_accreditate';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['column_name'], 'integer'],
            [['attiva'], 'boolean'],
            [['denominazione', 'email'], 'string', 'max' => 100],
            [['indirizzo_sede', 'recapiti'], 'string', 'max' => 200],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'denominazione' => 'Denominazione',
            'email' => 'Email',
            'indirizzo_sede' => 'Indirizzo Sede',
            'recapiti' => 'Recapiti',
            'column_name' => 'Column Name',
            'attiva' => 'Attiva',
        ];
    }

    /**
     * Gets query for [[AdiPics]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAdiPics()
    {
        return $this->hasMany(AdiPic::class, ['ditta_scelta' => 'id']);
    }

    public function getDescrDitta() {
        return strtoupper($this->denominazione).', '.$this->indirizzo_sede. ' - '.$this->recapiti. ' - '.$this->email;
    }
}
