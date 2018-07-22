<?php

namespace app\ajax\controller\index;

use app\common\controller\Ajax;
use app\common\service\model\user\UserFundRecord;

class Finance extends Ajax
{
    public function getFundRecord()
    {
        $dateStart = input('get.date_start/s', '');
        $dateEnd = input('get.date_end/s', '');
        $groupText = input('get.group_text/s', '');

        if ($dateStart != '' && !chk_date_format($dateStart, 'Y-m-d')) {
            $this->echoDataFormatToJson([
                'status' => 'fail',
                'msg' => '消费开始时间无效'
            ]);
        }
        if ($dateEnd != '' && !chk_date_format($dateEnd, 'Y-m-d')) {
            $this->echoDataFormatToJson([
                'status' => 'fail',
                'msg' => '消费结束时间无效'
            ]);
        }
        if ($dateStart != '' && $dateEnd != '' && $dateStart > $dateEnd) {
            $this->echoDataFormatToJson([
                'status' => 'fail',
                'msg' => '消费开始时间不得超过结束时间'
            ]);
        }

        $where = [];
        if ($dateStart != '') {
            $where['gmt_create'] = ['>=', date('Y-m-d 00:00:00', strtotime($dateStart))];
        }
        if ($dateEnd != '') {
            $where['gmt_create'] = ['<=', date('Y-m-d 23:59:59', strtotime($dateEnd))];
        }
        switch ($groupText) {
            case 'official':
                $where['group'] = 1;
                break;
            case 'recharge':
                $where['group'] = 2;
                break;
            case '购买':
                $where['group'] = ['in', [3, 4, 5]];
                break;
            case 'refund':
                $where['group'] = ['in', [6, 7, 8]];
                break;
            case 'upgrade':
                $where['group'] = ['in', [9, 10]];
                break;
            case 'other':
                $where['group'] = ['>', 10];
                break;
            default:
                break;
        }

        $this->assign('fund_records', UserFundRecord::where($where)->field(true)
            ->order('user_fund_record_id', 'desc')
            ->paginate(10, false, ['type' => 'kongbao']));

        $this->echoDataFormatToJson([
            'status' => 'success',
            'data' => $this->fetch()
        ]);
    }
}