<?php
/**
 * Created by PHPStorm.
 * User: daemon
 * Date: 13.06.16
 * Time: 0:06
 */

use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;

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
        !Yii::$app->user->isGuest ? (
        ['label' => Yii::t('app', 'My uploads'), 'url' => ['site/uploads']]
        ) : '',
        Yii::$app->user->isGuest ? (
        ['label' => 'Login', 'url' => ['/site/login']]
        ) : (
        ['label' => 'Logout (' . Yii::$app->user->identity->first_name . ')', 'url' => ['/site/logout']]
        )
    ],
]);
NavBar::end();
?>