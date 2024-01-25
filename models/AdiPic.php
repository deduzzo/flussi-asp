<?php

namespace app\models;

use Carbon\Carbon;
use Yii;
use yii\debug\panels\EventPanel;
use yii\helpers\Json;

/**
 * This is the model class for table "adi_pic".
 *
 * @property int $id
 * @property string|null $distretto
 * @property string|null $data_pic
 * @property string|null $inizio
 * @property string|null $fine
 * @property string|null $fine_reale
 * @property string|null $cartella_aster
 * @property int|null $num_contatto
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
 * @property string|null $data_ora_inserimento
 * @property string|null $data_ora_invio
 * @property int|null $ditta_scelta
 * @property string|null $id_utente
 * @property string|null $note
 * @property string|null $motivazione_chiusura
 * @property bool $attivo
 *
 * @property DitteAccreditate $dittaScelta
 */
class AdiPic extends \yii\db\ActiveRecord
{

    const SCENARIO_SCELTA_DITTA = 'scelta_ditta';
    const SCENARIO_PIC_PRESENTE = 'pic_presente';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'adi_pic';
    }

    public static function getTuttiDistretti()
    {
        $distretti = array_unique(self::find()->select('distretto')->column());
        // return map of distretti same key -> value
        return array_combine($distretti, $distretti);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['data_pic', 'inizio', 'fine','fine_reale', 'data_ora_invio','data_ora_inserimento'], 'safe'],
            [['distretto', 'data_pic','inizio','fine','fine_reale', 'cf', 'nome', 'cognome', 'dati_nascita', 'dati_residenza', 'recapiti', 'diagnosi', 'piano_terapeutico'], 'required'],
            [['cf'], 'required'],
            [['piano_terapeutico', 'note','motivazione_chiusura'], 'string'],
            [['attivo'], 'boolean'],
            [['num_contatto', 'ditta_scelta'], 'integer'],
            [['distretto', 'cartella_aster', 'cf', 'cognome', 'nome', 'dati_nascita', 'dati_domicilio', 'recapiti', 'medico_curante', 'medico_prescrittore', 'nome_file'], 'string', 'max' => 100],
            [['dati_residenza'], 'string', 'max' => 200],
            [['diagnosi'], 'string', 'max' => 1000],
            [['id_utente'], 'string', 'max' => 50],
            [['ditta_scelta'], 'exist', 'skipOnError' => true, 'targetClass' => DitteAccreditate::class, 'targetAttribute' => ['ditta_scelta' => 'id']],
            [['ditta_scelta'], 'required', 'on' => [self::SCENARIO_SCELTA_DITTA, self::SCENARIO_PIC_PRESENTE]],
            [['motivazione_chiusura'], 'required', 'on' => self::SCENARIO_PIC_PRESENTE],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '#',
            'distretto' => 'Distretto',
            'data_pic' => 'Data PAI',
            'inizio' => 'Inizio',
            'fine' => 'Fine',
            'cartella_aster' => 'Cartella Aster',
            'num_contatto' => 'Num Contatto',
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
            'piano_terapeutico' => 'Piano Terapeutico (da/a - intervento - frequenza)',
            'nome_file' => 'Nome File',
            'data_ora_inserimento' => 'Data creazione',
            'data_ora_invio' => 'Data Ora Invio',
            'ditta_scelta' => 'Ditta Scelta',
            'id_utente' => 'Id Utente',
            'note' => 'Note',
            'motivazione_chisura' => 'Motivazioni Chiusura',
            'attivo' => 'Stato',
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

    public static function getPianoTerapeutico($pianoTerapeuticoString)
    {
        $outString = "";
        $da = null;
        $a = null;
        if ($pianoTerapeuticoString) {
            foreach ($pianoTerapeuticoString as $intervento) {
                $interventoSplitted = explode("\t", $intervento);
                for ($i = 0; $i < count($interventoSplitted); $i++) {
                    if ($i === 0) {
                        $outString .= "DA/A: " . str_replace(" ", "-", $interventoSplitted[$i]);
                        if (is_null($da) && is_null($a)) {
                            $daA = explode(" ", $interventoSplitted[$i]);
                            // import from carbon
                            $da =$daA[0];
                            $a = $daA[1];
                        }
                    } else if ($i === count($interventoSplitted) - 1)
                        $outString .= " - FREQUENZA: $interventoSplitted[$i]";
                    else
                        $outString .= " - " . $interventoSplitted[$i];
                }
                $outString .= "\n";
            }
        }
        return ['da' => $da, 'a' => $a, 'out' => $outString];
    }
}
