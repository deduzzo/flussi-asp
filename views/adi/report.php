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
    <?php $form = ActiveForm::begin(); ?>


    <?php
    // optionselect for ditta
    $ditta = \app\models\DitteAccreditate::find()->all();
    $items = \yii\helpers\ArrayHelper::map($ditta, 'id', 'denominazione');
    echo $form->field($pic, 'ditta_scelta')->dropDownList($items, ['prompt' => 'Seleziona la ditta','disabled' => true]);
    ?>
    <div class="form-group">
        <div class="text-center">
            <?= Html::submitButton('Genera Report', ['class' => 'btn btn-success']) ?>
        </div>
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

