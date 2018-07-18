<?php

namespace app\admin\controller;

use app\common\constant\Route;
use app\common\constant\Session;
use app\common\controller\Controller;
use app\common\service\model\advertisement\Banner;
use app\common\service\model\advertisement\BannerDetail;
use app\common\service\model\advertisement\Text;
use think\Db;

class Advertisement extends Controller
{
    public function index()
    {
        $bannerActive = '';
        $textActive = '';
        switch (input('get.active_data', '')) {
            case 'text':
                $textActive = 'active';
                break;
            default:
                $bannerActive = 'active';
                break;
        }

        $this->assign('banner_active', $bannerActive);
        $this->assign('text_active', $textActive);

        $this->assign('banners', Banner::all());
        $this->assign('texts', Text::all());
        return $this->fetch();
    }

    public function bannerEdit()
    {
        if (request()->isPost()) {
            $this->bannerSave();
        }
        $this->assign('banner', Banner::get(input('get.banner_id/d', 0)));
        return $this->fetch();
    }

    private function bannerSave()
    {
        $bannerId = input('post.banner_id/d', 0);
        if (!Banner::get($bannerId)) {
            session(Session::ERROR_MSG, 'Banner无效，请重新操作');
            $this->redirect(url(Route::ADMIN_ADVERTISEMENT));
        }

        $imagePath = ROOT_PATH . 'public/up/banner/';
        if (!is_dir($imagePath)) {
            mkdir($imagePath, 0755, true);
        }

        $detailImages = [];
        $files = $_FILES['files'];
        if (isset($files['name'])) {
            $imgIndex = 0;
            foreach ($files['name'] as $id => $name) {
                $name = trim($name);
                if ($id == '__I__') {
                    continue;
                }

                $needImage = false;
                if (strpos($id, 'i_') !== false) {
                    $needImage = true;
                }else if(!empty($name)){
                    $needImage = true;
                }
                if($needImage == false){
                    continue;
                }

                $imgIndex++;

                if ((int)$files['error'][$id] > 0) {
                    session(Session::ERROR_MSG, '第' . $imgIndex . '张新图片上传有误');
                    $this->redirect(url(Route::ADMIN_ADVERTISEMENT_BANNER_EDIT, 'banner_id=' . $bannerId));
                }

                if ((int)$files['size'][$id] > (1024 * 1024 * 3)) {
                    session(Session::ERROR_MSG, '第' . $imgIndex . '张新图片超过3MB');
                    $this->redirect(url(Route::ADMIN_ADVERTISEMENT_BANNER_EDIT, 'banner_id=' . $bannerId));
                }

                if (!in_array(trim($files['type'][$id]), ['image/png', 'image/jpeg'])) {
                    session(Session::ERROR_MSG, '第' . $imgIndex . '张新图片格式不是 png / jpg / jpeg');
                    $this->redirect(url(Route::ADMIN_ADVERTISEMENT_BANNER_EDIT, 'banner_id=' . $bannerId));
                }

                if (!move_uploaded_file(trim($files['tmp_name'][$id]), $imagePath . $name)) {
                    session(Session::ERROR_MSG, '第' . $imgIndex . '张新图片上传保存失败');
                    $this->redirect(url(Route::ADMIN_ADVERTISEMENT_BANNER_EDIT, 'banner_id=' . $bannerId));
                }

                $detailImages[$id] = str_replace(ROOT_PATH, '/', $imagePath . $name);
            }
        }

        $links = input('post.links/a', []);
        $sorts = input('post.sorts/a', []);
        $status = input('post.status/a', []);
        $newWindowOpens = input('post.new_window_opens/a', []);

        $linkIndex = 0;
        $inDatas = [];
        $upDatas = [];
        foreach ($links as $id => $link) {
            if ($id == '__I__') {
                continue;
            }

            $link = trim($link);
            $linkIndex++;

            if ($link != '' && !filter_var($link, FILTER_VALIDATE_URL)) {
                session(Session::ERROR_MSG, '第' . $linkIndex . '个链接地址无效');
                $this->redirect(url(Route::ADMIN_ADVERTISEMENT_BANNER_EDIT, 'banner_id=' . $bannerId));
            }

            if (strpos($id, 'i_') !== false) {
                if (!isset($detailImages[$id])) {
                    session(Session::ERROR_MSG, '第' . $linkIndex . '个Banner图片未上传');
                    $this->redirect(url(Route::ADMIN_ADVERTISEMENT_BANNER_EDIT, 'banner_id=' . $bannerId));
                }
                $inDatas[] = [
                    'banner_id' => $bannerId,
                    'detail_image' => $detailImages[$id],
                    'detail_link' => $link,
                    'detail_sort' => (int)$sorts[$id],
                    'detail_status' => (int)$status[$id],
                    'new_window_open' => (int)$newWindowOpens[$id],
                    'operator' => session('admin.account')
                ];
            } else {
                if (isset($detailImages[$id])) {
                    $upDatas[] = [
                        'banner_detail_id' => $id,
                        'detail_image' => $detailImages[$id],
                        'detail_link' => $link,
                        'detail_sort' => (int)$sorts[$id],
                        'detail_status' => (int)$status[$id],
                        'new_window_open' => (int)$newWindowOpens[$id],
                        'operator' => session('admin.account')
                    ];
                } else {
                    $upDatas[] = [
                        'banner_detail_id' => $id,
                        'detail_link' => $link,
                        'detail_sort' => (int)$sorts[$id],
                        'detail_status' => (int)$status[$id],
                        'new_window_open' => (int)$newWindowOpens[$id],
                        'operator' => session('admin.account')
                    ];
                }
            }
        }

        Db::startTrans();
        try {
            Banner::update([
                'banner_status' => input('post.banner_status/d', 0),
                'operator' => session('admin.account')
            ], ['banner_id' => $bannerId]);

            $bannerDetailModel = new BannerDetail();
            if (!empty($inDatas)) {
                $bannerDetailModel->saveAll($inDatas);
            }

            if (!empty($upDatas)) {
                $bannerDetailModel->saveAll($upDatas);
            }

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            session(Session::ERROR_MSG, 'Banner图片保佑失败');
            $this->redirect(url(Route::ADMIN_ADVERTISEMENT_BANNER_EDIT, 'banner_id=' . $bannerId));
        }

        session(Session::SUCCESS_MSG, 'Banner图片保佑成功');
        $this->redirect(url(Route::ADMIN_ADVERTISEMENT_BANNER_EDIT, 'banner_id=' . $bannerId));
    }

    public function textEdit()
    {
        if (request()->isPost()) {
            $this->textSave();
        }
        $this->assign('text', Text::get(input('get.text_id/d', 0)));
        return $this->fetch();
    }

    private function textSave()
    {
        $textId = input('post.text_id/d', 0);
        if (!Text::get($textId)) {
            session(Session::ERROR_MSG, '文案内容无效，请重新操作');
            $this->redirect(url(Route::ADMIN_ADVERTISEMENT, 'active_data=text'));
        }

        $save = Text::update([
            'description' => input('post.description/s', '', 'trim'),
            'status' => input('post.status/d', 0),
            'operator' => session('admin.account')
        ], ['text_id' => $textId]);
        if ($save) {
            session(Session::SUCCESS_MSG, '文案内容更新成功');
        } else {
            session(Session::ERROR_MSG, '文案内容更新失败');
        }

        $this->redirect(url(Route::ADMIN_ADVERTISEMENT_TEXT_EDIT, 'text_id=' . $textId));
    }
}