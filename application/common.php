<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

//判断一个格式化时间是否合法
function chk_date_format($datetime, $format = 'Y-m-d H:i:s'){
    $unix_time = strtotime($datetime);
    if(empty($unix_time)){
        return false;
    }

    return date($format, $unix_time) == $datetime;
}