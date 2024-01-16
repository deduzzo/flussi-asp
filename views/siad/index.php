<?php

use kartik\file\FileInput;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\FileUpload */

$this->title = 'ADI - SIAD';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-about">
    <h1><?= Html::encode($this->title) ?></h1>
    <p>Seleziona l'allegato PDF del PAI da avviare:</p>

    <?php $form = ActiveForm::begin([
        'action' => Url::to(['/siad/index']), // Sostituisci con il tuo controller e la tua action
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

    echo Html::submitButton('Invia', ['class' => 'btn btn-primary']);

    ActiveForm::end(); ?>
</div>
