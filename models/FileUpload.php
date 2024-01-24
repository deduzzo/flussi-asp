<?php
namespace app\models;

use yii\base\Model;
use yii\web\UploadedFile;

class FileUpload extends Model
{
    const SCENARIO_SINGLE = 'single';
    const SCENARIO_MULTIPLE = 'multiple';

    /**
     * @var UploadedFile|UploadedFile[] Carica un singolo file o più file
     */
    public $file;
    public $nuovo = false;

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_SINGLE] = ['file', 'nuovo'];
        $scenarios[self::SCENARIO_MULTIPLE] = ['file', 'nuovo'];
        return $scenarios;
    }

    public function rules()
    {
        return [
            [['file'], 'file', 'skipOnEmpty' => true, 'extensions' => 'pdf', 'on' => self::SCENARIO_SINGLE],
            [['file'], 'each', 'rule' => ['file', 'skipOnEmpty' => true, 'extensions' => 'pdf'], 'on' => self::SCENARIO_MULTIPLE],
            [['nuovo'], 'boolean'],
        ];
    }
}