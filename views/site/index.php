<?php

/* @var $this yii\web\View */
/* @var $model app\models\FileLoader */

use dosamigos\fileupload\FileUploadUI;

$this->title = 'Abr Daemon file cloud';

?>

<div class="row">
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