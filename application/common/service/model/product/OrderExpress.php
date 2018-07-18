<?php

namespace app\common\service\model\product;

use app\common\service\Model;

class OrderExpress extends Model
{
    public function user()
    {
        return $this->belongsTo('app\\common\service\\model\\user\\User');
    }

    public function shippingAddress()
    {
        return $this->belongsTo('app\\common\service\\model\\address\\ShippingAddress');
    }
}