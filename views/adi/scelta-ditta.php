<?php

use app\models\DitteAccreditate;
use kartik\file\FileInput;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $pic app\models\AdiPic */
/* @var $ulterioriAllegati app\models\FileUpload */

$this->title = 'ADI - SIAD';
$this->params['breadcrumbs'][] = "Nuova PIC";
$this->params['breadcrumbs'][] = $this->title;
?>


<div>
        <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <div class="alert alert-info" role="alert">
        Selezionare la scelta dall'utente, ed eventuali note. <br />
        E' possibile inoltre allegare ulteriori documentazione da allegare al PAI trascinando i file nella sezione "Allegati" (solo file .pdf, dimensione massima <?= ini_get('post_max_size') ?>)
    </div>

    <?php
        // optionselect for ditta
        $ditta = DitteAccreditate::find()->where(['attiva' => true])->orderBy('denominazione')->all();
        $items = [];
        foreach ($ditta as $d) {
            /* @var $d \app\models\DitteAccreditate */
            $items[$d->id] = $d->getDescrDitta();
        }
        // div class success
        echo "<div class='alert alert-success' role='alert'>";
        echo $form->field($pic, 'ditta_scelta')->dropDownList($items, ['prompt' => 'Seleziona la ditta', 'class' => 'form-control'])->label("Ditta scelta dall'utente:", ['style' => 'font-weight: bold;']);
        // echo note text area
        echo $form->field($pic, 'note')->textarea(['rows' => 3])->label('Eventuali note da inserire nella comunicazione (in caso di distretto di Messina indicare se NORD o SUD):', ['style' => 'font-weight: bold;']);

        echo $form->field($ulterioriAllegati, 'file')->widget(FileInput::classname(), [
            'options' => ['multiple' => true],
            'pluginOptions' => [
                'allowedFileExtensions' => ['pdf'],
                'dropZoneTitle' => 'Ulteriori file da allegare alla mail (facoltativi)',
                'showCaption' => false,
                'browseLabel' => 'Scegli eventuali allegati (solo .pdf)',
            ]
        ]);
            echo "<div class='text-center'>";
            echo Html::submitButton('Conferma PIC e assegna alla ditta selezionata', ['class' => 'btn btn-success',"id" => "subBtn", "onclick" => "handleClick(this.id)"]);
            echo "</div>";

        echo "</div>";
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
    <?= $form->field($pic, 'id_utente')->hiddenInput()->label(false) ?>
    <?= $form->field($pic, 'inizio')->hiddenInput()->label(false) ?>
    <?= $form->field($pic, 'fine')->hiddenInput()->label(false) ?>
    <?= $form->field($pic, 'fine_reale')->hiddenInput()->label(false) ?>
    <?= $form->field($pic, 'num_contatto')->hiddenInput()->label(false) ?>

    <div class="form-group">
        <fieldset>
            <legend>Sommario</legend>
            <div class="row">
                <div class="col-md-2">
                    <?= $form->field($pic, 'nome')->textInput(['maxlength' => true, 'disabled' => true]) ?>
                </div>
                <div class="col-md-2">
                    <?= $form->field($pic, 'cognome')->textInput(['maxlength' => true, 'disabled' => true]) ?>
                </div>
                <div class="col-md-2">
                    <?= $form->field($pic, 'cf')->textInput(['maxlength' => true, 'disabled' => true]) ?>
                </div>
                <div class="col-md-2">
                    <?= $form->field($pic, 'data_pic')->textInput(['maxlength' => true, 'disabled' => true]) ?>
                </div>
                <div class="col-md-2">
                    <?= $form->field($pic, 'inizio')->textInput(['maxlength' => true, 'disabled' => true]) ?>
                </div>
                <div class="col-md-2">
                    <?= $form->field($pic, 'fine_reale')->textInput(['maxlength' => true, 'disabled' => true]) ?>
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

<script>

</script>