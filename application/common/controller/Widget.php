<?php
/**
 * 所有Widget控制器基类
 */

namespace app\common\controller;

class Widget extends Controller
{
    public function _initialize()
    {
        parent::_initialize();
        $this->view->engine->layout(false);
    }
}