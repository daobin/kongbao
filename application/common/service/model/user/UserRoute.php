<?php

namespace app\common\service\model\user;

use app\common\constant\Route;
use app\common\service\Model;

class UserRoute extends Model
{
    public static function getCenterNavs()
    {
        return [
            self::getVocationalNavs(),
            self::getFinanceNavs(),
            self::getAccountManagementNavs()
        ];
    }

    private static function getFinanceNavs(){
        return [
            'group_name' => '财务管理',
            'group_key' => 'finance_management',
            'navs' => [
                [
                    'name' => '账号充值',
                    'route' => Route::FINANCE_ACCOUNT_RECHARGE,
                ],
                [
                    'name' => '资金明细',
                    'route' => Route::FINANCE_FUND_RECORD
                ],
                [
                    'name' => '充值记录',
                    'route' => Route::FINANCE_RECHARGE_RECORD
                ]
            ]
        ];
    }

    private static function getVocationalNavs()
    {
        return [
            'group_name' => '我的业务',
            'group_key' => 'vocational_work',
            'navs' => [
                [
                    'name' => '发货地址',
                    'route' => Route::VOCATION_WORK_SHIPPING_ADDRESS
                ],
                [
                    'name' => '购买空包',
                    'route' => Route::VOCATION_WORK_BUY_EXPRESS
                ],
                [
                    'name' => '已买空包',
                    'route' => Route::VOCATION_WORK_MY_EXPRESS
                ],
                [
                    'name' => '购买流量',
                    'route' => Route::VOCATION_WORK_BUY_FLOW
                ],
                [
                    'name' => '已买流量',
                    'route' => Route::VOCATION_WORK_MY_FLOW
                ],
                [
                    'name' => '购买收藏',
                    'route' => Route::VOCATION_WORK_BUY_COLLECTION
                ],
                [
                    'name' => '已买收藏',
                    'route' => Route::VOCATION_WORK_MY_COLLECTION
                ],
                [
                    'name' => '我要底单',
                    'route' => Route::VOCATION_WORK_ORDER_DOCUMENT_APPLY
                ],
                [
                    'name' => '我的底单',
                    'route' => Route::VOCATION_WORK_MY_ORDER_DOCUMENT
                ],
                [
                    'name' => '上传单号',
                    'route' => Route::VOCATION_WORK_UPLOAD_ORDER_NUMBER
                ]
            ]
        ];
    }

    private static function getAccountManagementNavs()
    {
        return [
            'group_name' => '账号管理',
            'group_key' => 'account_management',
            'navs' => [
                [
                    'name' => '推广奖励',
                    'route' => Route::USER_RECOMMEND,
                ],
                [
                    'name' => '修改密码',
                    'route' => Route::USER_PASSWORD
                ],
                [
                    'name' => '接口说明',
                    'route' => Route::USER_API
                ],
                [
                    'name' => '退出登录',
                    'route' => Route::USER_LOGOUT
                ]
            ]
        ];
    }
}