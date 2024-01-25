<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\AdiPic;

/**
 * AdiPicSearch represents the model behind the search form of `app\models\AdiPic`.
 */
class AdiPicSearch extends AdiPic
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'num_contatto', 'ditta_scelta'], 'integer'],
            [['distretto', 'data_pic', 'inizio', 'fine', 'cartella_aster', 'cf', 'cognome', 'nome', 'dati_nascita', 'dati_residenza', 'dati_domicilio', 'recapiti', 'medico_curante', 'medico_prescrittore', 'diagnosi', 'piano_terapeutico', 'nome_file', 'data_ora_invio', 'id_utente', 'note'], 'safe'],
            [['attivo'], 'boolean'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = AdiPic::find()->innerJoin('ditte_accreditate', 'ditte_accreditate.id = adi_pic.ditta_scelta');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => array('pageSize' => 30),
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'data_pic' => $this->data_pic,
            'inizio' => $this->inizio,
            'fine' => $this->fine,
            'num_contatto' => $this->num_contatto,
            'data_ora_invio' => $this->data_ora_invio,
            'ditta_scelta' => $this->ditta_scelta,
            'attivo' => $this->attivo,
        ]);

        $query->andFilterWhere(['like', 'distretto', $this->distretto])
            ->andFilterWhere(['like', 'cartella_aster', $this->cartella_aster])
            ->andFilterWhere(['like', 'cf', $this->cf])
            ->andFilterWhere(['like', 'cognome', $this->cognome])
            ->andFilterWhere(['like', 'nome', $this->nome])
            ->andFilterWhere(['like', 'dati_nascita', $this->dati_nascita])
            ->andFilterWhere(['like', 'dati_residenza', $this->dati_residenza])
            ->andFilterWhere(['like', 'dati_domicilio', $this->dati_domicilio])
            ->andFilterWhere(['like', 'recapiti', $this->recapiti])
            ->andFilterWhere(['like', 'medico_curante', $this->medico_curante])
            ->andFilterWhere(['like', 'medico_prescrittore', $this->medico_prescrittore])
            ->andFilterWhere(['like', 'diagnosi', $this->diagnosi])
            ->andFilterWhere(['like', 'piano_terapeutico', $this->piano_terapeutico])
            ->andFilterWhere(['like', 'nome_file', $this->nome_file])
            ->andFilterWhere(['like', 'id_utente', $this->id_utente])
            ->andFilterWhere(['like', 'note', $this->note]);

        $query->andFilterWhere(['like', 'dittaScelta.denominazione', $this->ditta_scelta]);

        $dataProvider->sort->attributes['dittaScelta.denominazione'] = [
            'asc' => ['ditte_accreditate.denominazione' => SORT_ASC],
            'desc' => ['ditte_accreditate.denominazione' => SORT_DESC],
        ];


        return $dataProvider;
    }
}
