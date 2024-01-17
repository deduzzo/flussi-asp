<?php

namespace app\models;

use Yii;
use yii\debug\panels\EventPanel;
use yii\helpers\Json;

/**
 * This is the model class for table "adi_pic".
 *
 * @property int $id
 * @property string|null $distretto
 * @property string|null $data_pic
 * @property string|null $cartella_aster
 * @property string $cf
 * @property string|null $cognome
 * @property string|null $nome
 * @property string|null $dati_nascita
 * @property string|null $dati_residenza
 * @property string|null $dati_domicilio
 * @property string|null $recapiti
 * @property string|null $medico_curante
 * @property string|null $medico_prescrittore
 * @property string|null $diagnosi
 * @property string|null $piano_terapeutico
 * @property string|null $nome_file
 * @property string|null $data_ora_invio
 * @property int|null $ditta_scelta
 *
 * @property DitteAccreditate $dittaScelta
 */
class AdiPic extends \yii\db\ActiveRecord
{

    const SCENARIO_SCELTA_DITTA = 'scelta_ditta';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'adi_pic';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['data_pic', 'data_ora_invio'], 'safe'],
            [['cf'], 'required'],
            [['piano_terapeutico'], 'string'],
            [['ditta_scelta'], 'integer'],
            [['distretto', 'cartella_aster', 'cf', 'cognome', 'nome', 'dati_nascita', 'dati_domicilio', 'recapiti', 'medico_curante', 'medico_prescrittore', 'nome_file'], 'string', 'max' => 100],
            [['dati_residenza', 'diagnosi'], 'string', 'max' => 200],
            [['ditta_scelta'], 'exist', 'skipOnError' => true, 'targetClass' => DitteAccreditate::class, 'targetAttribute' => ['ditta_scelta' => 'id']],
            [['ditta_scelta'], 'required', 'on' => self::SCENARIO_SCELTA_DITTA],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'distretto' => 'Distretto',
            'data_pic' => 'Data Pic',
            'cartella_aster' => 'Cartella Aster',
            'cf' => 'Cf',
            'cognome' => 'Cognome',
            'nome' => 'Nome',
            'dati_nascita' => 'Dati Nascita',
            'dati_residenza' => 'Dati Residenza',
            'dati_domicilio' => 'Dati Domicilio',
            'recapiti' => 'Recapiti',
            'medico_curante' => 'Medico Curante',
            'medico_prescrittore' => 'Medico Prescrittore',
            'diagnosi' => 'Diagnosi',
            'piano_terapeutico' => 'Piano Terapeutico',
            'nome_file' => 'Nome File',
            'data_ora_invio' => 'Data Ora Invio',
            'ditta_scelta' => 'Ditta Scelta',
        ];
    }

    /**
     * Gets query for [[DittaScelta]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDittaScelta()
    {
        return $this->hasOne(DitteAccreditate::class, ['id' => 'ditta_scelta']);
    }

    public function getPianoTerapeutico()
    {
        $out = Json::decode($this->piano_terapeutico);
        $outString = "";
        foreach ($out as $intervento) {
            $interventoSplitted = explode("\t", $intervento);
            for($i=0; $i<count($interventoSplitted); $i++) {
                if ($i === 0)
                    $outString .= "DA/A: ".str_replace(" ","-",$interventoSplitted[$i]);
                else if ($i === count($interventoSplitted) -1)
                    $outString .= " - FREQUENZA: $interventoSplitted[$i]";
                else
                    $outString .= " - ".$interventoSplitted[$i];
            }
            $outString .= "\n";
        }
        return $outString;
    }
}
