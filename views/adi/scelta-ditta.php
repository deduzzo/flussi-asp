<?php

use app\models\Assistito;
use app\models\DitteAccreditate;
use kartik\file\FileInput;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $pic app\models\AdiPic */
/* @var $picPresente app\models\AdiPic */
/* @var $ulterioriAllegati app\models\FileUpload */


$this->title = 'ADI - SIAD';
$this->params['breadcrumbs'][] = "Nuova PIC";
$this->params['breadcrumbs'][] = $this->title;
?>


<div>
    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <div class="alert alert-info" role="alert">
        Selezionare la scelta dall'utente, ed eventuali note. <br/>
        E' possibile inoltre allegare ulteriori documentazione da allegare al PAI trascinando i file nella sezione
        "Allegati" (solo file .pdf, dimensione massima <?= ini_get('post_max_size') ?>)
    </div>
    <?php
    // if scenario is pic presente
    if ($pic->scenario === $pic::SCENARIO_PIC_PRESENTE && $picPresente) {
        // div class success
        echo "<div class='alert alert-danger' role='alert'>";
        echo "<h4 class='alert-heading'>ATTENZIONE!! PAI attivo già presente nel sistema!</h4>";
        echo "<p><b>Il paziente ha già un PAI attivo (inizio " . Yii::$app->formatter->asDate($picPresente->inizio) . " fine " . Yii::$app->formatter->asDate($picPresente->fine_reale) . " scheda ASTER: " . $picPresente->cartella_aster . " inserito da " . str_replace("@asp.messina.it", "", $picPresente->id_utente) . ")  </b>";
        echo "<p><b>Si prega di verificare il PAI precedente e di procedere SOLTANTO se c'è stato un errore oppure si tratta di una rimodulazione o riattualizzazione.<br /> In qualsiasi altro caso " . Html::a('basta tornare alla home cliccando qui.', ['adi/index']) . "</b><br /><br />";
        echo Html::a('Visualizza il PAI precedente', ['adi/report', 'id' => $picPresente->id], ['class' => 'btn btn-danger', 'target' => '_blank']);
        // echo link to return home
        echo "</p><hr>";
        echo "<p class='mb-0'><b>Se si continua, il PAI attivo verrà chiuso e verrà notificata la vecchia ditta (se diversa).<br /> Per continuare è obbligatorio indicare una motivazione di chiusura del PAI precedente:</b></p>";
        // echo options for $pic->motivazione_chiusura, values "SCADENZA" and "RINUNCIA"
        echo $form->field($pic, 'motivazione_chiusura')->dropDownList(
            [
                'RIATTUALIZZAZIONE' => 'RIATTUALIZZAZIONE nuovo PAI',
                'RIMODULAZIONE' => 'RIMODULAZIONE: Rimodulazione del PAI precedente',
                'ERRORE' => 'ERRORE: PAI precedente inserito per errore',
                'CAMBIO_DITTA_ASSISTITO' => 'CAMBIO DITTA: L\'assistito ha deciso di cambiare ditta fornitrice',
                'RINUNCIA_ASSISTITO' => 'RINUNCIA ASSISTITO: Rinuncia dell\'assistito',
                'ASSISTITO_RICOVERATO' => 'ASSISTITO RICOVERATO: Pai precedente non attivato per ricovero paziente',
                'RINUNCIA_DITTA' => 'PROBLEMA DITTA: Rinuncia o problema legato alla ditta precedente',
            ], ['prompt' => 'Seleziona la motivazione', 'class' => 'form-control', 'style' => 'margin-top: 10px'])->label(false);
        echo "</div>";
        // echo button to download file
    }

    if (str_contains(strtoupper($pic->distretto), 'MESSINA') && (!str_contains(strtoupper($pic->distretto), 'NORD') && !str_contains(strtoupper($pic->distretto), 'SUD'))) {
        // div class success
        echo "<div class='alert alert-warning' role='alert'>";
        echo "<h4 class='alert-heading'>RILEVATO DISTRETTO DI MESSINA,</h4>";
        echo "<p><b>Si prega di specificare se Messina NORD o SUD, l'informazione è richiesta per una corretta gestione del paziente da parte della ditta.</b>";
        echo "</p><hr>";
        echo $form->field($pic, 'distretto')->dropDownList(
            [
                'MESSINA NORD' => 'MESSINA NORD',
                'MESSINA SUD' => 'MESSINA SUD'], ['prompt' => 'Seleziona il distretto (se nord o sud)', 'class' => 'form-control', 'style' => 'margin-top: 10px', 'value' => 'MESSINA'])->label(false);
        echo "</div>";
    }
    else {
        echo $form->field($pic, 'distretto')->hiddenInput()->label(false);
    }

    // div class success
    ?>

    <?php
    // optionselect for ditta
    $ditta = DitteAccreditate::find()->where(['attiva' => true])->all();
    $items = [];
    foreach ($ditta as $d) {
        /* @var $d \app\models\DitteAccreditate */
        $items[$d->id] = $d->getDescrDitta();
    }
    // div class success
    echo "<div class='alert alert-success' role='alert'>";
    echo $form->field($pic, 'ditta_scelta')->dropDownList($items, ['prompt' => 'Seleziona la ditta', 'class' => 'form-control'])->label("Ditta scelta dall'utente:", ['style' => 'font-weight: bold;']);
    // echo note text area
    echo $form->field($pic, 'note')->textarea(['rows' => 3])->label('Eventuali note da inserire nella comunicazione:', ['style' => 'font-weight: bold;']);

    echo $form->field($ulterioriAllegati, 'file')->widget(FileInput::classname(), [
        'options' => ['multiple' => true],
        'pluginOptions' => [
            'allowedFileExtensions' => ['pdf'],
            'dropZoneTitle' => 'Ulteriori file da allegare alla mail (facoltativi)',
            'showCaption' => false,
            'browseLabel' => 'Scegli eventuali allegati (solo .pdf)',
        ]
    ]);

    echo $form->field($pic,'copia_pai_inviata_al_medico')->checkbox(['label' => 'Invia copia mail al medico di base '.$pic->medico_rilevato, 'id' => 'conferma', 'required' => true,'checked' => true]);
    echo $form->field($pic,'mail_medico')->textInput(['maxlength' => true, 'placeholder' => 'Indirizzo mail'])->label('Indirizzo mail:');
    echo "<div class='text-center'>";
    echo Html::submitButton('Conferma PIC e assegna alla ditta selezionata', ['class' => 'btn btn-success', "id" => "subBtn", "onclick" => "handleClick(this.id)"]);
    echo "</div>";

    echo "</div>";
    ?>

    <?= $form->field($pic, 'nome')->hiddenInput()->label(false) ?>
    <?= $form->field($pic, 'cognome')->hiddenInput()->label(false) ?>
    <?= $form->field($pic, 'cf')->hiddenInput()->label(false) ?>
    <?= $form->field($pic, 'diagnosi')->hiddenInput()->label(false) ?>
    <?= $form->field($pic, 'piano_terapeutico')->hiddenInput()->label(false) ?>
    <?= $form->field($pic, 'data_pic')->hiddenInput()->label(false) ?>
    <?= $form->field($pic, 'cartella_aster')->hiddenInput()->label(false) ?>
    <?= $form->field($pic, 'dati_nascita')->hiddenInput()->label(false) ?>
    <?= $form->field($pic, 'dati_residenza')->hiddenInput()->label(false) ?>
    <?= $form->field($pic, 'dati_domicilio')->hiddenInput()->label(false) ?>
    <?= $form->field($pic, 'recapiti')->hiddenInput()->label(false) ?>
    <?= $form->field($pic, 'medico_curante')->hiddenInput()->label(false) ?>
    <?= $form->field($pic, 'medico_prescrittore')->hiddenInput()->label(false) ?>
    <?= $form->field($pic, 'medico_rilevato')->hiddenInput()->label(false) ?>
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
                <div class="col-md-12">
                    <?= $form->field($pic, 'distretto')->textInput(['maxlength' => true, 'disabled' => true]) ?>
                </div>
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
                <div class="col-md-6">
                    <?= $form->field($pic, 'medico_rilevato')->textInput(['maxlength' => true, 'disabled' => true])->label("Medico di base dell'assistito") ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($pic, 'medico_curante')->textInput(['maxlength' => true, 'disabled' => true]) ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($pic, 'medico_prescrittore')->textInput(['maxlength' => true, 'disabled' => true]) ?>
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