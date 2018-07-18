<?php

namespace app\admin\controller;

use app\common\constant\Route;
use app\common\constant\Session;
use app\common\controller\Controller;
use app\common\service\model\admin\Admin as AdminModel;

class Admin extends Controller
{
    public function index()
    {
        $this->assign('admin_list', AdminModel::where([])->paginate());
        return $this->fetch();
    }

    public function edit()
    {
        if(request()->isPost()){
            $this->save();
        }

        $adminId = input('admin_id/d', 0);
        $this->assign('admin', AdminModel::get($adminId));
        return $this->fetch();
    }

    private function save(){
        $adminId = input('post.admin_id/d', 0);
        $account = input('post.account/s', '');
        $password = input('post.password/s', '');
        if($account == '' || $adminId <= 0 && $password == ''){
            session(Session::ERROR_MSG, '管理员账号、密码不能为空');
            $this->redirect(url(Route::ADMIN_ADMIN_EDIT, ['admin_id'=>$adminId]));
        }

        $saveData = [
            'admin_group_id' => input('post.admin_group_id', 0),
            'account' => $account,
            'password' => $password,
            'operator' => session('admin.account')
        ];

        $adminModel = new AdminModel();

        $adminInfo = $adminModel->where(['account' => $account])
            ->field('admin_id')->order('admin_id', 'desc')->find();
        if(isset($adminInfo->admin_id) && $adminInfo->admin_id != $adminId){
            session(Session::ERROR_MSG, '管理员账号不能重复');
            $this->redirect(url(Route::ADMIN_ADMIN_EDIT, ['admin_id'=>$adminId]));
        }

        if($adminId > 0){
            unset($saveData['account']);
            if($saveData['password'] != ''){
                $saveData['password'] = password_hash($saveData['password'], PASSWORD_DEFAULT);
            }else{
                unset($saveData['password']);
            }

            $adminModel->save($saveData, ['admin_id'=>$adminId]);
        }else{
            $saveData['password'] = password_hash($saveData['password'], PASSWORD_DEFAULT);
            $adminModel->save($saveData);
            $adminId = $adminModel->admin_id;
        }

        session(Session::SUCCESS_MSG, '管理员保存成功');
        $this->redirect(url(Route::ADMIN_ADMIN_EDIT, ['admin_id'=>$adminId]));
    }
}