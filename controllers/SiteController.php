<?php

namespace app\controllers;

use app\models\FileLoader;
use Yii;
use yii\web\Controller;
use app\models\Files;

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
        $file = new Files();
        return $file->uploadFile();
    }

    public function actionGet($shortlink) {

    }

}
