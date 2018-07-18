<?php

namespace app\common\service\model\configuration;

use app\common\service\Model;
use think\db\Query;

class RecommendNavigation extends Model
{
    /**
     * @var 导航推荐位置
     */
    private static $positions;

    public function __construct($data = [])
    {
        parent::__construct($data);
        self::$positions = array(
            'common_top' => '通用顶部',
            'friend_link' => '友情链接'
        );
    }

    public static function getPositions()
    {
        return self::$positions;
    }

    public function scopeList(Query $query, $condition = null)
    {
        $where = [];
        if (!empty($condition) && is_array($condition)) {
            foreach ($condition as $field => $val) {
                switch ($field) {
                    case 'status':
                        $where[$field] = (int)$val;
                        break;
                    case 'position':
                        $where[$field] = trim($val);
                        break;
                    default:
                        break;
                }
            }
        }
        if (!empty($where)) {
            $query->where($where);
        }
        $query->field(true)->order([
            'position' => 'asc',
            'sort' => 'asc',
            'recommend_navigation_id' => 'asc'
        ]);
    }

    public function getPositionTextAttr($val, $data)
    {
        if (isset(self::$positions[$data['position']])) {
            return self::$positions[$data['position']];
        }

        return '';
    }

    public function getStatusTextAttr($val, $data)
    {
        return (int)$data['status'] == 1 ? '是' : '否';
    }

    public function getNewWindowOpenTextAttr($val, $data)
    {
        return (int)$data['new_window_open'] == 1 ? '是' : '否';
    }
}
