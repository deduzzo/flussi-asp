<?php

/** @var yii\web\View $this */

use yii\helpers\Url;$this->title = Yii::$app->name;
?>
<div class="site-index">

    <div class="jumbotron text-center bg-transparent mt-5 mb-5">
        <h1 class="display-4">Gestione Flussi Informativi</h1>
        <p class="display-6">ASP 5 Messina</p>
    </div>

    <div class="body-content">

        <div class="row">
            <div class="col-lg-4 mb-3">
                <h2>ADI - Assistenza Domiciliare Integrata</h2>

<p>Creazione e invio PAI in regime di convenzionamento</p>
<a class="btn btn-primary" href="<?= Url::to(['/adi/nuova']); ?>">Nuovo PAI</a>
<a class="btn btn-outline-secondary" href="<?= Url::to(['/adi/index']); ?>">Elenco</a>
            </div>
            <?php /*\app\models\utils\Utils::aggiornaMailMedici("D:\DATI\Download\mmgpediatri.xlsx"); */?>
            <div class="col-lg-4 mb-3">
                <!--<h2>Heading</h2>

                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et
                    dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip
                    ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu
                    fugiat nulla pariatur.</p>

                <p><a class="btn btn-outline-secondary" href="https://www.yiiframework.com/forum/">Yii Forum &raquo;</a></p>-->
            </div>
            <div class="col-lg-4">
<!--                <h2>Heading</h2>

                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et
                    dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip
                    ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu
                    fugiat nulla pariatur.</p>

                <p><a class="btn btn-outline-secondary" href="https://www.yiiframework.com/extensions/">Yii Extensions &raquo;</a></p>-->
            </div>
        </div>

    </div>
</div>
