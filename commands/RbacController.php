<?php
/**
 * Created by PhpStorm.
 * User: daemon
 * Date: 16.05.16
 * Time: 16:49
 */

namespace app\commands;

use Yii;
use yii\console\Controller;

class RbacController extends Controller
{

    public function actionInit() {
        $auth = Yii::$app->authManager;

        if (!($common_user = $auth->getRole('commonUser'))) {
            $common_user = $auth->createRole('commonUser');
            $auth->add($common_user);
        }
    }

    public function actionAssignUser($userid) {
        $auth = Yii::$app->authManager;

        $common_user = $auth->getRole('commonUser');

        $auth->assign($common_user, $userid);
    }

}