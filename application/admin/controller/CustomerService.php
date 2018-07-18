<?php

namespace app\admin\controller;

use app\common\constant\Route;
use app\common\constant\Session;
use app\common\controller\Controller;
use app\common\service\model\configuration\Qq;

class CustomerService extends Controller
{
    public function qq()
    {
        if (request()->isPost()) {
            $this->qqSave();
        }

        $this->assign('qq_list', Qq::scope('order')->select());
        return $this->fetch();
    }

    private function qqSave()
    {
        $names = input('post.names/a', []);
        $qqNumbers = input('post.qq_numbers/a', []);
        $sorts = input('post.sorts/a', []);
        $status = input('post.status/a', []);

        if (empty($names)) {
            session(Session::ERROR_MSG, '请新增QQ客服');
            $this->redirect(url(Route::ADMIN_CUSTOMER_SERVICE_OF_QQ));
        }

        $inDatas = [];
        $upDatas = [];
        foreach ($names as $id => $name) {
            $name = trim($name);
            $qqNumber = trim($qqNumbers[$id]);
            if ($id == '__I__' || $name == '' || $qqNumber == '') {
                continue;
            }

            if (strpos($id, 'i_') !== false) {
                $inDatas[] = [
                    'name' => $name,
                    'qq_number' => $qqNumber,
                    'sort' => (int)$sorts[$id],
                    'status' => (int)$status[$id],
                    'operator' => session('admin.account')
                ];
            } else {
                $upDatas[] = [
                    'qq_id' => $id,
                    'name' => $name,
                    'qq_number' => $qqNumber,
                    'sort' => (int)$sorts[$id],
                    'status' => (int)$status[$id],
                    'operator' => session('admin.account')
                ];
            }
        }

        if (empty($inDatas) && empty($upDatas)) {
            session(Session::ERROR_MSG, '请新增QQ客服');
            $this->redirect(url(Route::ADMIN_CUSTOMER_SERVICE_OF_QQ));
        }

        $qqModel = new Qq();
        if (!empty($inDatas)) {
            $qqModel->saveAll($inDatas);
        }
        if (!empty($upDatas)) {
            $qqModel->saveAll($upDatas);
        }

        session(Session::SUCCESS_MSG, 'QQ客服保存成功');
        $this->redirect(url(Route::ADMIN_CUSTOMER_SERVICE_OF_QQ));
    }
}