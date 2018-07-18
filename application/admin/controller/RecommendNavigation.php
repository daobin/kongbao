<?php

namespace app\admin\controller;

use app\common\constant\Route;
use app\common\constant\Session;
use app\common\controller\Controller;
use app\common\service\model\configuration\RecommendNavigation as NavModel;

class RecommendNavigation extends Controller
{
    public function index()
    {
        $this->assign('nav_list', NavModel::scope('list')->select());
        return $this->fetch();
    }

    public function edit()
    {
        if(request()->isPost()){
            $this->save();
        }

        $navId = input('nav_id/d', 0);
        $this->assign('nav', NavModel::get($navId));
        $this->assign('positions', NavModel::getPositions());

        return $this->fetch();
    }

    private function save()
    {
        $navId = input('post.nav_id/d', 0);
        $title = input('post.title/s', '');
        $link = input('post.link/s', '');
        $position = input('post.position/s', '');
        $sort = input('post.sort/d', 0);
        $status = input('post.status/d', 0);
        $newWindowOpen = input('post.new_window_open/d', 0);
        if($title == '' || $link == ''){
            session(Session::ERROR_MSG, '导航标题、链接不能为空');
            $this->redirect(url(Route::ADMIN_RECOMMEND_NAVIGATION_EDIT, ['nav_id'=>$navId]));
        }

        if(!filter_var($link, FILTER_VALIDATE_URL)){
            session(Session::ERROR_MSG, '导航链接不是一个有效链接');
            $this->redirect(url(Route::ADMIN_RECOMMEND_NAVIGATION_EDIT, ['nav_id'=>$navId]));
        }

        $saveData = [
            'title' => $title,
            'link' => $link,
            'position' => $position,
            'sort' => $sort,
            'status' => $status,
            'new_window_open' => $newWindowOpen,
            'operator' => session('admin.account')
        ];

        $navModel = new NavModel();
        if($navId > 0){
            $navModel->save($saveData, ['recommend_navigation_id'=>$navId]);
        }else{
            $navModel->save($saveData);
            $navId = $navModel->recommend_navigation_id;
        }

        session(Session::SUCCESS_MSG, '推荐导航保存成功');
        $this->redirect(url(Route::ADMIN_RECOMMEND_NAVIGATION_EDIT, ['nav_id'=>$navId]));
    }
}
