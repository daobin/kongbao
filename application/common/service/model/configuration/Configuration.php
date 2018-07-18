<?php

namespace app\common\service\model\configuration;

use app\common\service\Model;
use think\db\Query;

class Configuration extends Model
{
    public function scopeList(Query $query, $condition = null)
    {
        $where = [];
        if (!empty($condition) && is_array($condition)) {
            foreach ($condition as $field => $val) {
                switch ($field) {
                    case 'group':
                        $where[$field] = trim($val);
                        break;
                }
            }
        }
        if (!empty($where)) {
            $query->where($where);
        }
        $query->field(true)->order(['sort' => 'asc', 'configuration_id' => 'asc']);
    }
}