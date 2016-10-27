<?php
/**
 * Created by PHPStorm.
 * User: daemon
 * Date: 13.06.16
 * Time: 0:13
 */

/* @var $this View */
/* @var $dataProvider yii\data\ActiveDataProvider */

use app\models\Files;
use yii\grid\DataColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],

        [
            'class' => DataColumn::className(),
            'attribute' => 'title',
            'format' => 'ntext',
            'content' => function($model, $key, $index, $column) {
                if ($model->loading_state == Files::LOADING_STATE_IN_PROCESS)
                    return $model->title;
                else
                    return Html::a($model->title, ['site/get', 'shortlink' => $model->shortlink]);
            }
        ],
        'size',
        'uploaded_at:datetime',

        [
            'class' => 'yii\grid\ActionColumn',
            'buttons' => [
                'update' => function($url, $model, $key) {
                    /*return Html::a('<span class="glyphicon glyphicon-pencil"></span>', ['site/uploads-update', 'id' => $key], [
                        'title' => Yii::t('yii', 'Update'),
                        'aria-label' => Yii::t('yii', 'Update'),
                        'data-pjax' => '0',
                    ]);*/
                },

                'view' => function($url, $model, $key) {
                    /*return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', ArrayHelper::merge(['admin-panel/l10n-view'], $key), [
                        'title' => Yii::t('yii', 'View'),
                        'aria-label' => Yii::t('yii', 'View'),
                        'data-pjax' => '0',
                    ]);*/
                },

                'delete' => function ($url, $model, $key) {
                    return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['site/uploads-delete', 'id' => $key], [
                        'title' => Yii::t('yii', 'Delete'),
                        'aria-label' => Yii::t('yii', 'Delete'),
                        'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                        'data-method' => 'post',
                        'data-pjax' => '0',
                    ]);
                },
            ]
        ],
    ],
]);