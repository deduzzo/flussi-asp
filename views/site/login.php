<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */

/** @var app\models\LoginForm $model */

use app\models\enums\TipologiaLogin;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Inserire i dati d'accesso del dominio</p>

    <div class="row">
        <div class="col-lg-5">

            <?php $form = ActiveForm::begin([
                'id' => 'login-form',
                'fieldConfig' => [
                    'template' => "{label}\n{input}\n{error}",
                    'labelOptions' => ['class' => 'col-lg-1 col-form-label mr-lg-3'],
                    'inputOptions' => ['class' => 'col-lg-3 form-control'],
                    'errorOptions' => ['class' => 'col-lg-7 invalid-feedback'],
                ],
            ]); ?>

            <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>

            <?= $form->field($model, 'password')->passwordInput() ?>

            <!-- Radio select for $model->tipo, values of array $list -->
            <?php
            echo $form->field($model, 'tipo')->radioList(TipologiaLogin::$list, [
                'item' => function ($index, $label, $name, $checked, $value) {
                    $return = '<div class="form-check form-check-inline">'; // Usa 'form-check-inline' per allineare i radio buttons
                    $return .= '<input type="radio" class="form-check-input" name="' . $name . '" value="' . $value . '" id="' . $value . '" ' . ($checked ? 'checked' : '') . '>';
                    $return .= '<label class="form-check-label" for="' . $value . '">' . $label . '</label>';
                    $return .= '</div>';
                    return $return;
                }
            ])->label(false);
            ?>


            <?= $form->field($model, 'rememberMe')->checkbox([
                'template' => "<div class=\"custom-control custom-checkbox\">{input} Ricordami</div>\n<div class=\"col-lg-8\">{error}</div>",
            ]) ?>

            <div class="form-group">
                <div>
                    <?= Html::submitButton('Login', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
                </div>
            </div>

            <?php ActiveForm::end(); ?>

        </div>
    </div>
</div>
