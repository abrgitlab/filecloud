<?php

namespace app\controllers;

use app\models\FileLoader;
use Yii;
use yii\web\Controller;
use app\models\Files;
use yii\web\NotFoundHttpException;

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
        return Files::uploadFile();
    }

    public function actionGet($shortlink) {
        $file = Files::findOne(['shortlink' => $shortlink, 'loading_state' => Files::LOADING_STATE_LOADED]);
        $path = Yii::getAlias('@webroot/media') . DIRECTORY_SEPARATOR . $file->shortlink;
        if ($file && file_exists($path)) {
            Yii::$app->response->sendFile($path, $file->title);
        } else {
            throw new NotFoundHttpException();
        }
    }

}
