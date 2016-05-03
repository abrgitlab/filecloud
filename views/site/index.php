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
        'url' => ['media/upload', 'id' => $model->id],
        'gallery' => false,
        'fieldOptions' => [
            'accept' => '*/*'
        ],
        'clientOptions' => [
            'maxFileSize' => 20000000
        ],
        // ...
        'clientEvents' => [
            'fileuploaddone' => 'function(e, data) {
                                    console.log(e);
                                    console.log(data);
                                }',
            'fileuploadfail' => 'function(e, data) {
                                    console.log(e);
                                    console.log(data);
                                }',
        ],
    ]);
    ?>
</div>