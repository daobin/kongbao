<?php

namespace app\common\service\model\advertisement;

use app\common\service\Model;

class Text extends Model
{
    /**
     * @var 文案展示位置
     */
    private static $positions;

    public function __construct($data = [])
    {
        parent::__construct($data);
        self::$positions = array(
            'common_top' => '顶部公告',
            'user_center' => '会员中心',
            'buy_kongbao' => '购买空包',
            'buyed_kongbao' => '已买空包',
            'shipping_address_set' => '收货地址设置',
            'order_document_apply' => '底单申请',
            'auto_recharge' => '自动充值',
            'upgrade_vip' => '升级VIP',
            'my_subordinate' => '我的下线'
        );
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
}