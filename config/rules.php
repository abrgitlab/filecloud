<?php
/**
 * Created by PHPStorm.
 * User: daemon
 * Date: 02.05.16
 * Time: 23:10
 */

return [
    'file/upload' => 'site/file-upload',
    'file/upload2' => 'site/file-upload2',
    'file/get/<shortlink:[a-zA-Z0-9]+>' => 'site/get',
    'uploads/<filter:[a-zA-Z]+>' => 'site/uploads',
    'uploads/delete/<id:[0-9]+>' => 'site/uploads-delete',
    'uploads' => 'site/uploads',
    'login' => 'site/login',
    'logout' => 'site/logout',

    'api/file/upload' => 'api/file-upload',
    'api/file/upload2' => 'api/file-upload2',
//    'api/uploads/<filter:[a-zA-Z]+>' => 'api/uploads',
    'api/uploads' => 'api/uploads',
    'api/login' => 'api/login',

//    'test' => 'site/test',
];