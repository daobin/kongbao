<?php

namespace app\common\service\model\address;

use app\common\service\Model;
use think\db\Query;

class ShippingAddress extends Model
{
    public function scopeList(Query $query, $condition = null)
    {
        $where = [];
        if (!empty($condition) && is_array($condition)) {
            foreach ($condition as $field => $val) {
                switch ($field) {
                    case 'user_id':
                    case 'is_delete':
                        $where[$field] = (int)$val;
                        break;
                    case 'search':
                        $query->whereOr(function (Query $query) use ($val) {
                            $query->where('name', '=', $val)
                                ->whereOr('telephone', '=', $val)
                                ->whereOr('province_name', 'like', "%{$val}%")
                                ->whereOr('city_name', 'like', "%{$val}%")
                                ->whereOr('district_name', 'like', "%{$val}%")
                                ->whereOr('address', 'like', "%{$val}%");
                        });
                        break;
                    default:
                        break;
                }
            }
        }

        if (!empty($where)) {
            $query->where($where);
        }

        $query->order(['is_default' => 'desc', 'shipping_address_id' => 'desc']);
    }
}