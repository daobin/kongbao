<?php

namespace app\common\service\model\product;

use app\common\service\Model;

class OrderCollection extends Model
{
    public function user()
    {
        return $this->belongsTo('app\\common\service\\model\\user\\User');
    }

    public function getStatusTextAttr($val, $data)
    {
        $statusText = '';
        switch ((int)$data['status']) {
            case 1:
                $statusText = '待处理';
                break;
            case 2:
                $statusText = '处理中';
                break;
            case 3:
                $statusText = '已处理';
                break;
            case 4:
                $statusText = '已取消';
                break;
        }

        return $statusText;
    }
}