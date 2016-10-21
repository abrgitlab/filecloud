<?php

namespace app\controllers;

use app\models\FileLoader;
use app\models\LoginForm;
use app\models\Users;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
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
                        'actions' => ['index', 'file-upload', 'uploads', 'uploads-delete', 'logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'uploads-delete' => ['POST'],
                ],
            ],
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
                'alias' => 'downloads',
                'title' => Yii::t('app', 'Archives'),
                'icon' => '',
            ],
            [
                'alias' => 'other',
                'title' => Yii::t('app', 'Other'),
                'icon' => ''
            ],
        ];

        $dataProvider = new ActiveDataProvider([
            'query' => Files::find()->where(['user_id' => Yii::$app->user->id]),
        ]);

        return $this->render("uploads/$filter", [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionFileUpload() {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return Files::uploadFile();
    }

    public function actionGet($shortlink) {
        $file = Files::findOne(['shortlink' => $shortlink, 'loading_state' => Files::LOADING_STATE_LOADED]);
        if ($file) {
            $path = Yii::getAlias('@webroot/media') . DIRECTORY_SEPARATOR . $file->shortlink;
            if (file_exists($path)) {
                Yii::$app->response->sendFile($path, $file->title);
                return;
            }
        }
        throw new NotFoundHttpException();
    }

    /**
     * Updates an existing Files model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id) {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Files model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUploadsDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['site/uploads']);
    }

    /**
     * Finds the Files model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Files the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = Files::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
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
