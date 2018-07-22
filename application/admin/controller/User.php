<?php

namespace app\admin\controller;

use app\common\constant\Route;
use app\common\constant\Session;
use app\common\controller\Controller;
use app\common\service\model\user\User as UserModel;
use app\common\service\model\user\UserFundRecord;
use app\common\service\model\user\UserLevel;
use app\common\service\model\user\UserPrice;
use app\common\service\model\user\UserRoute;
use app\common\service\model\product\Price;
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

    public function price()
    {
        if (request()->isPost()) {
            $this->priceSave();
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

        $this->assign('express_prices', Price::scope('list', ['group' => 'express'])->select());
        $this->assign('user_prices', UserPrice::where(['user_id' => $userId, 'group' => 'express'])
            ->column(['price_id', 'fixed_price']));

        return $this->fetch();
    }

    private function priceSave()
    {
        $userId = input('post.user_id/d', 0);
        if ($userId <= 0) {
            session(Session::ERROR_MSG, '无效会员');
            $this->redirect(url(Route::ADMIN_USER));
        }

        $user = UserModel::get($userId);
        if (empty($user)) {
            session(Session::ERROR_MSG, '无效会员');
            $this->redirect(url(Route::ADMIN_USER));
        }

        $save = true;

        //固定利润保存
        if (input('?post.express_fixed_profit')) {
            $save = UserModel::update([
                'express_fixed_profit' => input('post.express_fixed_profit/f'),
                'operator' => session('admin.account')
            ], ['user_id' => $userId]);

            if ($save) {
                session(Session::SUCCESS_MSG, '设置成功');
            } else {
                session(Session::ERROR_MSG, '设置失败');
            }

            $this->redirect(url(Route::ADMIN_USER_PRICE, ['user_id' => $userId]));
        }

        //固定价格保存
        $fixedPrices = input('post.fixed_prices/a', []);
        if (empty($fixedPrices)) {
            $this->redirect(url(Route::ADMIN_USER_PRICE, ['user_id' => $userId]));
        }

        try {
            Db::startTrans();

            foreach ($fixedPrices as $priceId => $fixedPrice) {
                if (!is_numeric($fixedPrice) && $fixedPrice > 0) {
                    continue;
                }

                $where = [
                    'user_id' => $userId,
                    'price_id' => $priceId,
                    'group' => 'express'
                ];
                if (!empty(UserPrice::where($where)->find())) {
                    UserPrice::update([
                        'fixed_price' => $fixedPrice,
                        'operator' => session('admin.account')
                    ], $where);
                } else {
                    UserPrice::create([
                        'user_id' => $userId,
                        'price_id' => $priceId,
                        'group' => 'express',
                        'fixed_price' => $fixedPrice,
                        'operator' => session('admin.account')
                    ]);
                }
            }

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $save = false;
        }

        if ($save) {
            session(Session::SUCCESS_MSG, '保存成功');
        } else {
            session(Session::ERROR_MSG, '保存失败');
        }

        $this->redirect(url(Route::ADMIN_USER_PRICE, ['user_id' => $userId]));
    }

    public function fund()
    {
        if (request()->isPost()) {
            $this->fundSave();
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

        return $this->fetch();
    }

    private function fundSave()
    {
        $userId = input('post.user_id/d', 0);
        if ($userId <= 0) {
            session(Session::ERROR_MSG, '无效会员');
            $this->redirect(url(Route::ADMIN_USER));
        }

        $user = UserModel::get($userId);
        if (empty($user)) {
            session(Session::ERROR_MSG, '无效会员');
            $this->redirect(url(Route::ADMIN_USER));
        }

        $errMsg = '';
        $type = input('post.type/d', 0);
        $price = input('post.price/f', 0);
        $detail = input('post.detail/s', '');

        if ($type != 1 && $type != 2) {
            $errMsg .= ($errMsg != '' ? '<br/>' : '') . '请选择操作类型';
        }
        if ($price <= 0) {
            $errMsg .= ($errMsg != '' ? '<br/>' : '') . '请填写有效金额数';
        }

        if ($errMsg != '') {
            session(Session::ERROR_MSG, $errMsg);
            $this->redirect(url(Route::ADMIN_USER_FUND, ['user_id' => $userId]));
        }

        $save = true;
        $detail = $detail != '' ? $detail : ($type == 1 ? '官方奖励' : '官方惩罚');

        try {
            Db::startTrans();

            $balance = $user->balance;
            if ($type == 1) {
                $balance += $price;
            } else if ($type == 2) {
                $balance -= $price;
            }

            UserFundRecord::create([
                'user_id' => $userId,
                'group' => 1,
                'type' => $type,
                'balance' => $balance,
                'price' => $price,
                'detail' => $detail,
                'operator' => session('admin.account')
            ]);

            UserModel::update([
                'balance' => $balance,
                'operator' => session('admin.account')
            ], ['user_id' => $userId]);

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $save = false;
        }

        if ($save) {
            session(Session::SUCCESS_MSG, '保存成功');
        } else {
            session(Session::ERROR_MSG, '保存失败');
        }
        $this->redirect(url(Route::ADMIN_USER_FUND, ['user_id' => $userId]));
    }

    public function fundRecord(){
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

        $pageQuery = input('get.');
        if(isset($pageQuery['page'])){
            unset($pageQuery['page']);
        }
        $this->assign('fund_records', UserFundRecord::where('user_id', '=', $user->user_id)
            ->order('user_fund_record_id', 'desc')->paginate(null, false, [
                'query' => $pageQuery
            ]));

        return $this->fetch();
    }

    public function fundRecords(){
        $where = ['group' => 1];
        $userAccount = input('get.user_account/s', '');
        if($userAccount != ''){
            $user = UserModel::getByUserAccount($userAccount);
            if(!empty($user)){
                $where['user_id'] = $user->user_id;
            }else{
                $where['user_id'] = 0;
            }
        }

        $pageQuery = input('get.');
        if(isset($pageQuery['page'])){
            unset($pageQuery['page']);
        }
        $this->assign('fund_records', UserFundRecord::where($where)
            ->order('user_fund_record_id', 'desc')->paginate(null, false, [
                'query' => $pageQuery
            ]));

        return $this->fetch();
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
            } else {
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