<?php
/**
 * 所有Ajax控制器基类
 */

namespace app\common\controller;

class Ajax extends Controller
{
    public function _initialize()
    {
        parent::_initialize();
        $this->view->engine->layout(false);

        if(!$this->request->isAjax()){
            $this->echoDataFormatToJson();
        }
    }

    public function chkAndGetUserInfo(){
        $userInfo = session('user_info');
        if(empty($userInfo)){
            $this->echoDataFormatToJson([
                'status' => 'fail',
                'code' => 'un_login',
                'msg' => '当前为登出状态，或登录时间过长，请刷新页面重新登录'
            ]);
        }

        return $userInfo;
    }
}