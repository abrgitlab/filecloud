<?php
/**
 * Created by PHPStorm.
 * User: daemon
 * Date: 06.06.16
 * Time: 22:49
 */

namespace app\controllers;


use app\models\LoginForm;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

class MobileController extends Controller {

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
                        'actions' => ['index', 'file-upload', 'uploads', 'logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ]
        ];
    }

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;

        return parent::beforeAction($action);
    }

    public function actions() {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionLogin() {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!Yii::$app->user->isGuest) {
            return [
                'status' => 'error',
                'description' => 'authorized',
            ];
        }

        $model = new LoginForm();
        $model->load(Yii::$app->request->post());
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return [
                'status' => 'success',
            ];
        }
        return [
            'status' => 'error',
            'description' => $model->getErrors(),
        ];
    }

}