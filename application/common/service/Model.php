<?php
/**
 * 所有服务模型基类
 */

namespace app\common\service;

class Model extends \think\Model
{
    protected $autoWriteTimestamp = true;
    protected $createTime = 'gmt_create';
    protected $updateTime = 'gmt_modify';
    protected $insert = [
        'gmt_create' => NOW_DATE_TIME,
        'gmt_modify' => NOW_DATE_TIME
    ];
    protected $update = [
        'gmt_modify' => NOW_DATE_TIME
    ];
    protected $type = [
        'gmt_create' => 'datetime',
        'gmt_modify' => 'datetime'
    ];
}