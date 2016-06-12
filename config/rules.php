<?php
/**
 * Created by PHPStorm.
 * User: daemon
 * Date: 02.05.16
 * Time: 23:10
 */

return [
    'file/upload' => 'site/file-upload',
    'file/get/<shortlink:[a-zA-Z]+>' => 'site/get',
    'uploads/<filter:[a-zA-Z]+>' => 'site/uploads',
    'uploads' => 'site/uploads',
    'login' => 'site/login',
    'logout' => 'site/logout',

    'api/login' => 'api/login',
//    'api/uploads/<filter:[a-zA-Z]+>' => 'api/uploads',
    'api/uploads' => 'api/uploads',

//    'test' => 'site/test',
];