<?php
/**
 * Created by PhpStorm.
 * User: daemon
 * Date: 16.05.16
 * Time: 16:49
 */

namespace app\commands;

use app\models\Users;
use Yii;
use yii\base\Security;
use yii\console\Controller;

class RbacController extends Controller
{

    public function actionInit() {
        $auth = Yii::$app->authManager;

        if (!($common_user = $auth->getRole('commonUser'))) {
            $common_user = $auth->createRole('commonUser');
            $auth->add($common_user);
        }

        $user_1 = Users::findOne(['email' => 'abr_mail@mail.ru']);

        if (!$user_1) {
            $user_1 = new Users();
            $user_1->login = 'abr_filecloud';
            $user_1->email = 'abr_mail@mail.ru';
            $user_1->password = '82bedeb2bd324a1c45a25a7626f9518c';
            $user_1->first_name = 'Дмитрий';
            $user_1->last_name = 'Малахов';
            $user_1->secret = Yii::$app->getSecurity()->generateRandomString(16);
            $user_1->active = 1;

            $user_1->save();

            $auth->assign($common_user, $user_1->id);
        }
    }

    public function actionAssignUser($userid) {
        $auth = Yii::$app->authManager;

        $common_user = $auth->getRole('commonUser');

        $auth->assign($common_user, $userid);
    }

}