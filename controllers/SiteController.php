<?php

namespace app\controllers;

use app\models\FileLoader;
use app\models\LoginForm;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use app\models\Files;
use yii\web\NotFoundHttpException;

class SiteController extends Controller
{

    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'except' => ['get', 'error'/*, 'test'*/],
                'rules' => [
                    [
                        'actions' => ['login'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['index', 'file-upload', 'logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ]
        ];
    }

    public function actions() {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionLogin() {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionLogout() {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionIndex() {
        return $this->render('index', [
            'model' => new FileLoader()
        ]);
    }

    public function actionUploads() {

    }

    public function actionFileUpload() {
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

//    public function actionTest() {
//        var_dump(Yii::$app->user->getId());die;
//    }

}
