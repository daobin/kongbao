<?php

namespace app\index\controller;

use app\common\controller\Controller;
use app\common\service\model\address\Province;
use app\common\service\model\advertisement\Text;
use app\common\service\model\product\Price;

class VocationWork extends Controller
{
    private $userInfo;

    public function _initialize()
    {
        parent::_initialize();

        $this->userInfo = session('user_info');
        if (empty($this->userInfo)) {
            $this->redirect(Route::USER_LOGIN);
        }

        $this->assign('user_info', $this->userInfo);
    }

    public function shippingAddress()
    {
        $this->assign('page_text', Text::where([
            'position' => 'shipping_address',
            'status' => 1
        ])->field(true)->find());

        $this->assign('provinces', Province::all());

        return $this->fetch();
    }

    public function buyExpress()
    {
        $this->assign('page_text', Text::where([
            'position' => 'buy_kongbao',
            'status' => 1
        ])->field(true)->find());

        $this->assign('prices', Price::scope('list', ['group' => 'express'])->select());
        $userPrices = $this->userInfo->userPrice()->where('group', '=', 'express')->select();
        if (!empty($userPrices)) {
            foreach ($userPrices as $index => $price) {
                $userPrices['pid_' . $price->price_id] = $price;
                unset($userPrices[$index]);
            }
        }
        $this->assign('user_prices', $userPrices);

        return $this->fetch();
    }

    public function myExpress()
    {
        $this->assign('page_text', Text::where([
            'position' => 'buyed_kongbao',
            'status' => 1
        ])->field(true)->find());

        return $this->fetch();
    }

    public function buyFlow()
    {
        $typeCondition = [
            'group' => 'flow',
            'orders' => ['type_id' => 'asc']
        ];
        $typeList = Price::scope('list', $typeCondition)->field(['type_id', 'type'])
            ->group('type_id')->select();
        $this->assign('type_list', $typeList);

        $typeId = 0;
        if (!empty($typeList)) {
            $typeId = $typeList[0]->type_id;
        }
        $this->assign('type_id', $typeId);

        $this->assign('prices', Price::scope('list', ['group' => 'flow'])->field(true)->select());
        return $this->fetch();
    }

    public function myFlow()
    {
        return $this->fetch();
    }

    public function buyCollection()
    {
        $typeCondition = [
            'group' => 'collection',
            'orders' => ['type_id' => 'asc']
        ];
        $typeList = Price::scope('list', $typeCondition)->field(['type_id', 'type'])
            ->group('type_id')->select();
        $this->assign('type_list', $typeList);

        $typeId = 0;
        if (!empty($typeList)) {
            $typeId = $typeList[0]->type_id;
        }
        $this->assign('type_id', $typeId);

        $this->assign('prices', Price::scope('list', ['group' => 'collection'])->field(true)->select());
        return $this->fetch();
    }

    public function myCollection()
    {
        return $this->fetch();
    }

    public function orderDocumentApply()
    {
        return $this->fetch();
    }

    public function myOrderDocument()
    {
        return $this->fetch();
    }

    public function uploadOrderNumber()
    {
        return $this->fetch();
    }
}