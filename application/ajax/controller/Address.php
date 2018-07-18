<?php

namespace app\ajax\controller;

use app\common\controller\Ajax;
use app\common\service\model\address\City;
use app\common\service\model\address\District;

class Address extends Ajax
{
    public function getCities()
    {
        $provinceId = input('get.province_id/d', 0);
        if ($provinceId <= 0) {
            $this->echoDataFormatToJson([
                'status' => 'fail',
                'msg' => '请选择省份'
            ]);
        }

        $cities = City::where('province_id', '=', $provinceId)->select();
        if (empty($cities)) {
            $this->echoDataFormatToJson([
                'status' => 'fail',
                'msg' => '所选择的省份下没有城市'
            ]);
        }

        $this->assign('cities', $cities);
        $this->echoDataFormatToJson([
            'status' => 'success',
            'data' => $this->fetch()
        ]);
    }

    public function getDistricts()
    {
        $cityId = input('get.city_id/d', 0);
        if ($cityId <= 0) {
            $this->echoDataFormatToJson([
                'status' => 'fail',
                'msg' => '请选择城市'
            ]);
        }

        $districts = District::where('city_id', '=', $cityId)->select();
        if (empty($districts)) {
            $this->echoDataFormatToJson([
                'status' => 'fail',
                'msg' => '所选择的城市下没有区县'
            ]);
        }

        $this->assign('districts', $districts);
        $this->echoDataFormatToJson([
            'status' => 'success',
            'data' => $this->fetch()
        ]);
    }

}