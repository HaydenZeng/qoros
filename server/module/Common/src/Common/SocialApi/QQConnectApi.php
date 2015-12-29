<?php
namespace Common\SocialApi;

use Common\SocialApi\SocialLoginInterface;
use Common\HttpApi\HttpJsonApi;
use User\Entity\UserOauthEntity;
use Common\Exception\BusinessException;
use Zend\Json\Json;

class QQConnectApi extends HttpJsonApi implements SocialLoginInterface{
    
    const VENDER_NAME = 'QQConnect';
    const BASE_URL = 'https://graph.qq.com';
    
    private $appId;
    private $appKey;
    private $callbackUrl;
    private $code;
    private $accessToken;
    private $openId;
    
    public function __construct($config) {
        if (DW_ENV_DEBUG > 0) {
            $config = $config['test'];
        }else {
            $config = $config['prod'];
        }
        $this->appId = $config['APP_ID'];
        $this->appKey = $config['APP_KEY'];
        $this->callbackUrl = $config['CALLBACK_URL'];
    }
    
	/* (non-PHPdoc)
     * @see \Common\SocialApi\SocialLoginInterface::login()
     */
    public function login()
    {
        $this->getAccessToken();
        $this->getOpenId();
        $userData = $this->getUserInfo();
        return $this->generateUserOauthEntity($userData);
    }
    
	/* (non-PHPdoc)
     * @see \Common\SocialApi\SocialLoginInterface::generateUserOauthEntity()
     */
    public function generateUserOauthEntity($datas)
    {
        $userOauth = new UserOauthEntity();
        $userOauth->accessToken = $this->accessToken;
        $userOauth->vender = self::VENDER_NAME;
        $userOauth->oauthId = $this->openId;
        $userOauth->venderAccount = $datas['nickname'];
        return $userOauth;
    }

    public function getAccessToken() {
        $data = array(
            'grant_type' => 'authorization_code',
            'client_id' => $this->appId,
            'client_secret' => $this->appKey,
            'code' => $this->code,
            'redirect_uri' => $this->callbackUrl
        );
        $result = $this->get(self::BASE_URL . '/oauth2.0/token', $data, array('sslverifypeer' => false));
        $array = array();
        $arr = explode('&', $result);
        foreach($arr as $a) {
            $new = explode('=', $a);
            if(count($new) > 1){
                $array[$new[0]] = $new[1];
            }
        }
        echo Json::encode($array);
        if (empty($array['access_token'])) {
            throw new BusinessException("QQ获取AccessToken失败", 1010);
        }

        $this->accessToken = $array['access_token'];
    }

    public function getOpenId() {
        $data = array(
            'access_token' => $this->accessToken
        );
        $result = $this->get(self::BASE_URL . '/oauth2.0/me', $data, array('sslverifypeer' => false));
        $array = array();
        $arr1 = explode('{', $result);
        $arr2 = explode('}', $arr1[1]);
        $arr3 = explode(',', $arr2[0]);
        foreach($arr3 as $a) {
            $new = explode(':', $a);
            if(count($new) > 1){
                $array[substr($new[0], 1, -1)] = substr($new[1], 1, -1);
            }
        }
        echo Json::encode($array);
        if (empty($array['openid'])) {
            throw new BusinessException("QQ获取OpenId失败", 1010);
        }

        $this->openId = $array['openid'];
    }
    
    public function getUserInfo() {
        $data = array(
            'access_token' => $this->accessToken,
            'oauth_consumer_key' => $this->appId,
            'openid' => $this->openId
        );
        $result = $this->get(self::BASE_URL . '/user/get_user_info', $data, array('sslverifypeer' => false));
        echo Json::encode($result);
        if ($result['ret'] < 0) {
            //有错误
            throw new BusinessException("获取QQ用户信息失败". $result['msg']);
        }
        return $result;
    }

    /**
     * @param field_type $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }
	/**
     * @param field_type $accessToken
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

	/**
     * @param field_type $openId
     */
    public function setOpenId($openId)
    {
        $this->openId = $openId;
    }
}

?>