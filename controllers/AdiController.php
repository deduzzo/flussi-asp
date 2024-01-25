<?php

namespace app\controllers;

use app\models\AdiPicSearch;
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
                'only' => ['index', 'scelta-ditta', 'report', 'cerca', 'nuova', 'download'],
                'rules' => [
                    [
                        'actions' => ['index', 'scelta-ditta', 'report', 'cerca', 'nuova', 'download'],
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
        $model->scenario = FileUpload::SCENARIO_SINGLE;
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
                    $filePath = Yii::$app->params['uploadPath'] . DIRECTORY_SEPARATOR . $model->file->baseName . '.' . $model->file->extension;
                    $model->file->saveAs($filePath);
                    $fileHash = hash_file('md5', $filePath);
                    $newFilePath = Yii::$app->params['uploadPath'] . DIRECTORY_SEPARATOR . $fileHash . '.' . $model->file->extension;
                    rename($filePath, $newFilePath);
                    try {
                        $dati = Utils::ottieniDatiPICfromPDF($newFilePath);
                        $newPic = new AdiPic();
                        $newPic->cartella_aster = $dati['cartellaAster'];
                        $newPic->num_contatto = $dati['numContatto'];
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
                        $newPic->piano_terapeutico = $dati['interventi'];
                        $newPic->inizio = Carbon::createFromFormat('d/m/Y', $dati['da'])->format('Y-m-d');
                        $newPic->fine = Carbon::createFromFormat('d/m/Y', $dati['a'])->format('Y-m-d');
                        $newPic->fine_reale = Carbon::createFromFormat('d/m/Y', $dati['a'])->format('Y-m-d');
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
        $terapia = str_replace("\r\n", "<br />", $pic->piano_terapeutico);
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
                    <td colspan='1' style='padding-top: 10px'><b>Data</b></td>
                    <td colspan='3' style='padding-top: 10px'>" . Yii::$app->formatter->asDate($pic->data_pic) . "</td>
                    <td colspan='1' style='padding-top: 10px'><b>Inizio:</b></td>
                    <td colspan='3' style='padding-top: 10px'>" . Yii::$app->formatter->asDate($pic->inizio) . "</td>
                    <td colspan='1' style='padding-top: 10px'><b>Fine:</b></td>
                    <td colspan='3' style='padding-top: 10px'>" . Yii::$app->formatter->asDate($pic->fine) . "</td>
                  </tr>
                  <tr>
                    <td colspan='1' style='padding-top: 10px'><b>Cartella<br /> ASTER</b></td>
                    <td colspan='5' style='padding-top: 10px'>$pic->cartella_aster</td>
                    <td colspan='1' style='padding-top: 10px'><b>Num Contatto:</b></td>
                    <td colspan='5' style='padding-top: 10px'>" . $pic->num_contatto . "</td>
                  </tr>
                  <tr>
                    <td colspan='1' style='padding-top: 10px'><b>Codice<br /> Fiscale</b></td>
                    <td colspan='11' style='padding-top: 10px'>$pic->cf</td>
                    </tr>
                  <tr>
                    <td colspan='1' style='padding-top: 10px'><b>Cognome</b></td>
                    <td colspan='5' style='padding-top: 10px'>$pic->cognome</td>
                    <td colspan='1' style='padding-top: 10px'><b>Nome</b></td>
                    <td colspan='5' style='padding-top: 10px'>$pic->nome</td>
                  </tr>
                  <tr>
                    <td colspan='1' style='padding-top: 10px'><b>Nato a</b></td>
                    <td colspan='5' style='padding-top: 10px'>$pic->dati_nascita</td>
                    <td colspan='1' style='padding-top: 10px'><b>Residenza</b></td>
                    <td colspan='5' style='padding-top: 10px'>$pic->dati_residenza</td>
                  </tr>
                  <tr>
                    <td colspan='1' style='padding-top: 10px'><b>Domiciliato a</b></td>
                    <td colspan='5' style='padding-top: 10px'>$pic->dati_domicilio</td>
                    <td colspan='1' style='padding-top: 10px'><b>Recapiti</b></td>
                    <td colspan='5' style='padding-top: 10px'>$pic->recapiti</td>
                  </tr>
                  <tr>
                    <td colspan='1' style='padding-top: 10px'><b>Medico<br /> curante</b></td>
                    <td colspan='5' style='padding-top: 10px'>$pic->medico_curante</td>
                    <td colspan='1' style='padding-top: 10px'><b>Medico <br />prescrittore</b></td>
                    <td colspan='5' style='padding-top: 10px'>$pic->medico_prescrittore</td>
                  </tr>
                  <tr>
                    <td colspan='1' style='padding-top: 10px'><b>Diagnosi</b></td>
                    <td colspan='11' style='padding-top: 10px'>$pic->diagnosi</td>
                  </tr>
                  <tr>
                    <td colspan='12' style='padding-top: 10px'><b>Piano terapeutico</b></td></tr>
                    <tr>
                    <td colspan='12' style='padding-top: 5px'>$terapia</td>
                  </tr>
                  <tr>
                    <td colspan='12' style='padding-top: 10px'><b>DITTA PRESCELTA:</b></td></tr>
                    <tr>
                    <td colspan='12' style='padding-top: 5px; color: red'><h2>" . $pic->dittaScelta->denominazione . "</h2></td>
                  </tr>
                  <tr>
                    <td colspan='12' style='padding-top: 20px'><b>EVENTUALI NOTE:</b></td></tr>
                    <tr>
                    <td colspan='12' style='padding-top: 5px'>" . $pic->note . "</td>
                  </tr>
                  </table>
                  <div class='footer' style='padding-top: 20px'>
                    <p>Data <span class='underline'>" . Yii::$app->formatter->asDate(Carbon::now()) . "</span></p>
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

        $oggettoMail = "PAI assistito " . $pic->cf . " cartella " . $pic->cartella_aster . " distretto " . $pic->distretto . " - " . $pic->dittaScelta->denominazione;

        $distrettiString = 'Messina NORD  -  <a href="mailto:adi.menord@asp.messina.it?subject=' . rawurlencode("CONFERMA RICEZIONE " . $oggettoMail) . '">adi.menord@asp.messina.it</a><br />
                    Messina SUD  -  <a href="mailto:adi.mesud@asp.messina.it?subject=' . rawurlencode("CONFERMA RICEZIONE " . $oggettoMail) . '">adi.mesud@asp.messina.it</a><br />
                    Barcellona  -  <a href="mailto:adi.barcellona-pg@asp.messina.it?subject=' . rawurlencode("CONFERMA RICEZIONE " . $oggettoMail) . '">adi.barcellona-pg@asp.messina.it</a><br />
                    Lipari  -  <a href="mailto:adi.lipari@asp.messina.it?subject=' . rawurlencode("CONFERMA RICEZIONE " . $oggettoMail) . '">adi.lipari@asp.messina.it</a><br />
                    Milazzo  -  <a href="mailto:adi.milazzo@asp.messina.it?subject=' . rawurlencode("CONFERMA RICEZIONE " . $oggettoMail) . '">adi.milazzo@asp.messina.it</a><br />
                    Mistretta  -  <a href="mailto:adi.mistretta@asp.messina.it?subject=' . rawurlencode("CONFERMA RICEZIONE " . $oggettoMail) . '">adi.mistretta@asp.messina.it</a><br />
                    Patti  -  <a href="mailto:adi.patti@asp.messina.it?subject=' . rawurlencode("CONFERMA RICEZIONE " . $oggettoMail) . '">adi.patti@asp.messina.it</a><br />
                    S.Agata  -  <a href="mailto:adi.sagata@asp.messina.it?subject=' . rawurlencode("CONFERMA RICEZIONE " . $oggettoMail) . '">adi.sagata@asp.messina.it</a><br />
                    Taormina  -  <a href="mailto:adi.taormina@asp.messina.it?subject=' . rawurlencode("CONFERMA RICEZIONE " . $oggettoMail) . '">adi.taormina@asp.messina.it</a>';
        try {
            $altriFileDaAllegare = glob(Yii::$app->params['uploadPath'] . DIRECTORY_SEPARATOR . $pic->id . DIRECTORY_SEPARATOR . '*');
            $message = Yii::$app->mailer->compose()->setHtmlBody(
                "In data " . Yii::$app->formatter->asDate($pic->data_pic) . " l'utente " . str_replace("@asp.messina.it","",$pic->id_utente) . " ha inserito un PAI a voi assegnato:<br /><br /> $pic->cognome $pic->nome con CF $pic->cf. <br /><br /> In allegato il PAI in oggetto. <br /><br />" .
                ($pic->note ? "<b>NOTE:</b><br />" . $pic->note . "<br /><br />" : "") .
                (count($altriFileDaAllegare) > 0 ? "<b> SONO PRESENTI ALLEGATI AGGIUNTIVI, si prega di prendere visione<br /><br /></b>" : "") .
                "<b>Si prega se possibile di restituire conferma via mail al servizio adi distrettuale di competenza utilizzando (se possibile) uno dei link in basso (in base al distretto di competenza).</b><br /><br />Di seguito i recapiti:<br /><br />"
                . $distrettiString . "<br /><br /><br /><b>Cordiali saluti</b><br /><br />ASP 5 Messina")
                ->setFrom(Yii::$app->params['adminEmail'])
                ->setCc(Yii::$app->params['ccEmail'])
                ->setTo($test ? 'roberto.dedomenico@asp.messina.it' : $pic->dittaScelta->email)
                ->setSubject($oggettoMail)
                ->attach(Yii::$app->params['tempPath'] . "$random.pdf", ['fileName' => "PAI-$pic->cf.pdf"]);
            foreach ($altriFileDaAllegare as $altroFile) {
                $message->attach($altroFile, ['fileName' => basename($altroFile)]);
            }
            $message->send();
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

    public function actionDownload($id, $file,$isOriginale = false)
    {
        $path = Yii::$app->params['uploadPath'] . (!$isOriginale ? (DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . $file) : (DIRECTORY_SEPARATOR.$file));
        if (file_exists($path)) {
            return Yii::$app->response->sendFile($path);
        } else {
            throw new \yii\web\NotFoundHttpException("File $file non trovato");
        }
    }

    public function actionIndex() {
        $searchProvider = new AdiPicSearch();
        $dataProvider = $searchProvider->search(Yii::$app->request->queryParams, true);
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchProvider,
        ]);
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

    public function actionSceltaDitta()
    {
        $pic = new AdiPic();
        $pic->scenario = AdiPic::SCENARIO_SCELTA_DITTA;
        $ulterioriAllegati = new FileUpload();
        $ulterioriAllegati->scenario = FileUpload::SCENARIO_MULTIPLE;
        if (Yii::$app->request->isPost) {
            $pic->load(Yii::$app->request->post());
            if ($pic->attributes['ditta_scelta'] === null) {
                return $this->render('scelta-ditta',
                    [
                        'pic' => $pic,
                        'ulterioriAllegati' => $ulterioriAllegati,
                    ]
                );
            } else {
                // save
                if ($pic->save()) {
                    $ulterioriAllegati->file = UploadedFile::getInstances($ulterioriAllegati, 'file');
                    if ($ulterioriAllegati->file !== null && count($ulterioriAllegati->file) > 0) {
                        if (!is_dir(Yii::$app->params['uploadPath']))
                            mkdir(Yii::$app->params['uploadPath'], 0777, true);
                        // join path with filename
                        if (is_dir(join(DIRECTORY_SEPARATOR, [Yii::$app->params['uploadPath'], $pic->id])))
                            // remove directory
                            Utils::deleteDirectory(join(DIRECTORY_SEPARATOR, [Yii::$app->params['uploadPath'], $pic->id]));
                        else
                            mkdir(join(DIRECTORY_SEPARATOR, [Yii::$app->params['uploadPath'], $pic->id]), 0777, true);
                        foreach ($ulterioriAllegati->file as $file) {
                            $filePath = join(DIRECTORY_SEPARATOR, [Yii::$app->params['uploadPath'], $pic->id, $file->fullPath]);
                            $file->saveAs($filePath);
                        }
                    }

                    Yii::$app->session->setFlash('success', 'Dati salvati correttamente');
                    $out = $this->inviaPdfAllaDitta($pic);
                    if ($out) {
                        Yii::$app->session->setFlash('success', "Email alla ditta " . $pic->dittaScelta->denominazione . " inviata correttamente");
                        return $this->redirect(['report', 'id' => $pic->id]);
                    } else
                        Yii::$app->session->setFlash('error', 'Errore nell\'invio dell\'email');
                } else {
                    Yii::$app->session->setFlash('error', 'Errore nel salvataggio dei dati');
                    return $this->render('scelta-ditta', ['pic' => $pic, 'ulterioriAllegati' => $ulterioriAllegati]);
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
