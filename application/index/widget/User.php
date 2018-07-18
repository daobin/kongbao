<?php

namespace app\index\widget;

use app\common\controller\Widget;
use app\common\service\model\user\UserRoute;

class User extends Widget
{
    public function sidebar()
    {
        $routes = UserRoute::all();
        foreach ($routes as $idx => $route) {
            if ((int)$route->status == 1) {
                unset($routes[$idx]);
                $routes[$route->route_group][$route->route] = 1;
            }else{
                unset($routes[$idx]);
            }
        }

        $this->assign('routes', $routes);
        $this->assign('center_navs', UserRoute::getCenterNavs());
        return $this->fetch('widget/user/sidebar');
    }
}