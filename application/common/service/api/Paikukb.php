<?php

namespace app\common\service\api;

use think\Log;

class Paikukb
{
    private static $services;

    public function __construct()
    {
        self::$services = ['GetKd', 'GetFlow', 'GetSC', 'BuyKddh', 'BuyFlowSC'];
    }

    public function doCurl($service, $params = null)
    {
        Log::init([
            'type' => 'File',
            'path' => LOG_PATH . 'paikukb' . DS . $service . DS,
        ]);
        Log::record('Request Start >>' . print_r($params, true));

        $service = trim($service);
        if (!in_array($service, self::$services)) {
            $response = [
                'status' => 'fail',
                'msg' => '服务错误'
            ];
            Log::record('Response >>' . print_r($response, true));
            return $response;
        }

        $username = API_USERNAME;
        $password = substr(md5(API_PASSWORD), 8, 16);
        $sid = uniqid() . time();

        $jsonData = [
            'username' => $username,
            'sid' => $sid,
            'sign' => md5($username . $password . $sid)
        ];
        if (isset($params['info'])) {
            $jsonData = [
                'info' => $jsonData
            ];
            unset($params['info']);
        }
        if (!empty($params) && is_array($params)) {
            $jsonData = array_merge($jsonData, $params);
        }
        Log::record('Request End >>' . print_r($jsonData, true));

        $jsonData = json_encode($jsonData);

        $url = API_URL . $service;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: applicaqtion/json; charset=utf-8',
            'Content-Length: ' . strlen($jsonData)
        ]);
        $response = curl_exec($ch);

        if (curl_errno($ch) > 0) {
            $curlError = curl_errno($ch) . ': ' . curl_error($ch);
            curl_close($ch);
            $response = [
                'status' => 'fail',
                'msg' => trim($curlError)
            ];
            Log::record('Response >>' . print_r($response, true));
            return $response;
        }

        $response = json_decode($response, true);
        curl_close($ch);

        if (trim($response['status']) != 'ok') {
            $response = [
                'status' => 'fail',
                'msg' => trim($response['status'])
            ];
            Log::record('Response >>' . print_r($response, true));
            return $response;
        }

        $response = [
            'status' => 'success',
            'data' => $response
        ];
        Log::record('Response >>' . print_r($response, true));
        return $response;
    }
}