<?php

namespace app\admin\widget;

use app\common\constant\Route;
use app\common\controller\Widget;

class Meta extends Widget
{
    public function index(){
        $this->assignCss();
        return $this->fetch('widget/meta/index');
    }

    private function assignCss(){
        $cssHrefs = ['admin/css/global.css'];
        switch ($this->route){
            case Route::ADMIN_HOME:
                break;
        }

        $this->assign('css_hrefs', $cssHrefs);
    }

}