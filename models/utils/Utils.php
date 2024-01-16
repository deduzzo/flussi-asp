<?php

namespace app\models\utils;

class Utils
{
    public static function validatePdf($text) {
        $text = strtolower($text);
        if (str_contains($text, 'assistito') &&
            str_contains($text, 'cognome') &&
            str_contains($text, 'codice fiscale') &&
            str_contains($text, 'firma del responsabile'))
            return true;
        else
            return false;
    }
}