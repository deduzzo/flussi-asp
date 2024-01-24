<?php

namespace app\models\utils;

use app\models\AdiPic;
use yii\helpers\Json;

class Utils
{
    public static function validatePdf($text)
    {
        $text = strtolower($text);
        if (str_contains($text, 'assistito') &&
            str_contains($text, 'cognome') &&
            str_contains($text, 'codice fiscale') &&
            str_contains($text, 'firma del responsabile'))
            return true;
        else
            return false;
    }

    public static function ottieniDatiPICfromPDF($path)
    {
        $out = [];
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($path);
        $text1Original = $pdf->gettext();
        $text1 = explode("\n", $text1Original);
        //$reader = new \Asika\Pdf2text;
        //$text2 = explode("\n",$reader->decode($path));
        $out['cartellaAster'] = self::estraiSottostringa($text1[0], 'Nr.Cartella:', 'Nr.Contatto:');
        $out['numContatto'] = self::estraiSottostringa($text1[0], 'Nr.Contatto:', 'Data');
        $out['cognome'] = self::estraiSottostringa($text1[1], 'Cognome', 'Nome');
        $out['nome'] = self::estraiSottostringa($text1[1], 'Nome');
        $out['nascita'] = self::estraiSottostringa($text1[2], 'Nato a');
        $out['cf'] = self::estraiSottostringa($text1[3], 'Codice fiscale');
        $out['residenza'] = self::estraiSottostringa($text1[4], 'Residente in') . ' ' . $text1[5];
        $out['domicilio'] = self::estraiSottostringa($text1[6], 'Domiciliato in') . ' ' . $text1[7];
        $out['telefono'] = self::estraiSottostringa($text1[8], 'tel/cel');
        $out['medicoCurante'] = self::estraiSottostringa($text1[9], 'Medico curante', 'tel/cel');
        $out['medicoPrescrittore'] = self::estraiSottostringa($text1[10], 'Medico prescrittore', 'tel/cel');
        $out['diagnosiNote'] = self::estraiSottostringa($text1Original, "DIAGNOSI E NOTE\n", "EROGATORE:");

        // get the pos of the array containing the string "DIAGNOSI E NOTE"
        $posInizio = array_search("Inizio - Fine Modalità/IndicazioneDescrizione	Frequenza", $text1);
        $posFine = array_search("SPORTELLO UNICO DI ACCESSO ALLE CURE DOMICILIARI", $text1);
        $interventi = [];
        $i = $posInizio + 1;
        while ($i < $posFine) {
            if (substr($text1[$i + 1], 2, 1) === "/" || $i == $posFine - 1) {
                $rigaTemp = $text1[$i];
                $i++;
            } else {
                $rigaTemp = substr($text1[$i], 0, 21) . "\t";
                $rigaTemp .= substr($text1[$i], 21, strlen($text1[$i]) - 21);
                while (substr($text1[$i + 1], 2, 1) !== "/" || $i == $posFine - 1) {
                    $rigaTemp .= " " . $text1[$i + 1];
                    $i++;
                }
                $i++;
            }
            $interventi[] = $rigaTemp;
        }
        $datiPiano = AdiPic::getPianoTerapeutico($interventi);

        $out['interventi'] = $datiPiano['out'];
        $out['da'] = $datiPiano['da'];
        $out['a'] = $datiPiano['a'];

        $out['distretto'] = str_replace("DI", "", $text1[count($text1) - 3]);
        $out['data'] = substr($text1[count($text1) - 1], 5, 10);
        return $out;
    }

    public static function estraiSottostringa($stringa, $inizio, $fine = null)
    {
        $posizioneInizio = strpos($stringa, $inizio);
        if ($posizioneInizio === false) {
            return ''; // Se non trova la stringa iniziale, ritorna una stringa vuota
        }

        $posizioneInizio += strlen($inizio); // Sposta la posizione all'ultimo carattere della stringa iniziale

        if ($fine === null) {
            return trim(substr($stringa, $posizioneInizio)); // Restituisce la stringa fino alla fine se $fine è null
        }

        $posizioneFine = strpos($stringa, $fine, $posizioneInizio);
        if ($posizioneFine === false) {
            return ''; // Se non trova la stringa finale, ritorna una stringa vuota
        }

        return trim(substr($stringa, $posizioneInizio, $posizioneFine - $posizioneInizio));
    }

    public static function deleteDirectory($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!Utils::deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }

        }

        return rmdir($dir);
    }
}