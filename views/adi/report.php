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


<div class="text-center">
    <?= Html::beginForm() ?>
    <?php if ($pic) Html::hiddenInput('id', $pic->id) ?>
    <?= Html::submitButton('Visualizza PDF del PAI', ['class' => 'btn btn-success', 'name' => 'report', 'value' => 'report']) ?>
</div>
<div class="text-center" style="margin-top: 10px">
    <?= $pic->data_ora_invio !== null ? ('<span class="badge bg-success">PAI inviato correttamente via mail</span>') : ('<span class="badge bg-danger">PAI non ancora inviato</span><br /><br />' . Html::submitButton('Invia PAI via mail alla ditta', ['class' => 'btn btn-warning', 'name' => 'notifica', 'value' => 'notifica'])) ?>
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
            <!-- elenco allegati -->
            <?php $path = Yii::$app->params['uploadPath'] . DIRECTORY_SEPARATOR . $pic->id;
            if (is_dir($path)) {
                $files = scandir($path);
                $files = array_diff($files, ['.', '..']);
                $numAllegati = count($files);
                // mostra elenco degli allegati con i relativi link
                echo "<div class='col-md-12'><b>Allegati:</b><br />";
                foreach ($files as $file)
                    echo Html::a($file, Url::to(['/adi/download', 'id' => $pic->id, 'file' => $file]), ['target' => '_blank']) . "<br />";
                echo "</div>";
            }
            ?>
        </div>
    </fieldset>
</div>

<?php ActiveForm::end() ?>

<?php
echo Html::beginForm();
echo Html::submitButton('Invia nuovamente mail completa alla ditta prescelta', ['class' => 'btn btn-warning', 'name' => 'notifica', 'value' => 'notifica']);
echo Html::endForm();
?>

