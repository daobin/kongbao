<?php

namespace app\common\service\model\user;

use app\common\service\Model;

class User extends Model
{
    public function userLevel()
    {
        return $this->belongsTo('UserLevel');
    }

    public function userPrice()
    {
        return $this->hasMany('UserPrice');
    }

    public function getRecommendUserIdInfoAttr($val, $data)
    {
        $recommendUserId = (int)$data['recommend_user_id'];
        if ($recommendUserId <= 0) {
            return false;
        }

        return self::get($recommendUserId);
    }

    public function getMyRecommendUsersCntAttr($val, $data)
    {
        return self::where(['recommend_user_id' => (int)$data['user_id']])->count();
    }
}