<?php

namespace app\common\helper;

class Ip
{
    /**
     * 获取客户端IP
     */
    public static function getClientIp()
    {
        if (isset($_SERVER)) {
            if (isset($_SERVER ['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $_SERVER ['HTTP_X_FORWARDED_FOR']);

                // 取X-Forwarded-For中第一个非unknown的有效IP字符串
                foreach ($arr as $ip) {
                    $ip = trim($ip);
                    if ($ip != 'unknown') {
                        $realIp = $ip;
                        break;
                    }
                }
            } elseif (isset($_SERVER ['HTTP_CLIENT_IP'])) {
                $realIp = $_SERVER ['HTTP_CLIENT_IP'];
            } else {
                if (isset($_SERVER ['REMOTE_ADDR'])) {
                    $realIp = $_SERVER ['REMOTE_ADDR'];
                } else {
                    $realIp = '0.0.0.0';
                }
            }
        } else {
            if (getenv('HTTP_X_FORWARDED_FOR')) {
                $arr = explode(',', getenv('HTTP_X_FORWARDED_FOR'));

                // 取X-Forwarded-For中第一个非unknown的有效IP字符串
                foreach ($arr as $ip) {
                    $ip = trim($ip);
                    if ($ip != 'unknown') {
                        $realIp = $ip;
                        break;
                    }
                }
            } elseif (getenv('HTTP_CLIENT_IP')) {
                $realIp = getenv('HTTP_CLIENT_IP');
            } else {
                $realIp = getenv('REMOTE_ADDR');
            }
        }

        preg_match("/[\d\.]{7,15}/", $realIp, $onlineIp);
        $realIp = !empty($onlineIp [0]) ? $onlineIp [0] : '0.0.0.0';

        return $realIp;
    }
}