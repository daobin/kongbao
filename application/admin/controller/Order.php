<?php

namespace app\admin\controller;

use app\common\constant\Route;
use app\common\constant\Session;
use app\common\controller\Controller;
use app\common\service\api\Paikukb;
use app\common\service\model\address\ShippingAddress;
use app\common\service\model\product\OrderCollection;
use app\common\service\model\product\OrderExpress;
use app\common\service\model\product\OrderFlow;
use app\common\service\model\product\Price;

class Order extends Controller
{
    public function express()
    {
        $activeData = input('get.active_data', '');
        $searchKey = input('get.s_key/s', '');
        $searchVal = input('get.s_val/s', '');
        $this->assign('active_data', $activeData);
        $this->assign('s_key', $searchKey);
        $this->assign('s_val', $searchVal);

        $where = [];
        $saleActive = '';
        $postActive = '';
        $deliveryActive = '';
        switch ($activeData) {
            case 'post':
                $postActive = 'active';
                $where['status'] = 2;
                break;
            case 'delivery':
                $deliveryActive = 'active';
                $where['status'] = 3;
                break;
            default:
                $saleActive = 'active';
                $where['status'] = 1;
                break;
        }
        $this->assign('sale_active', $saleActive);
        $this->assign('post_active', $postActive);
        $this->assign('delivery_active', $deliveryActive);

        if ($searchVal != '') {
            switch ($searchKey) {
                case 'user_account':
                    $userInfo = \app\common\service\model\user\User::getByUserAccount($searchVal);
                    $userId = 0;
                    if (!empty($userInfo)) {
                        $userId = $userInfo->user_id;
                    }
                    $where['user_id'] = $userId;
                    break;
                case 'logistics_number':
                    $where['logistics_number'] = $searchVal;
                    break;
                case 'shipping_name':
                    $shippingAddressInfo = ShippingAddress::getByName($searchVal);
                    $shippingAddressId = 0;
                    if (!empty($shippingAddressInfo)) {
                        $shippingAddressId = $shippingAddressInfo->shipping_address_id;
                    }
                    $where['shipping_address_id'] = $shippingAddressId;
                    break;
                case 'delivery_name':
                    $where['post_msg'] = ['like', $searchVal . '%'];
                    break;
            }
        }

        $titleId = input('get.title_id/d', 0);
        if ($titleId > 0) {
            $where['title_id'] = $titleId;
        }
        $this->assign('title_id', $titleId);

        $pageQuery = input('get.');
        if (isset($pageQuery['page'])) {
            unset($pageQuery['page']);
        }
        $orders = OrderExpress::where($where)->field(true)
            ->order('order_express_id', 'desc')->paginate(null, false, [
                'query' => $pageQuery
            ]);
        $this->assign('orders', $orders);


        $prices = Price::scope('list', ['group' => 'express'])->select();
        $this->assign('prices', $prices);

        return $this->fetch();
    }

    public function postExpress()
    {
        $titleId = input('post.title_id/d', 0);
        if (!input('?post.title_id') || $titleId <= 0) {
            session(Session::ERROR_MSG, '请选择快递');
            $this->redirect(Route::ADMIN_ORDER_EXPRESS);
        }

        $orders = OrderExpress::where([
            'title_id' => $titleId, 'status' => 1
        ])->field(true)->order('order_express_id', 'desc')->select();
        if (empty($orders)) {
            session(Session::ERROR_MSG, '选择快递下没有数据');
            $this->redirect(Route::ADMIN_ORDER_EXPRESS, ['title_id' => $titleId]);
        }

        $failedDeliveryAddress = [];
        $updates = [];

        $paikukb = new Paikukb();
        foreach ($orders as $order) {
            $data = [
                'info' => 1,
                'kdid' => $order->title_id,
                'num' => 1,
                'kg' => $order->weight_kg > 0 ? $order->weight_kg : null,
                'items' => [
                    [
                        'pid' => $order->post_pid,
                        'msg' => $order->post_msg
                    ]
                ],
                'postAddrItem' => [
                    'province' => $order->shipping_address->province_name,
                    'city' => $order->shipping_address->city_name,
                    'area' => $order->shipping_address->district_name,
                    'postName' => $order->shipping_address->name,
                    'postPhone' => $order->shipping_address->telephone,
                    'addr' => $order->shipping_address->address
                ]
            ];

            $response = $paikukb->doCurl('BuyKddh', $data);
            if ($response['status'] != 'success' || !isset($response['data']['kddhs'])) {
                $failedDeliveryAddress[] = '收货信息为【' . $order->post_msg . '】的快递提交总站失败，原因：' . $response['msg'];
                continue;
            }

            $response = reset($response['data']['kddhs']);
            $updates[] = [
                'order_express_id' => $order->order_express_id,
                'status' => 2,
                'post_price' => $response['price'],
                'logistics_number' => $response['num'],
                'gmt_post' => date('Y-m-d H:i:s')
            ];
        }

        if (!empty($updates)) {
            $orderExpressModel = new OrderExpress();
            $orderExpressModel->saveAll($updates);
        }

        if (!empty($failedDeliveryAddress)) {
            $msg = '以下收货信息快递提交至总站操作失败：<br/><br/><br/>';
            $msg .= implode('<br/><br/>', $failedDeliveryAddress);
            session(Session::ERROR_MSG, $msg);
            $this->redirect(Route::ADMIN_ORDER_EXPRESS, ['title_id' => $titleId]);
        } else {
            session(Session::SUCCESS_MSG, '提交选择快递至总站操作成功');
            $this->redirect(Route::ADMIN_ORDER_EXPRESS, ['title_id' => $titleId]);
        }
    }

    public function flow()
    {
        $activeData = input('get.active_data', '');
        $searchKey = input('get.s_key/s', '');
        $searchVal = input('get.s_val/s', '');
        $this->assign('active_data', $activeData);
        $this->assign('s_key', $searchKey);
        $this->assign('s_val', $searchVal);

        $where = [];
        $saleActive = '';
        $deliveryActive = '';
        $completeActive = '';
        $cancelActive = '';
        switch ($activeData) {
            case 'delivery':
                $deliveryActive = 'active';
                $where['status'] = 2;
                break;
            case 'complete':
                $completeActive = 'active';
                $where['status'] = 3;
                break;
            case 'cancel':
                $cancelActive = 'active';
                $where['status'] = 4;
                break;
            default:
                $saleActive = 'active';
                $where['status'] = 1;
                break;
        }
        $this->assign('sale_active', $saleActive);
        $this->assign('delivery_active', $deliveryActive);
        $this->assign('complete_active', $completeActive);
        $this->assign('cancel_active', $cancelActive);

        if ($searchVal != '') {
            switch ($searchKey) {
                case 'user_account':
                    $userInfo = \app\common\service\model\user\User::getByUserAccount($searchVal);
                    $userId = 0;
                    if (!empty($userInfo)) {
                        $userId = $userInfo->user_id;
                    }
                    $where['user_id'] = $userId;
                    break;
                case 'title':
                    $where['title'] = ['like', "%{$searchVal}%"];
                    break;
            }
        }

        $titleId = input('get.title_id/d', 0);
        if ($titleId > 0) {
            $where['title_id'] = $titleId;
        }
        $this->assign('title_id', $titleId);

        $pageQuery = input('get.');
        if (isset($pageQuery['page'])) {
            unset($pageQuery['page']);
        }
        $orders = OrderFlow::where($where)->field(true)
            ->order('order_flow_id', 'desc')->paginate(null, false, [
                'query' => $pageQuery
            ]);
        $this->assign('orders', $orders);

        $prices = Price::scope('list', ['group' => 'flow'])->select();
        $this->assign('prices', $prices);

        return $this->fetch();
    }

    public function postFlow(){
        $titleId = input('post.title_id/d', 0);
        if (!input('?post.title_id') || $titleId <= 0) {
            session(Session::ERROR_MSG, '请选择流量');
            $this->redirect(Route::ADMIN_ORDER_FLOW);
        }

        $orders = OrderFlow::where([
            'title_id' => $titleId, 'status' => 1
        ])->field(true)->order('order_flow_id', 'desc')->select();
        if (empty($orders)) {
            session(Session::ERROR_MSG, '选择流量下没有数据');
            $this->redirect(Route::ADMIN_ORDER_FLOW, ['title_id' => $titleId]);
        }
    }

    public function collection()
    {
        $activeData = input('get.active_data', '');
        $searchKey = input('get.s_key/s', '');
        $searchVal = input('get.s_val/s', '');
        $this->assign('active_data', $activeData);
        $this->assign('s_key', $searchKey);
        $this->assign('s_val', $searchVal);

        $where = [];
        $saleActive = '';
        $deliveryActive = '';
        $completeActive = '';
        $cancelActive = '';
        switch ($activeData) {
            case 'delivery':
                $deliveryActive = 'active';
                $where['status'] = 2;
                break;
            case 'complete':
                $completeActive = 'active';
                $where['status'] = 3;
                break;
            case 'cancel':
                $cancelActive = 'active';
                $where['status'] = 4;
                break;
            default:
                $saleActive = 'active';
                $where['status'] = 1;
                break;
        }
        $this->assign('sale_active', $saleActive);
        $this->assign('delivery_active', $deliveryActive);
        $this->assign('complete_active', $completeActive);
        $this->assign('cancel_active', $cancelActive);

        if ($searchVal != '') {
            switch ($searchKey) {
                case 'user_account':
                    $userInfo = \app\common\service\model\user\User::getByUserAccount($searchVal);
                    $userId = 0;
                    if (!empty($userInfo)) {
                        $userId = $userInfo->user_id;
                    }
                    $where['user_id'] = $userId;
                    break;
                case 'title':
                    $where['title'] = ['like', "%{$searchVal}%"];
                    break;
            }
        }

        $titleId = input('get.title_id/d', 0);
        if ($titleId > 0) {
            $where['title_id'] = $titleId;
        }
        $this->assign('title_id', $titleId);

        $pageQuery = input('get.');
        if (isset($pageQuery['page'])) {
            unset($pageQuery['page']);
        }
        $orders = OrderCollection::where($where)->field(true)
            ->order('order_collection_id', 'desc')->paginate(null, false, [
                'query' => $pageQuery
            ]);
        $this->assign('orders', $orders);

        $prices = Price::scope('list', ['group' => 'collection'])->select();
        $this->assign('prices', $prices);

        return $this->fetch();
    }

    public function postCollection(){
        $titleId = input('post.title_id/d', 0);
        if (!input('?post.title_id') || $titleId <= 0) {
            session(Session::ERROR_MSG, '请选择收藏');
            $this->redirect(Route::ADMIN_ORDER_COLLECTION);
        }

        $orders = OrderCollection::where([
            'title_id' => $titleId, 'status' => 1
        ])->field(true)->order('order_collection_id', 'desc')->select();
        if (empty($orders)) {
            session(Session::ERROR_MSG, '选择收藏下没有数据');
            $this->redirect(Route::ADMIN_ORDER_COLLECTION, ['title_id' => $titleId]);
        }
    }
}