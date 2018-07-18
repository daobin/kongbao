<?php

namespace app\ajax\controller\index;

use app\common\controller\Ajax;
use app\common\service\model\address\ShippingAddress;
use think\Db;
use think\Request;

class Address extends Ajax
{
    private $userInfo;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);

        $this->userInfo = $this->chkAndGetUserInfo();
    }

    public function addShippingAddress()
    {
        $addressData = input('post.');
        $addressData['user_id'] = $this->userInfo->user_id;
        $shippingAddress = new ShippingAddress($addressData);
        if ($shippingAddress->allowField(true)->save()) {
            $this->echoDataFormatToJson([
                'status' => 'success',
                'msg' => '发货人地址信息添加成功'
            ]);
        }

        $this->echoDataFormatToJson([
            'status' => 'fail',
            'msg' => '发货人地址信息添加失败'
        ]);
    }

    public function getShippingAddress()
    {
        $search = input('get.search/s', '');

        $condition = [
            'user_id' => $this->userInfo->user_id,
            'is_delete' => 0
        ];
        if ($search != '') {
            $condition['search'] = $search;
        }

        $selectAll = input('get.select_all/s', '0');
        $this->assign('select_all', $selectAll);
        if ($selectAll == '1') {
            $this->assign('address', ShippingAddress::scope('list', $condition)->select());
        } else {
            $this->assign('address', ShippingAddress::scope('list', $condition)
                ->paginate(1, false, ['type' => 'kongbao']));
        }

        $this->echoDataFormatToJson([
            'status' => 'success',
            'data' => $this->fetch()
        ]);
    }

    private function chkUserAddress($addressId)
    {
        $addressInfo = ShippingAddress::where([
            'shipping_address_id' => (int)$addressId,
            'user_id' => (int)($this->userInfo->user_id)
        ])->find();
        if (empty($addressInfo)) {
            $this->echoDataFormatToJson([
                'status' => 'fail',
                'msg' => '当前地址无效'
            ]);
        }
    }

    public function setDefaultShippingAddrss()
    {
        $set = true;
        $addressId = input('post.address_id/d', 0);
        $this->chkUserAddress($addressId);

        try {
            Db::startTrans();

            ShippingAddress::update(['is_default' => 0], ['shipping_address_id' => ['<>', $addressId]]);
            ShippingAddress::update(['is_default' => 1], ['shipping_address_id' => $addressId]);

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $set = false;
        }

        if ($set) {
            $this->echoDataFormatToJson([
                'status' => 'success',
                'msg' => '设置成功'
            ]);
        }

        $this->echoDataFormatToJson([
            'status' => 'fail',
            'msg' => '设置失败'
        ]);
    }

    public function deleteShippingAddress()
    {
        $addressId = input('post.address_id/d', 0);
        $this->chkUserAddress($addressId);

        if (ShippingAddress::update(['is_delete' => 1], ['shipping_address_id' => $addressId])) {
            $this->echoDataFormatToJson([
                'status' => 'success',
                'msg' => '删除成功'
            ]);
        }

        $this->echoDataFormatToJson([
            'status' => 'fail',
            'msg' => '删除失败'
        ]);
    }

}