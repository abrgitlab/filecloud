<?php

/* @var $this yii\web\View */

use app\assets\AppAsset;

$this->title = 'Abr Daemon file cloud';

AppAsset::register($this);

?>

<div class="container">
    <label class="control-label"><?= Yii::t('app', 'Select Files') ?></label>
    <input id="input-1a" type="file" class="file" multiple data-show-preview="false">
</div>