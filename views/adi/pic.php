<?php

use kartik\file\FileInput;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $pic app\models\AdiPic */

$this->title = 'ADI - Nuova presa in carico';
$this->params['breadcrumbs'][] = $this->title;
?>


<div>
    <?php $form = ActiveForm::begin([
        'action' => Url::to(['/adi/scelta-ditta']), // Imposta l'azione del form
        // Altri parametri del form, se necessari...
    ]); ?>

    <!-- show info alert with text "verificare i dati prima di inviare il pic" -->
    <div class="alert alert-info" role="alert">
        Si prega di verificare i dati prima di inviare la presa in carico.
    </div>
    <?= $form->field($pic, 'nome_file')->hiddenInput()->label(false) ?>
    <!-- fieldset with text dati pic -->
    <div class="form-group">
        <fieldset>
            <legend>Dati PIC</legend>
            <div class="row">
                <div class="col-md-2">
                    <?= $form->field($pic, 'distretto')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-2">
                    <?= $form->field($pic, 'data_pic')->textInput(['type' => 'date', 'maxlength' => true]) ?>
                </div>
                <div class="col-md-2">
                    <?= $form->field($pic, 'cartella_aster')->textInput(['maxlength' => true]) ?>
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>Dati Anagrafici</legend>
            <div class="row">
                <div class="col-md-3">
                    <?= $form->field($pic, 'nome')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($pic, 'cognome')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($pic, 'cf')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($pic, 'dati_nascita')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($pic, 'dati_residenza')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($pic, 'dati_domicilio')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($pic, 'recapiti')->textInput(['maxlength' => true]) ?>
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>Dati Sanitari</legend>
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($pic, 'medico_prescrittore')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($pic, 'medico_curante')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($pic, 'diagnosi')->textarea(['rows' => 2]) ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($pic, 'piano_terapeutico')->textarea(['rows' => 8, 'value' => $pic->getPianoTerapeutico()]) ?>
                </div>
            </div>
        </fieldset>
    </div>
    <div class="text-center"> <!-- Aggiunta di una classe contenitore con allineamento al centro -->
        <?= Html::submitButton('Conferma dati e vai alla scelta ditta', ['class' => 'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end() ?>
</div>
