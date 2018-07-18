<?php

namespace app\index\widget;

use app\common\constant\Route;
use app\common\controller\Widget;

class Meta extends Widget
{
    public function index(){
        $this->assignCss();
        $this->assignTKD();
        return $this->fetch('widget/meta/index');
    }

    private function assignCss(){
        $cssHrefs = [
            'jquery/jquery-ui-1.10.4.css',
            'index/css/validationEngine.jquery.css',
            'index/css/layer.css',
            'index/css/style.css',
            'index/css/kefu.css',
        ];
        switch ($this->route){
            case Route::HOME:
                $cssHrefs[] = 'index/css/index.css';
                break;
            case Route::VOCATION_WORK_BUY_EXPRESS:
                $cssHrefs[] = 'index/css/buy_express.css';
                break;
            case Route::VOCATION_WORK_MY_EXPRESS:
                $cssHrefs[] = 'index/css/my_express.css';
                break;
            case Route::VOCATION_WORK_BUY_FLOW:
                $cssHrefs[] = 'index/css/buy_flow.css';
                break;
            case Route::VOCATION_WORK_MY_FLOW:
                $cssHrefs[] = 'index/css/my_flow.css';
                break;
            case Route::VOCATION_WORK_BUY_COLLECTION:
                $cssHrefs[] = 'index/css/buy_collection.css';
                break;
            case Route::VOCATION_WORK_MY_COLLECTION:
                $cssHrefs[] = 'index/css/my_collection.css';
                break;
            case Route::VOCATION_WORK_UPLOAD_ORDER_NUMBER:
                $cssHrefs[] = 'index/css/webuploader.css';
                break;
        }

        $this->assign('css_hrefs', $cssHrefs);
    }

    private function assignTKD(){
        $websiteName = defined('WEBSITE_NAME') ? WEBSITE_NAME : '空包网';
        $tkd = [
            'title' => defined('WEBSITE_MATA_TITLE') ? WEBSITE_MATA_TITLE : $websiteName,
            'keywords' => defined('WEBSITE_META_KEYWORDS') ? WEBSITE_META_KEYWORDS : $websiteName,
            'description' => defined('WEBSITE_META_DESCRIPTION') ? WEBSITE_META_DESCRIPTION : $websiteName
        ];

        $this->assign('tkd', $tkd);
    }

}