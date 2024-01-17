<?php

use kartik\file\FileInput;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $pic app\models\AdiPic */

$this->title = 'ADI - SIAD';
$this->params['breadcrumbs'][] = "Nuov";
$this->params['breadcrumbs'][] = $this->title;
?>


<div>
    <?php $form = ActiveForm::begin(); ?>

    <div class="alert alert-info" role="alert">
        Selezionare la ditta preselta dall'utente:
    </div>

    <?php
        // optionselect for ditta
        $ditta = \app\models\DitteAccreditate::find()->all();
        $items = \yii\helpers\ArrayHelper::map($ditta, 'id', 'denominazione');
        echo $form->field($pic, 'ditta_scelta')->dropDownList($items, ['prompt' => 'Seleziona la ditta']);
    ?>
    <?= $form->field($pic, 'nome')->hiddenInput()->label(false) ?>
    <?= $form->field($pic, 'cognome')->hiddenInput()->label(false) ?>
    <?= $form->field($pic, 'cf')->hiddenInput()->label(false) ?>
    <?= $form->field($pic, 'diagnosi')->hiddenInput()->label(false) ?>
    <?= $form->field($pic, 'piano_terapeutico')->hiddenInput()->label(false) ?>
    <?= $form->field($pic, 'distretto')->hiddenInput()->label(false) ?>
    <?= $form->field($pic, 'data_pic')->hiddenInput()->label(false) ?>
    <?= $form->field($pic, 'cartella_aster')->hiddenInput()->label(false) ?>
    <?= $form->field($pic, 'dati_nascita')->hiddenInput()->label(false) ?>
    <?= $form->field($pic, 'dati_residenza')->hiddenInput()->label(false) ?>
    <?= $form->field($pic, 'dati_domicilio')->hiddenInput()->label(false) ?>
    <?= $form->field($pic, 'recapiti')->hiddenInput()->label(false) ?>
    <?= $form->field($pic, 'medico_curante')->hiddenInput()->label(false) ?>
    <?= $form->field($pic, 'medico_prescrittore')->hiddenInput()->label(false) ?>
    <?= $form->field($pic, 'diagnosi')->hiddenInput()->label(false) ?>
    <?= $form->field($pic, 'nome_file')->hiddenInput()->label(false) ?>

    <div class="form-group">
        <fieldset>
            <legend>Sommario</legend>
            <div class="row">
                <div class="col-md-3">
                    <?= $form->field($pic, 'nome')->textInput(['maxlength' => true, 'disabled' => true]) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($pic, 'cognome')->textInput(['maxlength' => true, 'disabled' => true]) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($pic, 'cf')->textInput(['maxlength' => true, 'disabled' => true]) ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($pic, 'diagnosi')->textarea(['rows' => 2, 'disabled' => true]) ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($pic, 'piano_terapeutico')->textarea(['rows' => 8, 'disabled' => true]) ?>
                </div>
            </div>
        </fieldset>
    </div>

    <div class="text-center">
        <?= Html::submitButton('Conferma PIC e assegna alla ditta selezionata', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end() ?>
</div>

