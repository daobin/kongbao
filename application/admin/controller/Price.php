<?php

namespace app\admin\controller;

use app\common\constant\Route;
use app\common\constant\Session;
use app\common\controller\Controller;
use app\common\service\api\Paikukb;
use app\common\service\model\configuration\Configuration;
use app\common\service\model\configuration\RechargeReward;
use app\common\service\model\product\Price as PriceMoel;
use app\common\service\model\user\UserLevel;
use think\Db;

class Price extends Controller
{
    public function express()
    {
        if (input('?get.del_ids')) {
            $this->expressDelete();
        }

        if (request()->isPost()) {
            $this->expressSave();
        }

        $priceProfits = Configuration::where('group', 'PRICE_PROFIT')->field(true)->select();
        foreach ($priceProfits as $priceProfit) {
            $this->assign($priceProfit->key, (float)$priceProfit->value);
        }

        $prices = PriceMoel::scope('list', ['group' => 'express'])->select();
        $this->assign('prices', $prices);
        return $this->fetch();
    }

    private function expressDelete()
    {
        $delIds = input('get.del_ids/s', '');
        if ($delIds == 'all') {
            $del = PriceMoel::where(['group' => 'express', 'price_id' => ['<>', 0]])->delete();
            if ($del) {
                session(Session::SUCCESS_MSG, '快递信息删除成功');
            } else {
                session(Session::ERROR_MSG, '快递信息删除失败');
            }
            $this->redirect(Route::ADMIN_PRICE_EXPRESS);
        }

        $delIds = trim($delIds, ',');
        if ($delIds == '') {
            session(Session::ERROR_MSG, '没有选择需要删除的快递信息');
            $this->redirect(Route::ADMIN_PRICE_EXPRESS);
        }

        $delIds = explode(',', $delIds);
        $del = PriceMoel::where('price_id', 'in', $delIds)->delete();
        if ($del) {
            session(Session::SUCCESS_MSG, '快递信息删除成功');
        } else {
            session(Session::ERROR_MSG, '快递信息删除失败');
        }
        $this->redirect(Route::ADMIN_PRICE_EXPRESS);
    }

    private function expressSave()
    {
        if (input('?post.batch_profit_set')) {
            $generalProfit = (float)input('post.general_profit');
            $vipProfit = (float)input('post.vip_profit');
            $agentProfit = (float)input('post.agent_profit');
            $apiProfit = (float)input('post.api_profit');

            Db::startTrans();
            try {
                Configuration::update(['value' => $generalProfit], ['key' => 'PRICE_PROFIT_OF_GENERAL']);
                Configuration::update(['value' => $vipProfit], ['key' => 'PRICE_PROFIT_OF_VIP']);
                Configuration::update(['value' => $agentProfit], ['key' => 'PRICE_PROFIT_OF_AGENT']);
                Configuration::update(['value' => $apiProfit], ['key' => 'PRICE_PROFIT_OF_API']);

                $sql = "update `kb_price` set `general_price` = `cost_price` + :general_profit";
                $sql .= ", `vip_price` = `cost_price` + :vip_profit";
                $sql .= ", `agent_price` = `cost_price` + :agent_profit";
                $sql .= ", `api_price` = `cost_price` + :api_profit";
                $sql .= " where `price_id` <> 0 and `group` = 'express'";
                Db::execute($sql, [
                    'general_profit' => $generalProfit,
                    'vip_profit' => $vipProfit,
                    'agent_profit' => $agentProfit,
                    'api_profit' => $apiProfit
                ]);
                session(Session::SUCCESS_MSG, '批量设置利润成功');
                Db::commit();
            } catch (\Exception $e) {
                session(Session::ERROR_MSG, '批量设置利润失败');
                Db::rollback();
            }

            $this->redirect(Route::ADMIN_PRICE_EXPRESS);
        }

        $generalPrices = input('post.general_prices/a');
        $vipPrices = input('post.vip_prices/a');
        $agentPrices = input('post.agent_prices/a');
        $apiPrices = input('post.api_prices/a');
        $sorts = input('post.sorts/a');
        $status = input('post.status/a');

        $saveDatas = [];
        foreach ($generalPrices as $priceId => $generalPrice) {
            $priceId = (int)$priceId;
            $saveDatas[] = [
                'price_id' => $priceId,
                'general_price' => (float)$generalPrice,
                'vip_price' => (float)$vipPrices[$priceId],
                'agent_price' => (float)$agentPrices[$priceId],
                'api_price' => (float)$apiPrices[$priceId],
                'sort' => (int)$sorts[$priceId],
                'status' => (int)$status[$priceId]
            ];
        }

        $priceModel = new PriceMoel();
        $save = $priceModel->saveAll($saveDatas);
        if ($save) {
            session(Session::SUCCESS_MSG, '保存成功');
        } else {
            session(Session::ERROR_MSG, '保存失败');
        }
        $this->redirect(Route::ADMIN_PRICE_EXPRESS);
    }

    public function syncExpress()
    {
        $paikukb = new Paikukb();
        $expressList = $paikukb->doCurl('GetKd');
        if (trim($expressList['status']) != 'success') {
            session(Session::ERROR_MSG, trim($expressList['msg']));
            $this->redirect(Route::ADMIN_PRICE_EXPRESS);
        }

        $expressList = $expressList['data']['kdPrice'];
        if (empty($expressList)) {
            session(Session::ERROR_MSG, '没有获取到总站快递信息，请稍候重试');
            $this->redirect(Route::ADMIN_PRICE_EXPRESS);
        }

        Db::startTrans();
        try {
            foreach ($expressList as $express) {
                $where = ['group' => 'express', 'title_id' => (int)$express['kdId']];
                if (PriceMoel::where($where)->find()) {
                    PriceMoel::update([
                        'title' => trim($express['kdName']),
                        'cost_price' => (float)$express['apiPrice'],
                        'tip' => trim($express['tips']),
                        'operator' => session('admin.account')
                    ], $where);
                } else {
                    PriceMoel::create([
                        'group' => 'express',
                        'title' => trim($express['kdName']),
                        'title_id' => (int)$express['kdId'],
                        'cost_price' => (float)$express['apiPrice'],
                        'tip' => trim($express['tips']),
                        'operator' => session('admin.account')
                    ]);
                }
            }
            session(Session::SUCCESS_MSG, '同步总站快递信息成功');
            Db::commit();
        } catch (\Exception $e) {
            session(Session::ERROR_MSG, '同步总站快递信息失败');
            Db::rollback();
        }

        $this->redirect(Route::ADMIN_PRICE_EXPRESS);
    }

    public function flow()
    {
        if (input('?get.del_ids')) {
            $this->flowDelete();
        }

        if (request()->isPost()) {
            $this->flowSave();
        }

        $types = PriceMoel::where('group', 'flow')->field(['type_id', 'type'])
            ->group('type_id')->order('type_id', 'asc')->select();
        $this->assign('types', $types);

        $typeId = input('get.type_id/d', $types ? (int)$types[0]->type_id : 0);
        $this->assign('type_id', $typeId);

        $prices = PriceMoel::scope('list', ['group' => 'flow', 'type_id' => $typeId])->select();
        $this->assign('prices', $prices);
        return $this->fetch();
    }

    private function flowDelete()
    {
        $delIds = input('get.del_ids/s', '');
        if ($delIds == 'all') {
            $del = PriceMoel::where(['group' => 'flow', 'price_id' => ['<>', 0]])->delete();
            if ($del) {
                session(Session::SUCCESS_MSG, '流量信息删除成功');
            } else {
                session(Session::ERROR_MSG, '流量信息删除失败');
            }
            $this->redirect(Route::ADMIN_PRICE_FLOW);
        }

        $delIds = trim($delIds, ',');
        if ($delIds == '') {
            session(Session::ERROR_MSG, '没有选择需要删除的流量信息');
            $this->redirect(Route::ADMIN_PRICE_FLOW);
        }

        $delIds = explode(',', $delIds);
        $del = PriceMoel::where('price_id', 'in', $delIds)->delete();
        if ($del) {
            session(Session::SUCCESS_MSG, '流量信息删除成功');
        } else {
            session(Session::ERROR_MSG, '流量信息删除失败');
        }
        $this->redirect(Route::ADMIN_PRICE_FLOW);
    }

    private function flowSave()
    {
        $generalPrices = input('post.general_prices/a');
        $vipPrices = input('post.vip_prices/a');
        $agentPrices = input('post.agent_prices/a');
        $apiPrices = input('post.api_prices/a');
        $sorts = input('post.sorts/a');
        $status = input('post.status/a');

        $saveDatas = [];
        foreach ($generalPrices as $priceId => $generalPrice) {
            $priceId = (int)$priceId;
            $saveDatas[] = [
                'price_id' => $priceId,
                'general_price' => (float)$generalPrice,
                'vip_price' => (float)$vipPrices[$priceId],
                'agent_price' => (float)$agentPrices[$priceId],
                'api_price' => (float)$apiPrices[$priceId],
                'sort' => (int)$sorts[$priceId],
                'status' => (int)$status[$priceId]
            ];
        }

        $priceModel = new PriceMoel();
        $save = $priceModel->saveAll($saveDatas);
        if ($save) {
            session(Session::SUCCESS_MSG, '保存成功');
        } else {
            session(Session::ERROR_MSG, '保存失败');
        }
        $this->redirect(Route::ADMIN_PRICE_FLOW);
    }

    public function syncFlow()
    {
        $paikukb = new Paikukb();
        $flowList = $paikukb->doCurl('GetFlow');
        if (trim($flowList['status']) != 'success') {
            session(Session::ERROR_MSG, trim($flowList['msg']));
            $this->redirect(Route::ADMIN_PRICE_FLOW);
        }

        $flowList = $flowList['data']['flowPrice'];
        if (empty($flowList)) {
            session(Session::ERROR_MSG, '没有获取到总站流量信息，请稍候重试');
            $this->redirect(Route::ADMIN_PRICE_FLOW);
        }

        Db::startTrans();
        try {
            foreach ($flowList as $flow) {
                $where = ['group' => 'flow', 'title_id' => (int)$flow['id']];
                if (PriceMoel::where($where)->find()) {
                    PriceMoel::update([
                        'title' => trim($flow['title']),
                        'type' => trim($flow['typeTitle']),
                        'type_id' => (int)$flow['typeId'],
                        'cost_price' => (float)$flow['apiPrice'],
                        'tip' => trim($flow['intro']),
                        'operator' => session('admin.account')
                    ], $where);
                } else {
                    PriceMoel::create([
                        'group' => 'flow',
                        'title' => trim($flow['title']),
                        'title_id' => (int)$flow['id'],
                        'type' => trim($flow['typeTitle']),
                        'type_id' => (int)$flow['typeId'],
                        'cost_price' => (float)$flow['apiPrice'],
                        'tip' => trim($flow['intro']),
                        'operator' => session('admin.account')
                    ]);
                }
            }
            session(Session::SUCCESS_MSG, '同步总站流量信息成功');
            Db::commit();
        } catch (\Exception $e) {
            session(Session::ERROR_MSG, '同步总站流量信息失败');
            Db::rollback();
        }

        $this->redirect(Route::ADMIN_PRICE_FLOW);
    }

    public function collection()
    {
        if (input('?get.del_ids')) {
            $this->collectionDelete();
        }

        if (request()->isPost()) {
            $this->collectionSave();
        }

        $types = PriceMoel::where('group', 'collection')->field(['type_id', 'type'])
            ->group('type_id')->order('type_id', 'asc')->select();
        $this->assign('types', $types);

        $typeId = input('get.type_id/d', $types ? (int)$types[0]->type_id : 0);
        $this->assign('type_id', $typeId);

        $prices = PriceMoel::scope('list', ['group' => 'collection', 'type_id' => $typeId])->select();
        $this->assign('prices', $prices);
        return $this->fetch();
    }

    private function collectionDelete()
    {
        $delIds = input('get.del_ids/s', '');
        if ($delIds == 'all') {
            $del = PriceMoel::where(['group' => 'collection', 'price_id' => ['<>', 0]])->delete();
            if ($del) {
                session(Session::SUCCESS_MSG, '流量信息删除成功');
            } else {
                session(Session::ERROR_MSG, '流量信息删除失败');
            }
            $this->redirect(Route::ADMIN_PRICE_COLLECTION);
        }

        $delIds = trim($delIds, ',');
        if ($delIds == '') {
            session(Session::ERROR_MSG, '没有选择需要删除的流量信息');
            $this->redirect(Route::ADMIN_PRICE_COLLECTION);
        }

        $delIds = explode(',', $delIds);
        $del = PriceMoel::where('price_id', 'in', $delIds)->delete();
        if ($del) {
            session(Session::SUCCESS_MSG, '流量信息删除成功');
        } else {
            session(Session::ERROR_MSG, '流量信息删除失败');
        }
        $this->redirect(Route::ADMIN_PRICE_COLLECTION);
    }

    private function collectionSave()
    {
        $generalPrices = input('post.general_prices/a');
        $vipPrices = input('post.vip_prices/a');
        $agentPrices = input('post.agent_prices/a');
        $apiPrices = input('post.api_prices/a');
        $sorts = input('post.sorts/a');
        $status = input('post.status/a');

        $saveDatas = [];
        foreach ($generalPrices as $priceId => $generalPrice) {
            $priceId = (int)$priceId;
            $saveDatas[] = [
                'price_id' => $priceId,
                'general_price' => (float)$generalPrice,
                'vip_price' => (float)$vipPrices[$priceId],
                'agent_price' => (float)$agentPrices[$priceId],
                'api_price' => (float)$apiPrices[$priceId],
                'sort' => (int)$sorts[$priceId],
                'status' => (int)$status[$priceId]
            ];
        }

        $priceModel = new PriceMoel();
        $save = $priceModel->saveAll($saveDatas);
        if ($save) {
            session(Session::SUCCESS_MSG, '保存成功');
        } else {
            session(Session::ERROR_MSG, '保存失败');
        }
        $this->redirect(Route::ADMIN_PRICE_COLLECTION);
    }

    public function syncCollection()
    {
        $paikukb = new Paikukb();
        $collectionList = $paikukb->doCurl('GetSC');
        if (trim($collectionList['status']) != 'success') {
            session(Session::ERROR_MSG, trim($collectionList['msg']));
            $this->redirect(Route::ADMIN_PRICE_COLLECTION);
        }

        $collectionList = $collectionList['data']['flowPrice'];
        if (empty($collectionList)) {
            session(Session::ERROR_MSG, '没有获取到总站收藏信息，请稍候重试');
            $this->redirect(Route::ADMIN_PRICE_COLLECTION);
        }

        Db::startTrans();
        try {
            foreach ($collectionList as $collection) {
                $where = ['group' => 'collection', 'title_id' => (int)$collection['id']];
                if (PriceMoel::where($where)->find()) {
                    PriceMoel::update([
                        'title' => trim($collection['title']),
                        'type' => trim($collection['typeTitle']),
                        'type_id' => (int)$collection['typeId'],
                        'cost_price' => (float)$collection['apiPrice'],
                        'tip' => trim($collection['intro']),
                        'operator' => session('admin.account')
                    ], $where);
                } else {
                    PriceMoel::create([
                        'group' => 'collection',
                        'title' => trim($collection['title']),
                        'title_id' => (int)$collection['id'],
                        'type' => trim($collection['typeTitle']),
                        'type_id' => (int)$collection['typeId'],
                        'cost_price' => (float)$collection['apiPrice'],
                        'tip' => trim($collection['intro']),
                        'operator' => session('admin.account')
                    ]);
                }
            }
            session(Session::SUCCESS_MSG, '同步总站收藏信息成功');
            Db::commit();
        } catch (\Exception $e) {
            session(Session::ERROR_MSG, '同步总站收藏信息失败');
            Db::rollback();
        }

        $this->redirect(Route::ADMIN_PRICE_COLLECTION);
    }

    public function vip()
    {
        if (request()->isPost()) {
            $this->vipSave();
        }

        $this->assign('user_levels', UserLevel::all());
        return $this->fetch();
    }

    private function vipSave()
    {
        $levelPrices = input('post.level_prices/a', []);
        if (empty($levelPrices)) {
            session(Session::ERROR_MSG, '提交数据无效，请刷新页面重试');
            $this->redirect(Route::ADMIN_PRICE_VIP);
        }

        Db::startTrans();
        try {
            foreach ($levelPrices as $id => $price) {
                UserLevel::update([
                    'level_price' => (float)$price,
                    'operator' => session('admin.account')
                ], ['user_level_id' => (int)$id]);
            }

            session(Session::SUCCESS_MSG, '保存成功');
            Db::commit();
        } catch (\Exception $e) {
            session(Session::ERROR_MSG, '保存失败');
            Db::rollback();
        }

        $this->redirect(Route::ADMIN_PRICE_VIP);
    }

    public function recharge()
    {
        if (request()->isPost()) {
            $this->rechargeSave();
        }

        $this->assign('recharge_rewards', RechargeReward::order('recharge_price', 'asc')
            ->select());

        return $this->fetch();
    }

    private function rechargeSave()
    {
        $recharges = input('post.recharges/a', []);
        $rewards = input('post.rewards/a', []);

        if (empty($recharges)) {
            session(Session::ERROR_MSG, '请新增充值奖励');
            $this->redirect(url(Route::ADMIN_PRICE_RECHARGE));
        }

        $inDatas = [];
        $upDatas = [];
        foreach ($recharges as $id => $recharge) {
            $recharge = (float)$recharge;
            $reward = (float)$rewards[$id];
            if ($id == '__I__' || $recharge <= 0 || $reward <= 0) {
                continue;
            }

            if (strpos($id, 'i_') !== false) {
                $inDatas[] = [
                    'recharge_price' => $recharge,
                    'reward_price' => $reward,
                    'operator' => session('admin.account')
                ];
            } else {
                $upDatas[] = [
                    'recharge_reward_id' => $id,
                    'recharge_price' => $recharge,
                    'reward_price' => $reward,
                    'operator' => session('admin.account')
                ];
            }
        }

        if (empty($inDatas) && empty($upDatas)) {
            session(Session::ERROR_MSG, '请新增充值奖励');
            $this->redirect(url(Route::ADMIN_PRICE_RECHARGE));
        }

        $rechargeRewardModel = new RechargeReward();
        if (!empty($inDatas)) {
            $rechargeRewardModel->saveAll($inDatas);
        }
        if (!empty($upDatas)) {
            $rechargeRewardModel->saveAll($upDatas);
        }

        session(Session::SUCCESS_MSG, '保存成功');
        $this->redirect(Route::ADMIN_PRICE_RECHARGE);
    }

    public function recommend()
    {
        if (request()->isPost()) {
            $this->recommendSave();
        }

        $this->assign('user_levels', UserLevel::all());
        return $this->fetch();
    }

    private function recommendSave()
    {
        $recommendPrices = input('post.recommend_prices/a', []);
        if (empty($recommendPrices)) {
            session(Session::ERROR_MSG, '提交数据无效，请刷新页面重试');
            $this->redirect(Route::ADMIN_PRICE_RECOMMEND);
        }

        Db::startTrans();
        try {
            foreach ($recommendPrices as $id => $price) {
                $price = (float)$price;
                if ($price <= 0) {
                    $price = 0;
                } else if ($price >= 99.99) {
                    $price = 99.99;
                }

                UserLevel::update([
                    'recommend_price' => $price,
                    'operator' => session('admin.account')
                ], ['user_level_id' => (int)$id]);
            }

            session(Session::SUCCESS_MSG, '保存成功');
            Db::commit();
        } catch (\Exception $e) {
            session(Session::ERROR_MSG, '保存失败');
            Db::rollback();
        }

        $this->redirect(Route::ADMIN_PRICE_RECOMMEND);
    }
}
