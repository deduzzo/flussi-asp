<?php

namespace app\controllers;

use app\models\AdiPicSearch;
use app\models\Assistito;
use app\models\AdiPic;
use app\models\FileUpload;
use app\models\utils\Utils;
use Carbon\Carbon;
use Exception;
use Mpdf\Mpdf;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
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
                        $out = "non presente nei sistemi";
                        $mailMedico = null;
                        try {
                            $assistito = Assistito::find()->where(['codice_fiscale' => $dati['cf']])->one();
                            if ($assistito) {
                                if ($assistito->codice_regionale_ts && $assistito->codice_regionale_nar && ($assistito->codice_regionale_ts === $assistito->codice_regionale_nar)) {
                                    if ($assistito->medicoNar) {
                                        $out = "rilevato su NAR e TS: " . $assistito->medicoNar->nominativo . " - ambito: " . strtoupper($assistito->medicoNar->distretto);
                                        $mailMedico = $assistito->medicoNar->mail;
                                    }
                                } else if ($assistito->codice_regionale_nar) {
                                    if ($assistito->medicoNar) {
                                        $out = "rilevato su NAR: " . $assistito->medicoNar->nominativo . " - ambito: " . strtoupper($assistito->medicoNar->distretto);
                                        $mailMedico = $assistito->medicoNar->mail;
                                    }
                                } else if ($assistito->codice_regionale_ts) {
                                    if ($assistito->medicoTs) {
                                        $out = "rilevato su TS: " . $assistito->medicoTs->nominativo . " - ambito: " . strtoupper($assistito->medicoTs->distretto);
                                        $mailMedico = $assistito->medicoTs->mail;
                                    }
                                }
                            }
                        } catch (\yii\db\Exception $e) {
                        }
                        $newPic->medico_rilevato = $out;
                        $newPic->mail_medico = $mailMedico;
                    } catch (\Exception $e) {
                        $aggiornamento = false;
                        if ($aggiornamento)
                            Yii::$app->session->setFlash('info', '<b>Sistema MOMENTANEAMENTE non disponibile per aggiornamenti, riprovare a breve.</b>');
                        else
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

    public function actionAggiornaMail() {

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
                <title></title>
                </head>
                <body>
                <div class='container'>
                  <table style='border: 0;'>
                  <tr>
                    <td rowspan='2' colspan='1'>
                        <img src='$alias/static/images/asp-messina.jpg' alt='ASP Messina' class='logo' style='height: 100px; width: auto'>
                    </td>
                    <td style='text-align: center; padding-top: 40px' colspan='11'>
                      <p>SPORTELLO UNICO DI ACCESSO ALLE CURE DOMICILIARI</p>
                      <p class='underline' style='padding-top: 5px'><b>$pic->distretto</b></p>
                    </td>
                  </tr>
                  <tr>
                    <td style='text-align: center; color: red' colspan='12'>
                        <h2>PIANO ASSISTENZA INDIVIDUALIZZATO (PAI)</h2>
                    </td>
                  </tr>
                  <tr>
                    <td colspan='1' style='padding-top: 20px'><b>Data</b></td>
                    <td colspan='4' style='padding-top: 20px'>" . Yii::$app->formatter->asDate($pic->data_pic) . "</td>
                    <td colspan='1' style='padding-top: 20px'><b>Inizio</b></td>
                    <td colspan='3' style='padding-top: 20px; color: red'><b>" . Yii::$app->formatter->asDate($pic->inizio) . "</b></td>
                    <td colspan='1' style='padding-top: 20px'><b>Fine</b></td>
                    <td colspan='2' style='padding-top: 20px; color: red'><b>" . Yii::$app->formatter->asDate($pic->fine) . "</b></td>
                  </tr>
                  <tr>
                   <td colspan='1' style='padding-top: 10px'><b>Cod. Ficale</b></td>
                    <td colspan='4' style='padding-top: 10px'>$pic->cf</td>
                    <td colspan='1' style='padding-top: 10px'><b>n°ASTER</b></td>
                    <td colspan='3' style='padding-top: 10px'>$pic->cartella_aster</td>
                    <td colspan='1' style='padding-top: 10px'><b>n° cont</b></td>
                    <td colspan='2' style='padding-top: 10px'>" . $pic->num_contatto . "</td>
                  </tr>
                  <tr>
                    <td colspan='1' style='padding-top: 10px'><b>Cognome</b></td>
                    <td colspan='5' style='padding-top: 10px; color: blue'><b>$pic->cognome</b></td>
                    <td colspan='1' style='padding-top: 10px'><b>Nome</b></td>
                    <td colspan='5' style='padding-top: 10px; color: blue'><b>$pic->nome</b></td>
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
                    <td colspan='1' style='padding-top: 10px'><b>Medico curante dichiarato</b></td>
                    <td colspan='5' style='padding-top: 10px'>$pic->medico_curante</td>
                    <td colspan='1' style='padding-top: 10px'><b>Medico prescrittore dichiarato</b></td>
                    <td colspan='5' style='padding-top: 10px'>$pic->medico_prescrittore</td>
                  </tr>
                  <tr>
                    <td colspan='1' style='padding-top: 10px'><b>MMG assistito</b></td>
                    <td colspan='5' style='padding-top: 10px'>$pic->medico_curante</td>
                  </tr>
                  <tr>
                    <td colspan='1' style='padding-top: 10px'><b>Diagnosi</b></td>
                    <td colspan='11' style='padding-top: 10px'>$pic->diagnosi</td>
                  </tr>
                  <tr>
                    <td colspan='12' style='padding-top: 10px'><b>PIANO TERAPEUTICO:</b></td></tr>
                 <tr>
                    <td colspan='12' style='padding-top: 5px'>$terapia</td>
                  </tr>
                  <tr>
                    <td colspan='2' style='padding-top: 10px'><b>DITTA PRESCELTA:</b></td>
                    <td colspan='10' style='padding-top: 10px; color: red'><h2>" . $pic->dittaScelta->denominazione . "</h2></td>
                  </tr>
                  <tr>
                    <td colspan='12' style='padding-top: 10px'><b>EVENTUALI NOTE:</b></td></tr>
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

    private function inviaPdfAllaDitta($pic, $picPrecedente = null)
    {
        $pdf = $this->generaPDFPic($pic);
        $test = false;
        // save file to temp folder
        $random = Yii::$app->security->generateRandomString(10);
        // create if not exist path Yii::$app->params['tempPath']
        if (!is_dir(Yii::$app->params['tempPath']))
            mkdir(Yii::$app->params['tempPath'], 0777, true);
        $pdf->Output(Yii::$app->params['tempPath'] . "$random.pdf", 'F');

        $dist = "[" . $pic->distretto . "]";
        $oggettoMail = " PAI - " . $pic->cognome . " " . $pic->nome . " " . $pic->cf . " #Aster: " . $pic->cartella_aster . ($picPrecedente ? (" [NUOVO, A SEGUITO DI " . $picPrecedente->motivazione_chiusura . "]") : "") . " - " . $pic->dittaScelta->denominazione;

        $distrettiString = 'Messina NORD  -  <a href="mailto:adi.menord@asp.messina.it?subject=' . rawurlencode("CONFERMA RICEZIONE " . $oggettoMail) . '">adi.menord@asp.messina.it</a><br />
                    Messina SUD  -  <a href="mailto:adi.mesud@asp.messina.it?subject=' . rawurlencode("CONFERMA RICEZIONE " . $oggettoMail) . '">adi.mesud@asp.messina.it</a><br />
                    Barcellona  -  <a href="mailto:adi.barcellona-pg@asp.messina.it?subject=' . rawurlencode("CONFERMA RICEZIONE " . $oggettoMail) . '">adi.barcellona-pg@asp.messina.it</a><br />
                    Lipari  -  <a href="mailto:adi.lipari@asp.messina.it?subject=' . rawurlencode("CONFERMA RICEZIONE " . $oggettoMail) . '">adi.lipari@asp.messina.it</a><br />
                    Milazzo  -  <a href="mailto:adi.milazzo@asp.messina.it?subject=' . rawurlencode("CONFERMA RICEZIONE " . $oggettoMail) . '">adi.milazzo@asp.messina.it</a><br />
                    Mistretta  -  <a href="mailto:adi.mistretta@asp.messina.it?subject=' . rawurlencode("CONFERMA RICEZIONE " . $oggettoMail) . '">adi.mistretta@asp.messina.it</a><br />
                    Patti  -  <a href="mailto:adi.patti@asp.messina.it?subject=' . rawurlencode("CONFERMA RICEZIONE " . $oggettoMail) . '">adi.patti@asp.messina.it</a><br />
                    S.Agata  -  <a href="mailto:adi.sagata@asp.messina.it?subject=' . rawurlencode("CONFERMA RICEZIONE " . $oggettoMail) . '">adi.sagata@asp.messina.it</a><br />
                    Taormina  -  <a href="mailto:adi.taormina@asp.messina.it?subject=' . rawurlencode("CONFERMA RICEZIONE " . $oggettoMail) . '">adi.taormina@asp.messina.it</a>';
        $oggettoMail = $dist . $oggettoMail;
        try {
            $altriFileDaAllegare = glob(Yii::$app->params['uploadPath'] . DIRECTORY_SEPARATOR . $pic->id . DIRECTORY_SEPARATOR . '*');
            $utente = str_replace("@asp.messina.it", "", $pic->id_utente);
            $cc = [Yii::$app->params['ccEmail']];
            if ($pic->mail_medico && $pic->mail_medico !== "") {
                if (!$test)
                    $cc[] = $pic->mail_medico;
                else
                    $cc[] = "robertodedomenico@gmail.com";
            }
            $message = Yii::$app->mailer->compose()->setHtmlBody(
                "In data " . Yii::$app->formatter->asDate($pic->data_pic) . " l'utente " . $utente . " dell'ASP di Messina ha inserito un PAI per l'assistito:<br /><br /> $pic->cognome $pic->nome con CF $pic->cf. <br /><br />" .
                 "DITTA PRESCELTA: <b>" . $pic->dittaScelta->denominazione . "</b><br />" .
                ("<br /><b>Medico di base dell'assistito " . $pic->medico_rilevato . "</b>".(($pic->mail_medico && $pic->mail_medico !== "") ? (" indirizzo mail: <a href='mailto:".$pic->mail_medico."'>".$pic->mail_medico."</a><br /><br />") : "<br /><br />" )) .
                " In allegato il PAI (e gli eventuali allegati).<br /><br />" .
                ($picPrecedente ? ("<b>ATTENZIONE: il PAI precedente con data ".Yii::$app->formatter->asDate($picPrecedente->data_pic)." assegnato alla ditta ".$picPrecedente->dittaScelta->denominazione." è stato chiuso in data " . Yii::$app->formatter->asDate($picPrecedente->fine_reale) . " con motivazione: <i>" . $picPrecedente->motivazione_chiusura . "</i></b><br /><br />") : "") .
                ($pic->note ? "<b>EVENTUALI NOTE:</b><br />" . (trim($pic->note) === "" ? "nessuna" : $pic->note) . "<br /><br />" : "") .
                ((count($altriFileDaAllegare) > 0) ? "<b> SONO PRESENTI ALLEGATI AGGIUNTIVI, si prega di prendere visione<br /><br /></b>" : "") .
                "<b>ESCLUSIVAMENTE PER LE DITTE: si prega di inviare conferma via mail al servizio ADI distrettuale di competenza utilizzando (se possibile) uno dei link in basso:</b><br /><br />"
                . $distrettiString . "<br /><br /><br /><b>Cordiali saluti</b><br /><br />ASP 5 Messina")
                ->setFrom(Yii::$app->params['adminEmail'])
                ->setCc($cc)
                ->setTo($test ? 'roberto.dedomenico@asp.messina.it' : $pic->dittaScelta->email)
                ->setSubject($oggettoMail)
                ->attach(Yii::$app->params['tempPath'] . "$random.pdf", ['fileName' => "PAI-$pic->cf.pdf"]);
            foreach ($altriFileDaAllegare as $altroFile) {
                $message->attach($altroFile, ['fileName' => basename($altroFile)]);
            }
            $message = $message->send();
            if ($picPrecedente && $picPrecedente->dittaScelta !== $pic->dittaScelta)
            {
                $message2 = Yii::$app->mailer->compose()->setHtmlBody("<b>ATTENZIONE!!</b><br /><br />Il PAI attualmente attivo per l'assistito <br /> $pic->cognome $pic->nome con codice fiscale $pic->cf del ".Yii::$app->formatter->asDate($picPrecedente->data_pic)." è stato chiuso dall'utente ".$pic->id_utente." in data " . Yii::$app->formatter->asDate($picPrecedente->fine_reale) . " con motivazione: <b><i>" . $picPrecedente->motivazione_chiusura . "</i></b><br /><br />Il nuovo PAI è stato inviato alla nuova ditta scelta dall'assistito: <b>" . $pic->dittaScelta->denominazione . "</b><br /><br />
                    Se ci dovesse essere qualsiasi problema o per qualsiasi chiarimento o richiesta di rettifica si prega di contattare il servizio ADI di competenza del distretto di <b>".$pic->distretto."</b><br /><br /><br /><b>Cordiali saluti</b><br /><br />ASP 5 Messina")
                    ->setFrom(Yii::$app->params['adminEmail'])
                    ->setCc(Yii::$app->params['ccEmail'])
                    ->setTo($picPrecedente->dittaScelta->email)
                    ->setSubject("CHIUSURA PAI per l'assistito $pic->cf motivazione: $picPrecedente->motivazione_chiusura")
                    ->send();
            }
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

    public function actionDownload($id, $file, $isOriginale = false)
    {
        $path = Yii::$app->params['uploadPath'] . (!$isOriginale ? (DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . $file) : (DIRECTORY_SEPARATOR . $file));
        if (file_exists($path)) {
            return Yii::$app->response->sendFile($path);
        } else {
            throw new \yii\web\NotFoundHttpException("File $file non trovato");
        }
    }

    public function actionIndex()
    {
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
        $picPresente = null;
        if (Yii::$app->request->isPost) {
            $pic->load(Yii::$app->request->post());
            if ($pic->fine_reale === "")
                $pic->fine_reale = $pic->fine;
            // find if there is another pic with the same cf and that overlaps $pic->inizio and $pic->fine
            $picPresente = AdiPic::find()->where(['cf' => $pic->cf])
                ->andWhere(['<=', 'inizio', $pic->fine_reale])
                ->andWhere(['attivo' => true])
                ->one();
            if ($picPresente) {
                if (Carbon::createFromFormat('Y-m-d', $picPresente->fine_reale)->isBefore(Carbon::now())) {
                    $picPresente->attivo = false;
                    $picPresente->save();
                    $picPresente = null;
                }
            }
            if (str_contains(strtoupper($pic->distretto), 'MESSINA') && (!str_contains(strtoupper($pic->distretto), 'NORD') && !str_contains(strtoupper($pic->distretto), 'SUD')))
                $pic->addError('distretto', 'Si prega di specificare il distretto di competenza (se messina Nord o Sud)');
            if ($picPresente)
                $pic->scenario = AdiPic::SCENARIO_PIC_PRESENTE;
            if ($pic->validate()) {
                $motivazione_chiusura = $pic->motivazione_chiusura;
                $pic->motivazione_chiusura = null;
                $pic->scenario = AdiPic::SCENARIO_SCELTA_DITTA;
                if ($pic->save()) {
                    if ($picPresente) {
                        $picPresente->motivazione_chiusura = $motivazione_chiusura;
                        $picPresente->fine_reale = Carbon::createFromFormat('Y-m-d', $pic->inizio)->subDay()->format('Y-m-d');
                        $picPresente->attivo = false;
                        $picPresente->save();
                    }

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
                    $out = $this->inviaPdfAllaDitta($pic, $picPresente);
                    if ($out) {
                        Yii::$app->session->setFlash('success', "Email alla ditta " . $pic->dittaScelta->denominazione . " inviata correttamente");
                    } else
                        Yii::$app->session->setFlash('error', 'PAI SALVATO, ma c\'è stato un errore nell\'invio del PAI alla ditta');
                    return $this->redirect(['report', 'id' => $pic->id]);
                } else {
                    Yii::$app->session->setFlash('error', 'Errore nel salvataggio dei dati');
                    return $this->render('scelta-ditta', [
                        'pic' => $pic,
                        'ulterioriAllegati' => $ulterioriAllegati,
                        'picPresente' => $picPresente
                    ]);
                }
            }
            return $this->render('scelta-ditta',
                [
                    'pic' => $pic,
                    'ulterioriAllegati' => $ulterioriAllegati,
                    'picPresente' => $picPresente
                ]
            );

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
