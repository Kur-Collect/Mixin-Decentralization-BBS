<?php
/**
 * Created by PhpStorm.
 * User: kurisu
 * Date: 18-12-16
 * Time: 下午5:20
 */

if (!function_exists('fetchMixinSDk')) {
    function fetchMixinSDk(): \ExinOne\MixinSDK\MixinClient
    {
        $rawKeys = Storage::get('MixinMatchBBS.keys');
        $keysArr = explode("\n", $rawKeys, 7);
        $keys    = array_combine(['mixin_id', 'client_id', 'client_secret', 'pin', 'pin_token', 'session_id', 'private_key'], $keysArr);
        return \ExinOne\MixinSDK\Facades\MixinSDK::use('default', getRobotConfig());
    }
}


if (!function_exists('getRobotConfig')) {
    function getRobotConfig()
    {
        $cacheKey = 'MixinMatchBBS.config';
        $keyFile  = 'MixinMatchBBS.keys';
        if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
            return \Illuminate\Support\Facades\Cache::get($cacheKey);
        } else {
            $rawKeys = Storage::get($keyFile);
            $keysArr = explode("\n", $rawKeys, 7);
            $keys    = array_combine(['mixin_id', 'client_id', 'client_secret', 'pin', 'pin_token', 'session_id', 'private_key'], $keysArr);
            return $keys;
        }
    }
}