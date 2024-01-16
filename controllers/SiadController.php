<?php

namespace app\controllers;

use app\models\FileUpload;
use Yii;
use yii\web\UploadedFile;

class SiadController extends \yii\web\Controller

{
    public function actionIndex()
    {
        $model = new FileUpload();

        if (Yii::$app->request->isPost) {
            $model->file = UploadedFile::getInstance($model, 'file');
            if ($model->validate()) {
                if (!is_dir(Yii::$app->params['uploadPath']))
                    mkdir(Yii::$app->params['uploadPath'], 0777, true);
                $filePath = Yii::$app->params['uploadPath'] . $model->file->baseName . '.' . $model->file->extension;
                if ($model->file->saveAs($filePath)) {
                    Yii::$app->session->setFlash('success', 'File caricato con successo.');

                }
            }
        }

        return $this->render('index', [
            'model' => $model,
        ]);

    }
}
