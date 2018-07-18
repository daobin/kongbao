<?php

namespace app\ajax\controller\index;

use app\common\controller\Ajax;
use app\common\helper\Ip;
use app\common\service\model\user\User as UserModel;

class User extends Ajax
{
    public function login()
    {
        $account = input('post.name/s', '');
        $password = input('post.pwd/s', '');

        $userInfo = UserModel::getByUserAccount($account);
        if (empty($userInfo)) {
            $this->echoDataFormatToJson([
                'status' => 'fail',
                'msg' => 'account'
            ]);
        }
        if (!password_verify($password, $userInfo->user_password)) {
            $this->echoDataFormatToJson([
                'status' => 'fail',
                'msg' => 'password'
            ]);
        }
        session('user_info', $userInfo);

        //登录成功后更新用户登录信息
        UserModel::update([
            'login_ip' => Ip::getClientIp(),
            'gmt_login' => NOW_DATE_TIME
        ], ['user_id' => $userInfo->user_id]);

        $this->echoDataFormatToJson(['status' => 'success']);
    }

    public function register()
    {
        $account = input('post.name/s', '');
        if (!empty(UserModel::getByUserAccount($account))) {
            $this->echoDataFormatToJson(['status' => 'fail', 'msg' => 'name']);
        }

        $captcha = input('post.code/s', '');
        if (!captcha_check($captcha, '', \config('captcha'))) {
            $this->echoDataFormatToJson(['status' => 'fail', 'msg' => 'code']);
        }

        $password = input('post.pwd/s', '');
        $qq = input('post.qq/s', '');
        $email = input('post.email/s', '');
        $recommendUserId = 0;
        if ((int)cookie('recommend_user_id') > 0) {
            $recommendUserId = (int)cookie('recommend_user_id');
        }

        $register = UserModel::create([
            'user_account' => $account,
            'user_password' => password_hash($password, PASSWORD_DEFAULT),
            'email_address' => $email,
            'qq_number' => $qq,
            'recommend_user_id' => $recommendUserId,
            'register_ip' => Ip::getClientIp()
        ]);
        if ($register) {
            $this->echoDataFormatToJson(['status' => 'success', 'msg' => '']);
        } else {
            $this->echoDataFormatToJson(['status' => 'fail', 'msg' => '注册失败']);
        }
    }

    public function password()
    {
        $userInfo = $this->chkAndGetUserInfo();

        $oldPassword = input('post.oldpass/s', '');
        $newPassword = input('post.newspass/s', '');
        if($oldPassword == $newPassword){
            $this->echoDataFormatToJson(['status' => 'fail', 'msg' => '对不起，您输入的新旧密码一致']);
        }

        if(!password_verify($oldPassword, $userInfo->user_password)){
            $this->echoDataFormatToJson([
                'status' => 'fail',
                'msg' => '对不起，您输入的旧新密码不正确，请检查是否输入有误（注意字母大小写）'
            ]);
        }

        $update = UserModel::update([
            'user_password' => password_hash($newPassword, PASSWORD_DEFAULT)
        ], ['user_id'=>$userInfo->user_id]);
        if($update){
            $this->echoDataFormatToJson(['status' => 'success', 'msg' => '']);
        }
        $this->echoDataFormatToJson(['status' => 'fail', 'msg' => '对不起，您输入的新密码修改失败']);
    }
}