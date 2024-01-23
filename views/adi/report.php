<?php

use kartik\file\FileInput;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $pic app\models\AdiPic */

$this->title = 'Adi - Report';
$this->params['breadcrumbs'][] = $this->title;
?>


<div>
    <div class="text-center">
        <?= Html::beginForm() ?>
            <?php if ($pic) Html::hiddenInput('id', $pic->id) ?>
            <?= Html::submitButton('Genera Report PDF', ['class' => 'btn btn-success', 'style' => 'margin-right:30px', 'name' => 'report', 'value' => 'report']) ?>
            <?= Html::submitButton('Invia PAI via mail alla ditta', ['class' => 'btn btn-warning', 'name' => 'notifica', 'value' => 'notifica']) ?>
            <?= "<div style='margin-top:10px'>Data invio ultima mail alla ditta: <span><b>" . ($pic->data_ora_invio !== null ? Yii::$app->formatter->asDatetime($pic->data_ora_invio) : 'Non ancora inviato') . "</b></span></div>" ?>
        <?= Html::endForm() ?>
    </div>
    <?php $form = ActiveForm::begin(); ?>
    <?php
    // optionselect for ditta
    $ditta = \app\models\DitteAccreditate::find()->all();
    $items = \yii\helpers\ArrayHelper::map($ditta, 'id', 'denominazione');
    echo $form->field($pic, 'ditta_scelta')->dropDownList($items, ['prompt' => 'Seleziona la ditta', 'disabled' => true]);
    ?>
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

    <?php ActiveForm::end() ?>
</div>

