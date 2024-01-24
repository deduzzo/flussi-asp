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
        <h1>Ricerca PAI</h1>
        <?= Html::beginForm("", 'get') ?>
        <div class="row form-group" style="margin-top:20px">
            <!-- form with a text field "codice fiscale" -->
            <div class="col-md-1">
                <?= Html::label('Codice Fiscale', 'cf', ['class' => 'mr-2']) ?>
            </div>
            <div class="col-md-2">
                <?= Html::textInput('cf', '', ['class' => 'form-control','placeholder' => "Codice Fiscale",'id'=> 'cf']) ?>
            </div>
            <div class="col-md-1">
                <?= Html::label('Cartella Aster n°', 'catella_aster', ['class' => 'mr-2']) ?>
            </div>
            <div class="col-md-2">
                <?= Html::textInput('cartella_aster', '', ['class' => 'form-control','placeholder' => "n° cartella aster",'id'=> 'catella_aster']) ?>
            </div>
            <div class="col-md-1">
                <?= Html::label('Cognome', 'cognome', ['class' => 'mr-2']) ?>
            </div>
            <div class="col-md-2">
                <?= Html::textInput('cognome', '', ['class' => 'form-control','placeholder' => "Cognome",'id'=> 'cognome']) ?>
            </div>
            <!-- form with a text field "nome" -->
            <div class="col-md-1">
                <?= Html::label('Nome', 'nome', ['class' => 'mr-2']) ?>
            </div>
            <div class="col-md-2">
                <?= Html::textInput('nome', '', ['class' => 'form-control','placeholder' => "Nome",'id'=> 'nome']) ?>
            </div>
            <!-- form options select for value distretti -->
            <div class="col-md-1">
                <?= Html::label('Distretto', 'distretto', ['class' => 'mr-2']) ?>
            </div>
            <div class="col-md-2">
                <?= Html::dropDownList('distretto', '', \app\models\AdiPic::getTuttiDistretti(), ['class' => 'form-control','prompt' => 'Seleziona distretto','id'=> 'distretto']) ?>
            </div>
            <div class="col-md-12" style="margin-top: 20px;">
                <?= Html::submitButton('Cerca', ['class' => 'btn btn-success']) ?>
            </div>
        </div>
        <?= Html::endForm() ?>
    </div>
</div>



