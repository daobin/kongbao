<?php

namespace app\admin\widget;

use app\common\constant\Route;
use app\common\controller\Widget;

class Header extends Widget
{
    public function index()
    {
        $this->assign('time_greeting', date('A') == 'AM' ? '上午好' : '下午好');
        $this->assignNavigation();
        return $this->fetch('widget/header/index');
    }

    private function assignNavigation()
    {
        $navs = [
            [
                'name' => '管理首页',
                'url' => url(Route::ADMIN_HOME)
            ],
            $this->getNavOfWebsite(),
            $this->getNavOfArticle(),
            $this->getNavOfUser(),
            $this->getNavOfPrice(),
            $this->getNavOfOrder(),
            $this->getNavOfDistributor(),
            $this->getNavOfAdmin(),
        ];

        $this->assign('navs', $navs);
    }

    private function getNavOfWebsite()
    {
        return [
            'name' => '网站管理',
            'sub_navs' => [
                [
                    'name' => '网站配置',
                    'url' => url(Route::ADMIN_WEBSITE)
                ],
                [
                    'name' => '推荐导航',
                    'url' => url(Route::ADMIN_RECOMMEND_NAVIGATION)
                ],
                [
                    'name' => '图文广告',
                    'url' => url(Route::ADMIN_ADVERTISEMENT)
                ],
                [
                    'name' => '客服QQ',
                    'url' => url(Route::ADMIN_CUSTOMER_SERVICE_OF_QQ)
                ],
            ]
        ];
    }

    private function getNavOfArticle()
    {
        return [
            'name' => '分类文章',
            'sub_navs' => [
                [
                    'name' => '分类管理',
                    'url' => url(Route::ADMIN_CATEGORY)
                ],
                [
                    'name' => '文章管理',
                    'url' => url(Route::ADMIN_ARTICLE)
                ]
            ]
        ];
    }

    private function getNavOfUser()
    {
        return [
            'name' => '会员管理',
            'sub_navs' => [
                [
                    'name' => '会员列表',
                    'url' => url(Route::ADMIN_USER)
                ],
                [
                    'name' => '左侧导航',
                    'url' => url(Route::ADMIN_USER_CENTER_NAV)
                ],
                [
                    'name' => '奖罚记录',
                    'url' => url(Route::ADMIN_USER_FUND_RECORDS)
                ]
            ]
        ];
    }

    private function getNavOfPrice()
    {
        return [
            'name' => '价格管理',
            'sub_navs' => [
                [
                    'name' => '快递价格',
                    'url' => url(Route::ADMIN_PRICE_EXPRESS)
                ],
                [
                    'name' => '流量价格',
                    'url' => url(Route::ADMIN_PRICE_FLOW)
                ],
                [
                    'name' => '收藏价格',
                    'url' => url(Route::ADMIN_PRICE_COLLECTION)
                ],
                [
                    'name' => 'VIP价格',
                    'url' => url(Route::ADMIN_PRICE_VIP)
                ],
                [
                    'name' => '充值奖励',
                    'url' => url(Route::ADMIN_PRICE_RECHARGE)
                ],
                [
                    'name' => '推荐奖励',
                    'url' => url(Route::ADMIN_PRICE_RECOMMEND)
                ],
            ]
        ];
    }

    private function getNavOfOrder()
    {
        return [
            'name' => '订单管理',
            'sub_navs' => [
                [
                    'name' => '快递订单',
                    'url' => url(Route::ADMIN_ORDER_EXPRESS)
                ],
                [
                    'name' => '流量订单',
                    'url' => url(Route::ADMIN_ORDER_FLOW)
                ],
                [
                    'name' => '收藏订单',
                    'url' => url(Route::ADMIN_ORDER_COLLECTION)
                ],
            ]
        ];
    }

    private function getNavOfDistributor()
    {
        return [
            'name' => '分站管理',
            'sub_navs' => [
                [
                    'name' => '分站列表',
                    'url' => url(Route::ADMIN_DISTRIBUTOR)
                ],
                [
                    'name' => '新增分站',
                    'url' => url(Route::ADMIN_DISTRIBUTOR_EDIT)
                ]
            ]
        ];
    }

    private function getNavOfAdmin()
    {
        return [
            'name' => '管理权限',
            'sub_navs' => [
                [
                    'name' => '管理员列表',
                    'url' => url(Route::ADMIN_ADMIN)
                ],
                [
                    'name' => '新增管理员',
                    'url' => url(Route::ADMIN_ADMIN_EDIT)
                ]
            ]
        ];
    }
}