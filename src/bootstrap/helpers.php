<?php
/**
 * Created by PhpStorm.
 * User: kurisu
 * Date: 18-12-16
 * Time: 下午5:20
 */

if (!function_exists('fetchMixinSDk')) {
    /**
     * @return \ExinOne\MixinSDK\MixinClient
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    function fetchMixinSDk(): \ExinOne\MixinSDK\MixinClient
    {
        return \ExinOne\MixinSDK\Facades\MixinSDK::use('default', getRobotConfig());
    }
}


if (!function_exists('getRobotConfig')) {
    /**
     * @return array|false|mixed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
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

if (!function_exists('uuid2Bytes2Base64')) {
    /**
     * @param string $uuid
     *
     * @return string
     */
    function uuid2Bytes2Base64(string $uuid)
    {
        return base64_encode(\Ramsey\Uuid\Uuid::fromString($uuid)->getBytes());
    }
}