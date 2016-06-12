<?php

/* @var $this View */
/* @var $content string */

use app\assets\AppAsset;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

AppAsset::register($this);

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<?= $this->params['navbar'] ?>

<div class="container">
    <div class="row sidebar">
        <div class="col-sm-3">
            <div class="sidebar-menu">
                <ul class="nav">
                    <?php foreach ($this->params['menu_items'] as $menu_item) { ?>
                        <li<?= ($this->params['filter'] == $menu_item['alias']) ? ' class="active"' : ''?>>
                            <?= Html::a(
                                Html::tag('i', '', ['class' => 'glyphicon' . ((empty($menu_item['icon'])) ? '' : ' glyphicon-' . $menu_item['icon'])]) . $menu_item['title'],
                                Url::to(['site/uploads', 'filter' => $menu_item['alias']])
                            ) ?>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
        <div class="col-sm-9">
            <div class="admin-content">
                <?= $content ?>
            </div>
        </div>
    </div>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
