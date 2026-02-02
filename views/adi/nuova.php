<?php

use kartik\file\FileInput;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\FileUpload */

$this->title = 'ADI - Assistenza domiciliare integrata';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-about">
    <?php $attivo = false; ?>
    <!-- if attivo is true -->
    <?php if ($attivo): ?>
    <h1><?= Html::encode($this->title) ?></h1>
    <p>Seleziona l'allegato PDF del PAI da gestire, oppure creane uno nuovo:</p>

    <?php $form = ActiveForm::begin([
        'action' => Url::to(['/adi/nuova']), // Sostituisci con il tuo controller e la tua action
        'options' => ['enctype' => 'multipart/form-data']
    ]);

    echo '<label class="control-label">Trascina o seleziona il file pdf</label>';
    echo $form->field($model, 'file')->widget(FileInput::classname(), [
        'pluginOptions' => [
            'allowedFileExtensions' => ['pdf'],
            'dropZoneTitle' => 'Trascina il file qui',
            'showCaption' => false,
            'browseLabel' => 'Scegli file',
        ]
    ]);

    echo '<div class="d-flex justify-content-start">'; // Contenitore Flex per i pulsanti
    echo Html::submitButton('Importa i dati del file pdf', ['class' => 'btn btn-success me-2']); // Pulsante esistente
    // text "oppure" with 10 px margin left and right
    echo Html::tag('div', 'oppure', ['class' => 'me-2 ms-2', 'style' => 'margin-top: 10px; margin-bottom: 10px;']);
    echo Html::submitButton('crea nuovo PAI da zero', ['class' => 'btn btn-danger', 'name' => 'nuovo', 'value' => 'true']);
    echo '</div>';

    ActiveForm::end(); ?>
    <?php else: ?>
        <div class="alert alert-warning" role="alert">
            <h4 class="alert-heading">Servizio non attivo</h4>
            <p>Il servizio di gestione dell'ADI non è attualmente attivo. Utilizzare il portale ADI Maggioli per inserire un nuovo pai</p>
        </div>
    <?php endif; ?>
</div>
