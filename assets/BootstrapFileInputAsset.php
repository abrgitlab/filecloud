<?php
/**
 * Created by PHPStorm.
 * User: daemon
 * Date: 02.05.16
 * Time: 23:45
 */

namespace app\assets;

use Yii;
use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class BootstrapFileInputAsset extends AssetBundle
{
    public $sourcePath = '@vendor/kartik-v/bootstrap-fileinput';

    public $css = [
        'css/fileinput.min.css',
    ];
    public $js = [
        'js/fileinput.min.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
    ];

    function init()
    {
        parent::init();
        $this->js[] = 'js/fileinput_locale_' . Yii::$app->language . '.js';
    }
}
