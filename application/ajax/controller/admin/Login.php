<?php

namespace app\ajax\controller\admin;

use app\common\controller\Ajax;
use app\common\service\model\admin\Admin;

class Login extends Ajax
{
    public function process()
    {

        $account = input('post.account/s', '');
        $password = input('post.password/s', '');
        $captcha = input('post.captcha/s', '');

        if($account == ''){
            $this->echoDataFormatToJson(['status'=>'fail', 'msg'=>'请输入登录账号']);
        }

        if($password == ''){
            $this->echoDataFormatToJson(['status'=>'fail', 'msg'=>'请输入登录密码']);
        }

        if($captcha == ''){
            $this->echoDataFormatToJson(['status'=>'fail', 'msg'=>'请输入验证编码']);
        }

        if(!captcha_check($captcha,'',\config('captcha'))){
            $this->echoDataFormatToJson(['status'=>'fail', 'msg'=>'验证编码错误']);
        }

        $adminInfo = Admin::get(function($query) use ($account){
            $query->where('account',$account);
        });
        if(empty($adminInfo)){
            $this->echoDataFormatToJson(['status'=>'fail', 'msg'=>'登录账号密码不匹配']);
        }

        if(!password_verify($password, $adminInfo->password)){
            $this->echoDataFormatToJson(['status'=>'fail', 'msg'=>'登录账号密码不匹配']);
        }

        session('admin.admin_id', $adminInfo->admin_id);
        session('admin.admin_group_id', $adminInfo->admin_group_id);
        session('admin.account', $adminInfo->account);
        session('admin.gmt_login', $adminInfo->gmt_login);
        session('admin.gmt_password', $adminInfo->gmt_password);

        //在设置会话之后更新登录时间
        $dateTime = date('Y-m-d H:i:s');
        Admin::update([
            'gmt_login' => $dateTime
        ], [
            'admin_id' => $adminInfo->admin_id
        ]);

        cookie('sctime', time());

        $this->echoDataFormatToJson(['status'=>'success', 'msg'=>'登录成功']);
    }
}