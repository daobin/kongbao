<?php
/**
 * 后台模块初始工作
 */

namespace app\admin\behavior;

use app\common\constant\Route;
use app\common\controller\Controller;

class ModuleInit extends Controller
{
    public function run(){
        $unNeedLoginRoutes = [Route::ADMIN_LOGIN, Route::ADMIN_LOGOUT];
        if(!in_array($this->route, $unNeedLoginRoutes) && (int)session('admin.admin_id') <= 0){
            $this->redirect(url(Route::ADMIN_LOGIN));
        }

        if($this->route == Route::ADMIN_LOGIN && (int)session('admin.admin_id') > 0){
            $this->redirect(url(Route::ADMIN_HOME));
        }
    }
}