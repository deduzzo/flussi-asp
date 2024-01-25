<?php


use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel app\models\AdiPicSearch */

$this->title = 'Adi';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $form = ActiveForm::begin() ?>

<style>
    .dataTable-table td, .dataTable-table th {
        font-size: 14px;
    }
</style>

<div class="alert alert-primary" role="alert">
    <?php
    echo Html::a('Nuova PIC', ['adi/nuova'], ['class' => 'btn btn-primary']);
    ?>
</div>
<div class="card">
    <div class="card-header">
        Ultimi PAI inseriti
    </div>
    <div class="card-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'pager' => [
                'class' => 'yii\bootstrap5\LinkPager',
                'firstPageLabel' => 'PRIMA',
                'lastPageLabel' => 'ULTIMA',
                'nextPageLabel' => '>>',
                'prevPageLabel' => '<<',
                'linkOptions' => ['class' => 'page-link'],
            ],
            'options' => [
                'tag' => 'div',
                'class' => 'dataTable-wrapper dataTable-loading no-footer sortable searchable fixed-columns',
                'id' => 'datatable',
            ],
            'tableOptions' => [
                'class' => 'table table-striped dataTable-table',
                'id' => 'table1',
            ],
            'columns' => [
                'id',
                [
                    'attribute' => 'distretto',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return Html::tag('strong', Html::encode($model->distretto));
                    },
                ],
                'cf',
                'cognome',
                'nome',
                'data_pic:date',
                'inizio:date',
                'fine:date',
                'cartella_aster',
                [
                        // stato with fa icon
                    'attribute' => 'attivo',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return $model->attivo
                            ? '<span class="badge bg-success">Attivo</span>'
                            : '<span class="badge bg-danger">Chiuso</span>';
                    },
                ],
                [
                    'attribute' => 'dittaScelta.denominazione',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return $model->dittaScelta && $model->dittaScelta->denominazione
                            ? Html::tag('strong', Html::encode($model->dittaScelta->denominazione))
                            : 'N/A';
                    },
                ],
                [
                    // notificato of type badge success or no
                    'attribute' => 'data_ora_invio',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return $model->data_ora_invio !== null ? ('<span class="badge bg-success">OK</span>') : ('<span class="badge bg-danger">Non inviato</span>');
                    },
                    'label' => 'Notifica'
                ],
                // add link to the report
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{report}',
                    'buttons' => [
                        'report' => function ($url, $model) {
                            return Html::a('<i class="fas fa-eye"></i>', $url, [
                                'title' => Yii::t('app', 'Report'),
                                'class' => 'btn btn-sm btn-success',
                                'data-pjax' => '0',
                            ]);
                        }
                    ],
                    'urlCreator' => function ($action, $model, $key, $index) {
                        return Url::to(['adi/report', 'id' => $model->id]);
                    }
                ]
            ],
        ]); ?>
    </div>
</div>


