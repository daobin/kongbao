<?php

namespace app\common\service\model\article;

use app\common\service\Model;
use think\db\Query;

class Category extends Model
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
                    case 'status':
                        $where[$field] = (int)$val;
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
        $order = [];
        if (!empty($pOrders) && is_array($pOrders)) {
            foreach ($pOrders as $field => $pOrder) {
                $pOrder = strtolower($pOrder);
                if ($pOrder != 'asc' && $pOrder != 'desc') {
                    continue;
                }

                switch ($field) {
                    case 'sort':
                    case 'gmt_create':
                        $order[$field] = $pOrder;
                    default:
                        break;
                }
            }
        }

        if (empty($order)) {
            $order = [
                'sort' => 'asc',
                'category_id' => 'asc'
            ];
        }

        $query->order($order);
    }

    public function articles(){
        return $this->hasMany('Article');
    }
}