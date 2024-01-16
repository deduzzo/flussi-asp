<?php
namespace app\models;

use yii\base\Model;
use yii\web\UploadedFile;

class FileUpload extends Model
{
    /**
     * @var UploadedFile
     */
    public $file;

    public function rules()
    {
        return [
            [['file'], 'file', 'skipOnEmpty' => false, 'extensions' => 'pdf'],
        ];
    }
}