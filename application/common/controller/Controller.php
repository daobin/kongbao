<?php
/**
 * 所有控制器基类
 */

namespace app\common\controller;

use app\common\service\model\configuration\Configuration;

class Controller extends \think\Controller
{
    /**
     * @var 页面访问路由
     */
    public $route;

    /**
     * 初始化工作
     */
    public function _initialize()
    {
        parent::_initialize();

        //初始化页面访问路由
        $routeInfo = $this->request->routeInfo();
        if (isset($routeInfo['route'])) {
            $this->route = trim($routeInfo['route']);
        } else {
            $this->route = trim($this->request->module());
            $this->route .= '/' . trim($this->request->controller());
            $this->route .= '/' . trim($this->request->action());
        }
        $this->route = strtolower($this->route);

        $this->initDefine();
    }

    private function initDefine(){
        defined('NOW_DATE_TIME') || define('NOW_DATE_TIME', date('Y-m-d H:i:s'));

        $baseCfgs = Configuration::where('group', 'BASE')->field(true)->select();
        foreach($baseCfgs as $baseCfg){
            $cfgKey = trim($baseCfg->key);
            defined($cfgKey) || define($cfgKey, trim($baseCfg->value));
        }
    }

    public function echoDataFormatToJson($data = null){
        if(empty($data) || !is_array($data)){
            $data = ['status'=>'fail', 'msg'=>'资源请求无效或返回数据无效'];
        }

        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

