<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    '__alias__' => [
        'kbadmin/' => 'admin/'
    ],
    '[kbadmin]' => [
        '$' => \app\common\constant\Route::ADMIN_HOME,
        'login' => \app\common\constant\Route::ADMIN_LOGIN,
        'logout' => \app\common\constant\Route::ADMIN_LOGOUT,
        ':controller/:action' => 'admin/:controller/:action'
    ],

    '/' => \app\common\constant\Route::HOME,
    '/index/r/:r_id$' => \app\common\constant\Route::HOME,
    '/article/list-<category_id>' => [
        \app\common\constant\Route::ARTICLE_LIST,
        [],
        ['category_id' => '\d+']
    ],
    '/article/info-<article_id>' => [
        \app\common\constant\Route::ARTICLE_INFO,
        [],
        ['article_id' => '\d+']
    ],
    '/register' => \app\common\constant\Route::USER_REGISTER,
    '/login' => \app\common\constant\Route::USER_LOGIN,
    '/logout' => \app\common\constant\Route::USER_LOGOUT,
    '/user' => \app\common\constant\Route::USER_INDEX,
    '/user/recommend' => \app\common\constant\Route::USER_RECOMMEND,
    '/user/password' => \app\common\constant\Route::USER_PASSWORD,
    '/user/api' => \app\common\constant\Route::USER_API,
    '/user/address' => \app\common\constant\Route::VOCATION_WORK_SHIPPING_ADDRESS,
    '/user/buy' => \app\common\constant\Route::VOCATION_WORK_BUY_EXPRESS,
    '/user/order' => \app\common\constant\Route::VOCATION_WORK_MY_EXPRESS,
    '/user/buy-flow' => \app\common\constant\Route::VOCATION_WORK_BUY_FLOW,
    '/user/my-flow' => \app\common\constant\Route::VOCATION_WORK_MY_FLOW,
    '/user/buy-collection' => \app\common\constant\Route::VOCATION_WORK_BUY_COLLECTION,
    '/user/my-collection' => \app\common\constant\Route::VOCATION_WORK_MY_COLLECTION,
    '/user/order-document' => \app\common\constant\Route::VOCATION_WORK_ORDER_DOCUMENT_APPLY,
    '/user/my-order-document' => \app\common\constant\Route::VOCATION_WORK_MY_ORDER_DOCUMENT,
    '/user/upload-order-number' => \app\common\constant\Route::VOCATION_WORK_UPLOAD_ORDER_NUMBER,
];
