<?php

namespace app\controllers;

use app\models\AdiPic;
use app\models\FileUpload;
use app\models\utils\Utils;
use Carbon\Carbon;
use PhpParser\Node\Expr\Cast\Object_;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\UploadedFile;

class SiadController extends \yii\web\Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'scelta-ditta', 'report', 'cerca', 'nuova'],
                'rules' => [
                    [
                        'actions' => ['index', 'scelta-ditta', 'report', 'cerca','nuova'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actionNuova()
    {
        $model = new FileUpload();
        if ($this->request->isPost) {
            $newPic = new AdiPic();
            if (!array_key_exists('nuovo',$this->request->post())) {
                $model->file = UploadedFile::getInstance($model, 'file');
                if ($model->validate()) {
                    if (!is_dir(Yii::$app->params['uploadPath']))
                        mkdir(Yii::$app->params['uploadPath'], 0777, true);
                    $filePath = Yii::$app->params['uploadPath'] . $model->file->baseName . '.' . $model->file->extension;
                    $model->file->saveAs($filePath);
                    $fileHash = hash_file('md5', $filePath);
                    $newFilePath = Yii::$app->params['uploadPath'] . $fileHash . '.' . $model->file->extension;
                    rename($filePath, $newFilePath);
                    $dati = Utils::ottieniDatiPICfromPDF($newFilePath);
                    $newPic = new AdiPic();
                    $newPic->cartella_aster = $dati['cartellaAster'];
                    $newPic->cf = $dati['cf'];
                    $newPic->data_pic = Carbon::createFromFormat('d/m/Y', $dati['data'])->format('Y-m-d');
                    $newPic->cognome = $dati['cognome'];
                    $newPic->nome = $dati['nome'];
                    $newPic->dati_nascita = $dati['nascita'];
                    $newPic->dati_residenza = $dati['residenza'];
                    $newPic->dati_domicilio = $dati['domicilio'];
                    $newPic->recapiti = $dati['telefono'];
                    $newPic->medico_curante = $dati['medicoCurante'];
                    $newPic->medico_prescrittore = $dati['medicoPrescrittore'];
                    $newPic->diagnosi = $dati['diagnosiNote'];
                    $newPic->piano_terapeutico = Json::encode($dati['interventi']);
                    $newPic->nome_file = $fileHash . '.' . $model->file->extension;
                    $newPic->data_ora_invio = date('Y-m-d H:i:s');
                    $newPic->distretto = $dati['distretto'];
                }
            }
            return $this->render('pic', [
                'pic' => $newPic,
            ]);
        }

        return $this->render('nuova', [
            'model' => $model,
        ]);

    }

    public function actionReport($id = null)
    {
        if ($id) {
            $pic = AdiPic::findOne($id);
            return $this->render('report', [
                'pic' => $pic,
            ]);
        } else
            return $this->render('cerca', [
            ]);
    }

    public function actionSceltaDitta()
    {
        $pic = new AdiPic();
        $pic->scenario = AdiPic::SCENARIO_SCELTA_DITTA;
        if (Yii::$app->request->isPost) {
            $pic->load(Yii::$app->request->post());
            if ($pic->attributes['ditta_scelta'] === null) {
                return $this->render('scelta-ditta', ['pic' => $pic]);
            } else {
                // save
                if ($pic->save()) {
                    Yii::$app->session->setFlash('success', 'Dati salvati correttamente');
                } else {
                    Yii::$app->session->setFlash('error', 'Errore nel salvataggio dei dati');
                    return $this->render('scelta-ditta', ['pic' => $pic]);
                }
                return $this->redirect(['report', 'id' => $pic->id]);
            }
        }
        return $this->render('index');
    }

    public function actionCerca($cf = null) {
        $pai = [];
        if ($cf) {
            $pai = AdiPic::find()->where(['cf' => $cf])->all();
            if (count($pai) == 0) {
                Yii::$app->session->setFlash('error', 'Nessun PIC trovato con il codice fiscale inserito');
            }
        }
        return $this->render('cerca', [
            'pai' => $pai,
        ]);
    }
}
