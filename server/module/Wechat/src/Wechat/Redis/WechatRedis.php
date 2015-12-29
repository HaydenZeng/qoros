<?php
namespace Wechat\Redis;

use Common\Redis\Redis;

class WechatRedis extends Redis
{
    public function saveAccessToken($token, $expiresIn)
    {
        $this->module('wechat')->logic('accessToken')->setex($expiresIn, $token);
    }
    
    public function getAccessToken()
    {
        return $this->module('wechat')->logic('accessToken')->get();
    }
    
    /**
     * wechat util methods / setCache
     */ 
    public function setCache($cachename, $value, $expired = 0)
    {
        return $this->module('wechat')->logic('util-cache')->logic($cachename)->setex($expired, $value);
    }
    
    /**
     * wechat util methods / getCache
     */
    public function getCache($cachename)
    {
        return $this->module('wechat')->logic('util-cache')->logic($cachename)->get();
    }
    
    /**
     * wechat util methods / removeCache
     */
    public function removeCache($cachename)
    {
        return $this->module('wechat')->logic('util-cache')->logic($cachename)->del();
    }

    public function getUserOperation($userId){
        return $this->module('wechat')->logic('user',$userId)->logic('operation')->get();
    }

    public function setUserOperation($userId,$op){
        $expiresIn = 60*60*24;//用户操作一天后过期
        return $this->module('wechat')->logic('user',$userId)->logic('operation')->setex($expiresIn, $op);
    }

    /**
     * 主动清除用户操作记录
     */
    public function clearUserOpeartion($userId){
        return $this->module('wechat')->logic('user',$userId)->logic('operation')->del();
    }


    public function setUserSelectData($userId,$field ,$data){
        return $this->module('wechat')->logic('user',$userId)->logic('selectData')->hset($field, $data);
    }

    public function getUserSelectData($userId,$field){
        return $this->module('wechat')->logic('user',$userId)->logic('selectData')->hget($field);
    }
}