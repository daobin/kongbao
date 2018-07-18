<?php

namespace app\admin\controller;

use app\common\constant\Route;
use app\common\constant\Session;
use app\common\controller\Controller;
use app\common\service\model\configuration\Distributor as DistributorModel;

class Distributor extends Controller
{
    public function index()
    {
        $this->assign('distributor_list', DistributorModel::where([])->paginate());
        return $this->fetch();
    }

    public function edit()
    {
        if (request()->isPost()) {
            $this->save();
        }

        $distributorId = input('distributor_id/d', 0);
        $this->assign('distributor', DistributorModel::get($distributorId));

        return $this->fetch();
    }

    private function save()
    {
        $distributorId = input('post.distributor_id/d', 0);
        $siteDomain = input('post.site_domain/s', '');
        $adminAccount = input('post.admin_account/s', '');
        $adminPassword = input('post.admin_password/s', '');
        if ($siteDomain == '' || $adminAccount == '' || $distributorId <= 0 && $adminPassword == '') {
            session(Session::ERROR_MSG, '分站域名和管理账号、密码不能为空');
            $this->redirect(url(Route::ADMIN_DISTRIBUTOR_EDIT, ['distributor_id' => $distributorId]));
        }

        if (substr_count($siteDomain, '.') < 2) {
            session(Session::ERROR_MSG, '分站域名格式错误');
            $this->redirect(url(Route::ADMIN_DISTRIBUTOR_EDIT, ['distributor_id' => $distributorId]));
        }

        $saveData = [
            'site_domain' => $siteDomain,
            'admin_account' => $adminAccount,
            'admin_password' => $adminPassword,
            'operator' => session('admin.account')
        ];

        $distributorModel = new DistributorModel();
        if ($distributorId > 0) {
            unset($saveData['site_domain'], $saveData['admin_account']);
            $distributorInfo = $distributorModel->where(['admin_account' => $adminAccount])
                ->field('distributor_id')->order('distributor_id', 'desc')->find();
            if (isset($distributorInfo->distributor_id) && $distributorInfo->distributor_id != $distributorId) {
                session(Session::ERROR_MSG, '分站后台账号不能重复');
                $this->redirect(url(Route::ADMIN_DISTRIBUTOR_EDIT, ['distributor_id' => $distributorId]));
            }
            if ($saveData['admin_password'] != '') {
                $saveData['admin_password'] = password_hash($saveData['admin_password'], PASSWORD_DEFAULT);
            } else {
                unset($saveData['admin_password']);
            }

            $distributorModel->save($saveData, ['distributor_id' => $distributorId]);
        } else {
            $saveData['admin_password'] = password_hash($saveData['admin_password'], PASSWORD_DEFAULT);
            $distributorModel->save($saveData);
            $distributorId = $distributorModel->distributor_id;
        }

        session(Session::SUCCESS_MSG, '分站保存成功');
        $this->redirect(url(Route::ADMIN_DISTRIBUTOR_EDIT, ['distributor_id' => $distributorId]));
    }
}