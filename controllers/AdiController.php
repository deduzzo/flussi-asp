<?php

namespace app\controllers;

use app\models\utils\SymfonyMailerComponent;
use app\models\AdiPic;
use app\models\FileUpload;
use app\models\utils\Utils;
use Carbon\Carbon;
use Exception;
use Mpdf\Mpdf;
use PhpParser\Node\Expr\Cast\Object_;
use TCPDF;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\UploadedFile;

class AdiController extends \yii\web\Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'scelta-ditta', 'report', 'cerca', 'nuova'],
                'rules' => [
                    [
                        'actions' => ['index', 'scelta-ditta', 'report', 'cerca', 'nuova'],
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
            if (!array_key_exists('nuovo', $this->request->post())) {
                $model->file = UploadedFile::getInstance($model, 'file');
                // if !file
                if ($model->file === null) {
                    Yii::$app->session->setFlash('error', 'Nessun file caricato, importare un file o creare un nuovo PAI da zero');
                    return $this->render('nuova', [
                        'model' => $model,
                    ]);
                }
                if ($model->validate()) {
                    if (!is_dir(Yii::$app->params['uploadPath']))
                        mkdir(Yii::$app->params['uploadPath'], 0777, true);
                    $filePath = Yii::$app->params['uploadPath'] . $model->file->baseName . '.' . $model->file->extension;
                    $model->file->saveAs($filePath);
                    $fileHash = hash_file('md5', $filePath);
                    $newFilePath = Yii::$app->params['uploadPath'] . $fileHash . '.' . $model->file->extension;
                    rename($filePath, $newFilePath);
                    try {
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
                        $newPic->id_utente = Yii::$app->user->identity->username;
                    } catch (\Exception $e) {
                        Yii::$app->session->setFlash('error', 'Errore nel reperire i dati dal file caricato');
                        return $this->render('nuova', [
                            'model' => $model,
                        ]);
                    }
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

    private function generaPDFPic($pic)
    {
        $mpdf = new Mpdf();
        $terapia =  str_replace("\r\n", "<br />", $pic->piano_terapeutico);
        $alias = Yii::getAlias('@web');
        $html = "<!DOCTYPE html>
                <html lang='it' xmlns='http://www.w3.org/1999/html'>
                <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <style>
                  td {
                    text-align: left; /* Aligns text to the left in all table cells */
                  }
                </style>
                </head>
                <body>
                <div class='container'>
                  <table style='border: 0;'>
                  <tr>
                    <td rowspan='2' colspan='6'>
                        <img src='$alias/static/images/asp-messina.jpg' alt='ASP Messina' class='logo'>
                    </td>
                    <td style='text-align: center; padding-top: 40px' colspan='6'>
                      <p>SPORTELLO UNICO DI ACCESSO ALLE CURE DOMICILIARI</p>
                      <p class='underline' style='padding-top: 5px'><b>$pic->distretto</b></p>
                    </td>
                  </tr>
                  <tr>
                    <td style='text-align: center' colspan='6'>
                        <h2>Piano Assistenza Individualizzato</h2>
                    </td>
                  </tr>
                  <tr>
                    <td colspan='1' style='padding-top: 20px'><b>Data</b></td>
                    <td colspan='5' style='padding-top: 20px'>".Yii::$app->formatter->asDate($pic->data_pic)."</td>
                    <td colspan='1' style='padding-top: 20px'><b>Cartella<br /> ASTER</b></td>
                    <td colspan='5' style='padding-top: 20px'>$pic->cartella_aster</td>
                  </tr>
                  <tr>
                    <td colspan='1' style='padding-top: 20px'><b>Codice<br /> Fiscale</b></td>
                    <td colspan='11' style='padding-top: 20px'>$pic->cf</td>
                    </tr>
                  <tr>
                    <td colspan='1' style='padding-top: 20px'><b>Cognome</b></td>
                    <td colspan='5' style='padding-top: 20px'>$pic->cognome</td>
                    <td colspan='1' style='padding-top: 20px'><b>Nome</b></td>
                    <td colspan='5' style='padding-top: 20px'>$pic->nome</td>
                  </tr>
                  <tr>
                    <td colspan='1' style='padding-top: 20px'><b>Nato a</b></td>
                    <td colspan='5' style='padding-top: 20px'>$pic->dati_nascita</td>
                    <td colspan='1' style='padding-top: 20px'><b>Residenza</b></td>
                    <td colspan='5' style='padding-top: 20px'>$pic->dati_residenza</td>
                  </tr>
                  <tr>
                    <td colspan='1' style='padding-top: 20px'><b>Domiciliato a</b></td>
                    <td colspan='5' style='padding-top: 20px'>$pic->dati_domicilio</td>
                    <td colspan='1' style='padding-top: 20px'><b>Recapiti</b></td>
                    <td colspan='5' style='padding-top: 20px'>$pic->recapiti</td>
                  </tr>
                  <tr>
                    <td colspan='1' style='padding-top: 20px'><b>Medico<br /> curante</b></td>
                    <td colspan='5' style='padding-top: 20px'>$pic->medico_curante</td>
                    <td colspan='1' style='padding-top: 20px'><b>Medico <br />prescrittore</b></td>
                    <td colspan='5' style='padding-top: 20px'>$pic->medico_prescrittore</td>
                  </tr>
                  <tr>
                    <td colspan='1' style='padding-top: 20px'><b>Diagnosi</b></td>
                    <td colspan='11' style='padding-top: 20px'>$pic->diagnosi</td>
                  </tr>
                  <tr>
                    <td colspan='12' style='padding-top: 20px'><b>Piano terapeutico</b></td></tr>
                    <tr>
                    <td colspan='12' style='padding-top: 5px'>$terapia</td>
                  </tr>
                  <tr>
                    <td colspan='12' style='padding-top: 20px'><b>DITTA PRESCELTA:</b></td></tr>
                    <tr>
                    <td colspan='12' style='padding-top: 5px'><h2>".$pic->dittaScelta->denominazione ."</h2></td>
                  </tr>
                  </table>
                  <div class='footer' style='padding-top: 50px'>
                    <p>Data <span class='underline'>".Yii::$app->formatter->asDate(Carbon::now())."</span></p>
                    <p>Firma del responsabile U.V.D.</p><br />
                    <p>__________________________________</p>
                  </div>
                </div>
                </body>
                </html>";

        $mpdf->WriteHTML($html);
        return $mpdf;
    }

    private function inviaPdfAllaDitta($pic)
    {
        $pdf = $this->generaPDFPic($pic);
        $test = false;
        // save file to temp folder
        $random = Yii::$app->security->generateRandomString(10);
        // create if not exist path Yii::$app->params['tempPath']
        if (!is_dir(Yii::$app->params['tempPath']))
            mkdir(Yii::$app->params['tempPath'], 0777, true);
        $pdf->Output(Yii::$app->params['tempPath'] . "$random.pdf", 'F');

        $oggettoMail = $pic->dittaScelta->denominazione. " - Conferma ricezione PAI assistito: ". $pic->cf;

        $distrettiString = 'adi.menord" <a href="mailto:adi.menord@asp.messina.it?subject=' . rawurlencode($oggettoMail) . '">adi.menord@asp.messina.it</a><br />
                    adi.mesud" <a href="mailto:adi.mesud@asp.messina.it?subject=' . rawurlencode($oggettoMail) . '">adi.mesud@asp.messina.it</a><br />
                    adi.barcellona-pg" <a href="mailto:adi.barcellona-pg@asp.messina.it?subject=' . rawurlencode($oggettoMail) . '">adi.barcellona-pg@asp.messina.it</a><br />
                    adi.lipari" <a href="mailto:adi.lipari@asp.messina.it?subject=' . rawurlencode($oggettoMail) . '">adi.lipari@asp.messina.it</a><br />
                    adi.milazzo" <a href="mailto:adi.milazzo@asp.messina.it?subject=' . rawurlencode($oggettoMail) . '">adi.milazzo@asp.messina.it</a><br />
                    adi.mistretta" <a href="mailto:adi.mistretta@asp.messina.it?subject=' . rawurlencode($oggettoMail) . '">adi.mistretta@asp.messina.it</a><br />
                    adi.patti" <a href="mailto:adi.patti@asp.messina.it?subject=' . rawurlencode($oggettoMail) . '">adi.patti@asp.messina.it</a><br />
                    adi.sagata" <a href="mailto:adi.sagata@asp.messina.it?subject=' . rawurlencode($oggettoMail) . '">adi.sagata@asp.messina.it</a><br />
                    adi.taormina" <a href="mailto:adi.taormina@asp.messina.it?subject=' . rawurlencode($oggettoMail) . '">adi.taormina@asp.messina.it</a>';
        try {
            $message = Yii::$app->mailer->compose()->setHtmlBody(
                "In data " . Yii::$app->formatter->asDate($pic->data_pic) . " è stato a voi assegnato l'assistito:<br /><br /> $pic->cognome $pic->nome con CF $pic->cf. <br /> In allegato il PAI in oggetto. <br />Si prega se possibile di restituire conferma al servizio adi del distretto mittente.<br /><br />Di seguito i recapiti:<br />"
                .$distrettiString."<br />Cordiali saluti<br /><br />ASP 5 Messina")
                ->setFrom('roberto.dedomenico@asp.messina.it')
                ->setTo($test ? 'roberto.dedomenico@asp.messina.it' : $pic->dittaScelta->email)
                ->setSubject('ASP 5 Messina - Nuovo PAI assistito ' . $pic->cf)->attach(Yii::$app->params['tempPath'] . "$random.pdf", ['fileName' => "PAI-$pic->cf.pdf"])->send();
            if ($message) {
                $pic->data_ora_invio = date('Y-m-d H:i:s');
                // remove temp file
                unlink(Yii::$app->params['tempPath'] . "$random.pdf");
                $pic->save();
            }
            return $message;
        } catch (\yii\db\Exception $ex) {
            return false;
        }
    }

    public function actionReport($id = null)
    {
        if ($id) {
            $pic = AdiPic::findOne($id);
            if ($this->request->isPost) {
                $pdf = $this->generaPDFPic($pic);
                if (array_key_exists('report', $this->request->post()))
                    $pdf->Output();
                else if (array_key_exists('notifica', $this->request->post())) {
                    $random = Yii::$app->security->generateRandomString(10);
                    if (!is_dir(Yii::$app->params['tempPath']))
                        mkdir(Yii::$app->params['tempPath'], 0777, true);
                    $pdf->Output(Yii::$app->params['tempPath'] . "$random.pdf", 'F');
                    try {
                        $res = $this->inviaPdfAllaDitta($pic);
                        if ($res)
                            Yii::$app->session->setFlash('success', "Email alla ditta " . $pic->dittaScelta->denominazione . " inviata correttamente");
                        else
                            Yii::$app->session->setFlash('error', 'Errore nell\'invio dell\'email');
                    } catch (Exception $e) {
                        Yii::$app->session->setFlash('error', 'Errore nell\'invio dell\'email');
                    }
                }
            }
            return $this->render('report', [
                'pic' => $pic,
            ]);
        } else
            return $this->redirect(['cerca']);
    }

    public
    function actionSceltaDitta()
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
                    $out = $this->inviaPdfAllaDitta($pic);
                    if ($out) {
                        Yii::$app->session->setFlash('success', "Email alla ditta " . $pic->dittaScelta->denominazione . " inviata correttamente");
                        return $this->redirect(['report', 'id' => $pic->id]);
                    }
                    else
                        Yii::$app->session->setFlash('error', 'Errore nell\'invio dell\'email');
                } else {
                    Yii::$app->session->setFlash('error', 'Errore nel salvataggio dei dati');
                    return $this->render('scelta-ditta', ['pic' => $pic]);
                }

            }
        }
        return $this->render('index');
    }

    public
    function actionCerca($cf = null)
    {
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
