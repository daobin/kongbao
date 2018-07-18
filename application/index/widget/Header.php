<?php

namespace app\index\widget;

use app\common\controller\Widget;
use app\common\service\model\advertisement\Banner;
use app\common\service\model\advertisement\Text;
use app\common\service\model\configuration\RecommendNavigation;

class Header extends Widget
{
    public function index()
    {
        $this->assign('top_text', Text::where([
            'position'=>'common_top',
            'status'=>1
        ])->field(true)->find());

        $this->assign('top_banner', Banner::where([
            'banner_position'=>'common_top',
            'banner_status'=>1
        ])->field(true)->find());

        $this->assign('navs', RecommendNavigation::scope('list', [
            'status' => 1,
            'position' => 'common_top'
        ])->select());

        $this->assign('user_info', session('user_info'));

        return $this->fetch('widget/header/index');
    }
}