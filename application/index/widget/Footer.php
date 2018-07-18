<?php

namespace app\index\widget;

use app\common\controller\Widget;
use app\common\service\model\configuration\Qq;
use app\common\service\model\configuration\RecommendNavigation;

class Footer extends Widget
{
    public function index(){
        $this->assign('qq_list', Qq::scope('list')->where('status', 1)->select());
        $this->assign('friend_lins', RecommendNavigation::scope('list', [
            'status' => 1,
            'position' => 'friend_link'
        ])->select());
        return $this->fetch('widget/footer/index');
    }
}