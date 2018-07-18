<?php

namespace app\ajax\controller\index;

use app\common\controller\Ajax;
use app\common\service\api\Paikukb;
use app\common\service\model\address\ShippingAddress;
use app\common\service\model\product\OrderCollection;
use app\common\service\model\product\OrderExpress;
use app\common\service\model\product\OrderFlow;
use app\common\service\model\product\Price as PriceModel;
use think\Db;

class Order extends Ajax
{
    private $userInfo;

    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->chkAndGetUserInfo();
    }

    public function buyExpress()
    {
        $shippingAddressId = input('post.shipping_address_id/d', 0);
        $priceId = input('post.price_id/d', 0);
        $price = (float)input('post.price', 0);
        $deliveryAddress = input('post.delivery_address/s', '');
        $kg = (float)input('post.kg', 0);

        $shippingAddressInfo = ShippingAddress::get($shippingAddressId);
        if (empty($shippingAddressInfo) || $shippingAddressInfo->is_delete == 1) {
            $this->echoDataFormatToJson([
                'status' => 'fail',
                'msg' => '发货地址无效'
            ]);
        }

        $priceInfo = PriceModel::get($priceId);
        if (empty($priceInfo)) {
            $this->echoDataFormatToJson([
                'status' => 'fail',
                'msg' => '快递类型无效'
            ]);
        }

        $datetime = date('YmdHis');
        $rd1 = mt_rand(0, 9);
        $rd2 = mt_rand(10, 99);
        $rd3 = mt_rand(100, 999);

        $orderItems = [];
        if (!empty($deliveryAddress)) {
            $deliveryAddress = explode("\n", $deliveryAddress);
            foreach ($deliveryAddress as $index => $deliveryAddr) {
                $pid = $datetime;
                if ($index >= 100) {
                    $pid .= $rd1;
                } else if ($index >= 10) {
                    $pid .= $rd2;
                } else {
                    $pid .= $rd3;
                }
                $pid .= $index;

                $deliveryAddr = trim($deliveryAddr);
                if (!empty($deliveryAddr)) {
                    $orderItems[] = [
                        'user_id' => $this->userInfo->user_id,
                        'shipping_address_id' => $shippingAddressId,
                        'status' => 1,
                        'title' => $priceInfo->title,
                        'title_id' => $priceInfo->title_id,
                        'cost_price' => $priceInfo->cost_price,
                        'general_price' => $priceInfo->general_price,
                        'vip_price' => $priceInfo->vip_price,
                        'agent_price' => $priceInfo->agent_price,
                        'api_price' => $priceInfo->api_price,
                        'buy_price' => $price,
                        'post_pid' => $pid,
                        'post_msg' => $deliveryAddr,
                        'weight_kg' => $kg
                    ];
                }
            }
            unset($deliveryAddress);
        }

        if (empty($orderItems)) {
            $this->echoDataFormatToJson([
                'status' => 'fail',
                'msg' => '收货地址无效'
            ]);
        }

        $orderExpressModel = new OrderExpress();
        $buy = $orderExpressModel->saveAll($orderItems);
        if ($buy) {
            $this->echoDataFormatToJson([
                'status' => 'success',
                'msg' => '购买成功'
            ]);
        }

        $this->echoDataFormatToJson([
            'status' => 'fail',
            'msg' => '购买失败'
        ]);
    }

    public function getExpress()
    {
        $where = [];
        $deliveryStatus = input('get.delivery_status/d', 0);
        if ($deliveryStatus == 1) {
            $where['status'] = 3;
        } else {
            $where['status'] = ['<>', 3];
        }
        $logisticsNumber = input('get.logistics_number/s', '');
        if (!empty($logisticsNumber)) {
            $where['logistics_number'] = $logisticsNumber;
        }
        $gmtStart = input('get.gmt_start/s', '');
        $gmtEnd = input('get.gmt_end/s', '');
        if (!empty($gmtStart) && empty($gmtEnd) || empty($gmtStart) && !empty($gmtEnd)) {
            $this->echoDataFormatToJson([
                'status' => 'fail',
                'msg' => '下单时间填写不全'
            ]);
        } else if (!empty($gmtStart) && !empty($gmtEnd)) {
            $gmtPattern = '/^\d{4}\-\d{2}\-\d{2}$/';
            if (!preg_match($gmtPattern, $gmtStart) || !preg_match($gmtPattern, $gmtEnd)) {
                $this->echoDataFormatToJson([
                    'status' => 'fail',
                    'msg' => '下单时间格式错误，正确时间格式如：2018-01-01'
                ]);
            } else if ($gmtStart > $gmtEnd) {
                $this->echoDataFormatToJson([
                    'status' => 'fail',
                    'msg' => '下单时间前后顺序填写错误'
                ]);
            } else if (strtotime($gmtEnd) - strtotime($gmtStart) > 365 * 24 * 3600) {
                $this->echoDataFormatToJson([
                    'status' => 'fail',
                    'msg' => '下单时间查询区间最大支持365天跨度'
                ]);
            }
        }

        $this->assign('delivery_status', $deliveryStatus);
        $this->assign('express_list', OrderExpress::where($where)->field(true)
            ->paginate(10, false, ['type' => 'kongbao']));
        $this->echoDataFormatToJson([
            'status' => 'success',
            'data' => $this->fetch()
        ]);
    }

    public function buyFlow()
    {
        $priceId = input('post.price_id/d', 0);
        $price = (float)input('post.price', 0);
        $shopName = input('post.shop_name/s', '');
        $shopKeywords = input('post.shop_keywords/s', '');
        $shopUrl = input('post.shop_url/s', '');
        $shopProductId = '';
        if (is_numeric($shopUrl)) {
            $shopProductId = $shopUrl;
            $shopUrl = '';
        } else if (!filter_var($shopUrl, FILTER_VALIDATE_URL)) {
            $this->echoDataFormatToJson([
                'status' => 'fail',
                'msg' => '商品链接无效'
            ]);
        }

        $priceInfo = PriceModel::get($priceId);
        if (empty($priceInfo)) {
            $this->echoDataFormatToJson([
                'status' => 'fail',
                'msg' => '流量套餐无效'
            ]);
        }

        $flow = [
            'user_id' => $this->userInfo->user_id,
            'status' => 1,
            'shop_name' => $shopName,
            'shop_keywords' => $shopKeywords,
            'shop_url' => $shopUrl,
            'shop_product_id' => $shopProductId,
            'title' => $priceInfo->title,
            'title_id' => $priceInfo->title_id,
            'cost_price' => $priceInfo->cost_price,
            'general_price' => $priceInfo->general_price,
            'vip_price' => $priceInfo->vip_price,
            'agent_price' => $priceInfo->agent_price,
            'api_price' => $priceInfo->api_price,
            'buy_price' => $price,
            'order_number' => date('YmdHis') . mt_rand(1000, 9999)
        ];

        $buy = OrderFlow::create($flow);
        if ($buy) {
            $this->echoDataFormatToJson([
                'status' => 'success',
                'msg' => '购买成功'
            ]);
        }

        $this->echoDataFormatToJson([
            'status' => 'fail',
            'msg' => '购买失败'
        ]);
    }

    public function getFlow()
    {
        $oStatus = input('get.o_status/d', 0);
        $this->assign('o_status', $oStatus);

        $this->assign('flow_list', OrderFlow::where('status', '=', $oStatus)->field(true)
            ->paginate(10, false, ['type' => 'kongbao']));
        $this->echoDataFormatToJson([
            'status' => 'success',
            'data' => $this->fetch()
        ]);
    }

    public function buyCollection()
    {
        $priceId = input('post.price_id/d', 0);
        $price = (float)input('post.price', 0);
        $shopName = input('post.shop_name/s', '');
        $shopKeywords = input('post.shop_keywords/s', '');
        $shopUrl = input('post.shop_url/s', '');
        $shopProductId = '';
        if (is_numeric($shopUrl)) {
            $shopProductId = $shopUrl;
            $shopUrl = '';
        } else if (!filter_var($shopUrl, FILTER_VALIDATE_URL)) {
            $this->echoDataFormatToJson([
                'status' => 'fail',
                'msg' => '商品链接无效'
            ]);
        }

        $priceInfo = PriceModel::get($priceId);
        if (empty($priceInfo)) {
            $this->echoDataFormatToJson([
                'status' => 'fail',
                'msg' => '收藏套餐无效'
            ]);
        }

        $flow = [
            'user_id' => $this->userInfo->user_id,
            'status' => 1,
            'shop_name' => $shopName,
            'shop_keywords' => $shopKeywords,
            'shop_url' => $shopUrl,
            'shop_product_id' => $shopProductId,
            'title' => $priceInfo->title,
            'title_id' => $priceInfo->title_id,
            'cost_price' => $priceInfo->cost_price,
            'general_price' => $priceInfo->general_price,
            'vip_price' => $priceInfo->vip_price,
            'agent_price' => $priceInfo->agent_price,
            'api_price' => $priceInfo->api_price,
            'buy_price' => $price,
            'order_number' => date('YmdHis') . mt_rand(1000, 9999)
        ];

        $buy = OrderCollection::create($flow);
        if ($buy) {
            $this->echoDataFormatToJson([
                'status' => 'success',
                'msg' => '购买成功'
            ]);
        }

        $this->echoDataFormatToJson([
            'status' => 'fail',
            'msg' => '购买失败'
        ]);
    }

    public function getCollection(){
        $oStatus = input('get.o_status/d', 0);
        $this->assign('o_status', $oStatus);

        $this->assign('collection_list', OrderCollection::where('status', '=', $oStatus)->field(true)
            ->paginate(10, false, ['type' => 'kongbao']));
        $this->echoDataFormatToJson([
            'status' => 'success',
            'data' => $this->fetch()
        ]);
    }
}

