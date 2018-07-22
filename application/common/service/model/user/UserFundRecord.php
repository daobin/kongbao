<?php

namespace app\common\service\model\user;

use app\common\service\Model;

class UserFundRecord extends Model
{
    public function user(){
        return $this->belongsTo('User');
    }

    public function getTypeCodeAttr($val, $data)
    {
        return (int)$data['type'] == 2 ? '-' : '';
    }

    public function getTypeTextAttr($val, $data)
    {
        if ((int)$data['type'] == 1) {
            return '奖励';
        } else if ((int)$data['type'] == 2) {
            return '惩罚';
        } else {
            return '';
        }
    }

    public static function getGroups()
    {
        return [
            //0, 1, 2
            '', '官方', '充值',
            //3, 4, 5
            '购买空包', '购买流量', '购买收藏',
            //6, 7, 8
            '空包退款', '流量退款', '收藏退款',
            //9, 10, 11
            '升级VIP会员', '升级代理会员', '推荐奖励',
            //12, 13, 14
            '充值奖励', '', '',
        ];
    }

    public function getGroupTextAttr($val, $data)
    {
        $groups = self::getGroups();
        return isset($groups[(int)$data['group']]) ? trim($groups[(int)$data['group']]) : '';
    }
}