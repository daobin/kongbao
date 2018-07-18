<?php

namespace app\admin\controller;

use app\common\constant\Route;
use app\common\constant\Session;
use app\common\controller\Controller;
use app\common\service\model\user\User as UserModel;
use app\common\service\model\user\UserLevel;
use app\common\service\model\user\UserRoute;
use think\Db;

class User extends Controller
{
    public function index()
    {
        $this->assign('user_list', UserModel::order('user_id', 'desc')->paginate());
        return $this->fetch();
    }

    public function edit()
    {
        if (request()->isPost()) {
            $this->save();
        }

        $userId = input('user_id/d', 0);
        if ($userId <= 0) {
            session(Session::ERROR_MSG, '无效会员');
            $this->redirect(url(Route::ADMIN_USER));
        }

        $user = UserModel::get($userId);
        if (empty($user)) {
            session(Session::ERROR_MSG, '无效会员');
            $this->redirect(url(Route::ADMIN_USER));
        }

        $this->assign('user', $user);
        $this->assign('user_levels', UserLevel::all());
        return $this->fetch();
    }

    private function save()
    {
        $userId = input('post.user_id/d', 0);
        if ($userId <= 0 || !(UserModel::get($userId))) {
            session(Session::ERROR_MSG, '无效会员');
            $this->redirect(url(Route::ADMIN_USER));
        }

        $userLevelId = input('post.user_level_id/d', 0);
        if ($userLevelId <= 0 || !(UserLevel::get($userLevelId))) {
            session(Session::ERROR_MSG, '会员等级错误');
            $this->redirect(url(Route::ADMIN_USER));
        }

        $emailAddress = input('post.email_address/s', '');
        if (!(filter_var($emailAddress, FILTER_VALIDATE_EMAIL))) {
            session(Session::ERROR_MSG, '邮箱格式错误');
            $this->redirect(url(Route::ADMIN_USER));
        }

        $saveData = [
            'user_level_id' => $userLevelId,
            'qq_number' => input('post.qq_number/s', ''),
            'telephone' => input('post.telephone/s', ''),
            'email_address' => $emailAddress,
            'operator' => session('admin.account')
        ];

        $userPassword = input('post.user_password/s', '');
        if ($userPassword != '') {
            $saveData['user_password'] = password_hash($userPassword, PASSWORD_DEFAULT);
        }

        UserModel::update($saveData, ['user_id' => $userId]);
        session(Session::SUCCESS_MSG, '会员保存成功');
        $this->redirect(url(Route::ADMIN_USER_EDIT, ['user_id' => $userId]));
    }

    public function centerNav()
    {
        if (request()->isPost()) {
            $this->centerNavSave();
        }

        $routes = UserRoute::all();
        foreach ($routes as $idx => $route) {
            if ((int)$route->status == 1) {
                unset($routes[$idx]);
                $routes[$route->route] = 1;
            }else{
                unset($routes[$idx]);
            }
        }

        $this->assign('routes', $routes);
        $this->assign('center_navs', UserRoute::getCenterNavs());
        return $this->fetch();
    }

    private function centerNavSave()
    {
        $routes = input('post.routes/a', []);

        if (empty($routes)) {
            if (UserRoute::where(['status' => 1])->count() > 0) {
                UserRoute::update([
                    'status' => 0,
                    'operator' => session('admin.account')
                ], ['status' => 1]);
            }
            $this->redirect(url(Route::ADMIN_USER_CENTER_NAV));
        }

        try {
            Db::startTrans();
            if (UserRoute::where(['status' => 1])->count() > 0) {
                UserRoute::update([
                    'status' => 0,
                    'operator' => session('admin.account')
                ], ['status' => 1]);
            }
            foreach ($routes as $group => $rts) {
                if (empty($rts)) {
                    continue;
                }

                foreach ($rts as $rt => $rtName) {
                    if (is_numeric($rt) || empty($rtName)) {
                        continue;
                    }

                    if (UserRoute::where(['route' => $rt, 'route_group' => $group])->find()) {
                        UserRoute::update(['status' => 1], [
                            'route' => $rt,
                            'route_group' => $group,
                            'operator' => session('admin.account')
                        ]);
                    } else {
                        UserRoute::create([
                            'route' => $rt,
                            'route_text' => $rtName,
                            'route_group' => $group,
                            'status' => 1,
                            'operator' => session('admin.account')
                        ]);
                    }
                }
            }

            session(Session::SUCCESS_MSG, '保存成功');

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
        }
        $this->redirect(url(Route::ADMIN_USER_CENTER_NAV));
    }
}