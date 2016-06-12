<?php

namespace app\controllers;

use app\models\FileLoader;
use app\models\LoginForm;
use app\models\Users;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use app\models\Files;
use yii\web\NotFoundHttpException;
use yii\web\Response;

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
                        'actions' => ['index', 'file-upload', 'uploads', 'logout'],
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
        if (!Yii::$app->user->isGuest) {
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

    public function actionUploads($filter = 'all') {
        $this->layout = 'uploads';

        $this->view->params['filter'] = $filter;
        $this->view->params['menu_items'] = [
            [
                'alias' => 'all',
                'title' => Yii::t('app', 'All'),
                'icon' => '',
            ],
            [
                'alias' => 'docs',
                'title' => Yii::t('app', 'Documents'),
                'icon' => ''
            ],
            [
                'alias' => 'pics',
                'title' => Yii::t('app', 'Pictures'),
                'icon' => ''
            ],
            [
                'alias' => 'audio',
                'title' => Yii::t('app', 'Audio'),
                'icon' => ''
            ],
            [
                'alias' => 'video',
                'title' => Yii::t('app', 'Video'),
                'icon' => ''
            ],
            [
                'alias' => 'downloads',
                'title' => Yii::t('app', 'Downloads'),
                'icon' => ''
            ],
            [
                'alias' => 'other',
                'title' => Yii::t('app', 'Other'),
                'icon' => ''
            ],
        ];

        return $this->render("uploads/$filter");
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

    public function render($view, $params = []) {
        if ($this->layout == null || $this->layout == 'main' || $this->layout == 'uploads')
            $this->view->params['navbar'] = $this->renderPartial('navbar');

        return parent::render($view, $params);
    }

}
