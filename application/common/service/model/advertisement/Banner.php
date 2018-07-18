<?php

namespace app\common\service\model\advertisement;

use app\common\service\Model;

class Banner extends Model
{
    public function details(){
        return $this->hasMany('BannerDetail');
    }

    public function getStatusTextAttr($val, $data){
        return (int)$data['banner_status'] == 1 ? '是' : '否';
    }
}