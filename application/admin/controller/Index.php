<?php

namespace app\admin\controller;

use app\common\constant\Route;
use app\common\controller\Controller;

class Index extends Controller
{
    public function index()
    {
        return $this->fetch();
    }

    public function login()
    {
        $this->view->engine->layout(false);

        $this->assign('comefrom', input('get.comefrom/s', url(Route::ADMIN_HOME)));

        return $this->fetch();
    }

    public function logout(){
        session('admin', null);
        $this->redirect(url(Route::ADMIN_LOGIN));
    }
}
