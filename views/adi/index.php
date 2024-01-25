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
            'options' => ['class' => 'table-responsive'],
            'tableOptions' => ['class' => 'table table-striped table-bordered fs-6'], // Aggiungi qui le classi di Bootstrap
            'columns' => [
                'id',
                'cf',
                'distretto',
                'data_pic:date',
                'inizio:date',
                'fine:date',
                'cartella_aster',
                'cognome',
                'nome',
                'dittaScelta.denominazione',
                [
                    // notificato of type badge success or no
                    'attribute' => 'data_ora_invio',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return $model->data_ora_invio !== null ? ('<span class="badge bg-success">OK</span>') : ('<span class="badge bg-danger">PAI non ancora inviato</span>');
                    },
                    'label' => 'Notifica'
                ],
                // add link to the report
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{report}',
                    'buttons' => [
                        'report' => function ($url, $model) {
                            return Html::a('<i class="fas fa-file-pdf"></i>', $url, [
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


