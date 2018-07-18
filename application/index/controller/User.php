<?php

namespace app\index\controller;

use app\common\constant\Route;
use app\common\controller\Controller;
use app\common\service\model\advertisement\Text;
use app\common\service\model\user\User as UserModel;

class User extends Controller
{
    public function _initialize()
    {
        parent::_initialize();
        if (in_array($this->route, [Route::USER_LOGIN, Route::USER_REGISTER])) {
            if (!empty(session('user_info'))) {
                $this->redirect(Route::USER_INDEX);
            }
        } else if (empty(session('user_info'))) {
            $this->redirect(Route::USER_LOGIN);
        }
    }

    public function register()
    {
        return $this->fetch();
    }

    public function login()
    {
        return $this->fetch();
    }

    public function logout()
    {
        session('user_info', null);
        $this->redirect(Route::USER_LOGIN);
    }

    public function index()
    {
        $this->assign('user_info', session('user_info'));
        $this->assign('page_text', Text::where([
            'position' => 'user_center',
            'status' => 1
        ])->field(true)->find());

        return $this->fetch();
    }

    public function recommend()
    {
        $this->assign('page_text', Text::where([
            'position' => 'my_subordinate',
            'status' => 1
        ])->field(true)->find());

        $userInfo = session('user_info');
        $recommendLink = url(Route::HOME, '', false, true);
        $recommendLink .= 'index/r/' . strtoupper(substr($userInfo->email_address, 0, 6));
        $recommendLink .= $userInfo->user_id . '.html';
        $this->assign('recommend_link', $recommendLink);

        $this->assign('recommend_users', UserModel::where(['recommend_user_id' => $userInfo->user_id])
            ->field(true)->order('user_id', 'desc')->paginate());
        return $this->fetch();
    }

    public function password()
    {
        return $this->fetch();
    }

    public function api(){
        $this->assign('host_link', url(Route::HOME, '', false, true));
        return $this->fetch();
    }
}