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
use yii\filters\auth\HttpBearerAuth;
use yii\filters\ContentNegotiator;
use yii\rest\Controller;
use yii\web\Response;

class ApiController extends Controller {

    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' =>HttpBearerAuth::className(),
            'except' => ['login'],
        ];
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::className(),
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];
        $behaviors['access'] = [
            'class' => AccessControl::className(),
                'except' => ['get', 'error'/*, 'test'*/],
            'rules' => [
                [
                    'actions' => ['login'],
                    'allow' => true,
                    'roles' => ['?'],
                ],
//                [
//                    'actions' => ['index', 'file-upload', 'uploads', 'logout'],
//                    'allow' => true,
//                    'roles' => ['@'],
//                ],
            ],
        ];
        return $behaviors;
//        return [
//            'access' => [
//                'class' => AccessControl::className(),
//                'except' => ['get', 'error'/*, 'test'*/],
//                'rules' => [
//                    [
//                        'actions' => ['login'],
//                        'allow' => true,
//                        'roles' => ['?'],
//                    ],
//                    [
//                        'actions' => ['index', 'file-upload', 'uploads', 'logout'],
//                        'allow' => true,
//                        'roles' => ['@'],
//                    ],
//                ],
//            ]
//        ];
    }

    public function init() {
        Yii::$app->user->enableSession = false;
        parent::init();
    }

    public function actions() {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionLogin() {
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->get(), '') && $model->login()) {
            return ['access_token' => Yii::$app->user->identity->getAuthKey()];
        } else {
            $model->validate();
            return $model;
        }
    }

}