<?php

use kartik\file\FileInput;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $pic app\models\AdiPic */

$this->title = 'Adi - Cerca PAI';
$this->params['breadcrumbs'][] = $this->title;
?>


<div class="d-flex justify-content-center">
    <div class="form-group text-center">
        <h1>Cerca PAI attivo</h1>
        <!-- form with a text field "codice fiscale" -->
        <?= Html::beginForm("", 'get') ?>
        <?= Html::textInput('cf', '', ['class' => 'form-control mr-2', 'placeholder' => 'Codice Fiscale', 'style' => 'width:400px; display: inline-block;']) ?>
        <?= Html::submitButton('Cerca', ['class' => 'btn btn-success']) ?>
        <?= Html::endForm() ?>
    </div>
</div>



