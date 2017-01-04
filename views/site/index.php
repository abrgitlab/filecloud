<?php

/* @var $this View */
/* @var $model FileLoader */

use app\models\FileLoader;
use dosamigos\fileupload\FileUploadUI;
use yii\web\View;

$this->title = Yii::t('app', Yii::$app->params['productName']);

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


<?php
$form = \yii\widgets\ActiveForm::begin(['action' => 'file/upload2', 'options' => ['enctype' => 'multipart/form-data']]);
?>

<?= $form->field($model, 'file')->fileInput() ?>

<input type="submit">

<?php \yii\widgets\ActiveForm::end() ?>