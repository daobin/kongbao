<?php

namespace app\common\service\model\article;

use app\common\service\Model;
use think\db\Query;

class Article extends Model
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
                    case 'category_id':
                    case 'is_recommend':
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
                    case 'article_id':
                        $order[$field] = $pOrder;
                    default:
                        break;
                }
            }
        }

        if (empty($order)) {
            $order = [
                'sort' => 'asc',
                'article_id' => 'desc'
            ];
        }

        $query->order($order);
    }

    public function category(){
        return $this->belongsTo('Category');
    }

    public function getStatusTextAttr($val, $data){
        return (int)$data['status'] == 1 ? '是' : '否';
    }

    public function getIsRecommendTextAttr($val, $data){
        return (int)$data['is_recommend'] == 1 ? '是' : '否';
    }
}