<?php

/* @var $this yii\web\View */
/* @var $model app\models\FileLoader */

use app\assets\AppAsset;
use dosamigos\fileupload\FileUploadUI;

$this->title = 'Abr Daemon file cloud';

AppAsset::register($this);

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
        ],
    ]);
    ?>
</div>