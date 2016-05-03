<?php

namespace app\controllers;

use app\models\FileLoader;
use Yii;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\UploadedFile;

class SiteController extends Controller
{

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index', [
            'model' => new FileLoader()
        ]);
    }

    public function actionFileUpload()
    {
        $file = UploadedFile::getInstanceByName('FileLoader[file]');
        $directory = Yii::getAlias('@webroot/media') . DIRECTORY_SEPARATOR . Yii::$app->session->id . DIRECTORY_SEPARATOR;
        if (!is_dir($directory)) {
            mkdir($directory);
        }
        if ($file) {
            $uid = uniqid(time(), true);
            $fileName = $uid . '.' . $file->extension;
            $filePath = $directory . $fileName;
            if ($file->saveAs($filePath)) {
                $path = '/media' . DIRECTORY_SEPARATOR . Yii::$app->session->id . DIRECTORY_SEPARATOR . $fileName;
                return Json::encode([
                    'files' => [[
                        'name' => $fileName,
                        'size' => $file->size,
                        "url" => $path,
                    ]]
                ]);
            }
        }
        return '';
    }

}
