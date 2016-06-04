<?php

/* @var $this View */
/* @var $model FileLoader */

use app\models\FileLoader;
use dosamigos\fileupload\FileUploadUI;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\web\View;

$this->title = Yii::$app->params['productName'];

?>

<?php
NavBar::begin([
    'brandLabel' => Yii::$app->params['companyName'],
    'brandUrl' => Yii::$app->homeUrl,
    'options' => [
        'class' => 'navbar navbar-default',
    ],
]);
echo Nav::widget([
    'options' => ['class' => 'navbar-nav navbar-right'],
    'items' => [
//        ['label' => 'Мои загрузки', 'url' => ['site/uploads']],
        Yii::$app->user->isGuest ? (
        ['label' => 'Login', 'url' => ['/site/login']]
        ) : (
        ['label' => 'Logout (' . Yii::$app->user->identity->first_name . ')', 'url' => ['/site/logout']]
        )
    ],
]);
NavBar::end();
?>

<div class="container">
    <?= FileUploadUI::widget([
        'model' => $model,
        'attribute' => 'file',
        'url' => ['file/upload', 'id' => $model->id],
        'fieldOptions' => [
            'accept' => '*/*'
        ],
        'clientOptions' => [
            'maxChunkSize' => 500000,
            'disableImagePreview' => true,
        ],
    ]);
    ?>
</div>
