<?php

namespace app\common\service\model\user;

use app\common\service\Model;

class UserPrice extends Model
{
    public function price(){
        return $this->belongsTo('Price');
    }
}