<?php

namespace app\admin\controller;

use app\common\constant\Route;
use app\common\constant\Session;
use app\common\controller\Controller;
use app\common\service\model\configuration\Configuration;

class Website extends Controller
{
    public function index()
    {
        if (request()->isPost()) {
            $this->save();
        }
        $this->assign('cfg_list', Configuration::scope('list', ['group' => 'BASE'])->select());
        return $this->fetch();
    }

    private function save()
    {
        $cfgData = input('post.cfg_data/a', []);
        if (!empty($cfgData)) {
            foreach ($cfgData as $cfgKey => $cfgVal) {
                Configuration::update([
                    'value' => $cfgVal,
                    'operator' => session('admin.account')
                ], ['key' => $cfgKey]);
            }
        }

        $logoFile = request()->file('website_logo');
        if ($logoFile) {
            $logoInfo = $logoFile->validate([
                'size' => (5 * 1024), 'ext' => 'jpg,png'
            ])->move(ROOT_PATH . 'public/up/website_logo', false);
            if ($logoInfo) {
                Configuration::update([
                    'value' => '/public/up/website_logo/' . $logoInfo->getFilename(),
                    'operator' => session('admin.account')
                ], ['key' => 'WEBSITE_LOGO_IMG']);
            } else {
                session(Session::ERROR_MSG, '网站LOGO上传失败：' . trim($logoFile->getError()));
                $this->redirect(url(Route::ADMIN_WEBSITE));
            }
        }

        session(Session::SUCCESS_MSG, '保存成功');
        $this->redirect(url(Route::ADMIN_WEBSITE));
    }
}
