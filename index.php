<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]

$database_name = strtolower(trim($_SERVER['HTTP_HOST']));
$database_name = explode('.', $database_name);
$database_name_count = count($database_name);
if ($database_name_count >= 2) {
    $database_name = trim($database_name[$database_name_count - 2]) . '_' . trim($database_name[$database_name_count - 1]);
    define('CURRENT_DATABASE_NAME', $database_name);
} else {
    die('System Error!!!');
}

// 定义应用目录
define('APP_PATH', __DIR__ . '/application/');
// 加载框架引导文件
require __DIR__ . '/thinkphp/start.php';
