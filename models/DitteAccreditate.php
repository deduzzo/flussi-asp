<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "ditte_accreditate".
 *
 * @property int $id
 * @property string|null $denominazione
 * @property string|null $email
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
            [['denominazione', 'email'], 'string', 'max' => 100],
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
}
