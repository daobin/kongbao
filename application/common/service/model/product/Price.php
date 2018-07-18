<?php

namespace app\common\service\model\product;

use app\common\service\Model;
use think\db\Query;

class Price extends Model
{
    public function scopeList(Query $query, $condition = null)
    {
        $orders = [];
        if (isset($condition['orders'])) {
            $orders = $condition['orders'];
            unset($condition['orders']);
        }
        $this->scopeOrder($query, $orders);

        $where = [];
        if (!empty($condition) && is_array($condition)) {
            foreach ($condition as $field => $val) {
                switch ($field) {
                    case 'group':
                        $where[$field] = trim($val);
                        break;
                    case 'status':
                    case 'type_id':
                        $where[$field] = (int)$val;
                        break;
                    default:
                        break;
                }
            }
        }
        if (!empty($where)) {
            $query->where($where);
        }
    }

    public function scopeOrder(Query $query, $pOrders = null)
    {
        $orders = [];
        if (!empty($pOrders) && is_array($pOrders)) {
            foreach ($pOrders as $field => $pOrder) {
                $pOrder = strtolower($pOrder);
                if ($pOrder != 'asc' && $pOrder != 'desc') {
                    continue;
                }

                switch ($field) {
                    case 'type_id':
                    case 'sort':
                    case 'gmt_create':
                        $orders[$field] = $pOrder;
                    default:
                        break;
                }
            }
        }

        if (empty($orders)) {
            $orders = [
                'sort' => 'asc',
                'price_id' => 'asc'
            ];
        }

        $query->order($orders);
    }
}