<?php

namespace app\common\service\model\advertisement;

use app\common\service\Model;

class BannerDetail extends Model
{
    public function getNewWindowOpenTextAttr($val, $data)
    {
        return (int)$data['new_window_open'] == 1 ? '是' : '否';
    }
}