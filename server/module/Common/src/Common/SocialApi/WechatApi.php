<?php
namespace Common\SocialApi;

require_once(ROOT . "/public/libweibo/saetv2.ex.class.php");

use Common\SocialApi\SocialLoginInterface;
use User\Entity\UserOauthEntity;
use Common\HttpApi\HttpJsonApi;
use Common\Exception\BusinessException;

class WechatApi extends HttpJsonApi implements SocialLoginInterface{
    
    const VENDER_NAME = 'Wechat';
    const BASE_URL = 'https://api.weixin.qq.com/sns';
    
    private $appId;
    private $appSecret;
    private $code;
    
    
    public function __construct($config) {
        $this->appId = $config['AppID'];
        $this->appSecret = $config['AppSecret'];
    }
    
	/* (non-PHPdoc)
     * @see \Common\SocialApi\SocialLoginInterface::login()
     */
    public function login()
    {
        $userOauth = $this->getAccessToken();
        $userData = $this->getUserInfo($userOauth->accessToken, $userOauth->oauthId);
        $accessDatas = $userOauth->getArrayCopy();
        $userData = array_merge($accessDatas, $userData);
        return $this->generateUserOauthEntity($userData);
    }
    
	/* (non-PHPdoc)
     * @see \Common\SocialApi\SocialLoginInterface::generateUserOauthEntity()
     */
    public function generateUserOauthEntity($datas)
    {
        $userOauth = new UserOauthEntity();
        $userOauth->accessToken = $datas['accessToken'];
        $userOauth->vender = self::VENDER_NAME;
        $userOauth->oauthId = $datas['openid'];
        $userOauth->venderAccount = $datas['nickname'];
        $userOauth->unionid = isset($datas['unionid']) ? $datas['unionid'] : null;
        return $userOauth;
    }
    
    public function getUserInfo($accessToken, $openId) {
        $data = array(
            'access_token' => $accessToken,
            'openid' => $openId,
        );
        $result = $this->get(self::BASE_URL . '/userinfo', $data, array('sslverifypeer' => false));
        if (isset($result['errcode'])) {
            //有错误
            throw new BusinessException("获取微信用户信息失败". $result['errmsg']);
        }
        return $result;
    }
    
    /**
     * @throws BusinessException
     * @return \User\Entity\UserOauthEntity
     */
    public function getAccessToken() {
        $data = array(
            'appid' => $this->appId,
            'secret' => $this->appSecret,
            'code' => $this->code,
            'grant_type' => 'authorization_code'
        );
        $result = $this->get(self::BASE_URL . '/oauth2/access_token', $data, array('sslverifypeer' => false));
        if (isset($result['errcode'])) {
            //有错误
            throw new BusinessException("微信认证失败" . $result['errmsg']);
        }
        $userOauth = new UserOauthEntity();
        $userOauth->accessToken = $result['access_token'];
        $userOauth->oauthId = $result['openid'];
        return $userOauth;
    }

	/**
     * @param field_type $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }
}

?>