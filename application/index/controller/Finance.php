<?php

namespace app\index\controller;

use app\common\controller\Controller;

class Finance extends Controller
{
    private $userInfo;

    public function _initialize()
    {
        parent::_initialize();

        $this->userInfo = session('user_info');
        if (empty($this->userInfo)) {
            $this->redirect(Route::USER_LOGIN);
        }

        $this->assign('user_info', $this->userInfo);
    }

    public function accountRecharge(){
        return $this->fetch();
    }

    public function rechargeRecord(){
        return $this->fetch();
    }

    public function fundRecord(){
        return $this->fetch();
    }
}