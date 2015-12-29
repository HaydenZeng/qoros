<?php
namespace Common\SocialApi;

require_once(ROOT . "/public/libweibo/saetv2.ex.class.php");

use Common\SocialApi\SocialLoginInterface;
use User\Entity\UserOauthEntity;

class SinaWeiboApi implements SocialLoginInterface{
    
    const VENDER_NAME = 'SinaWeibo';
    private $appKey;
    private $appSecret;
    private $callbackUrl;
    /**
     * @var \SaeTOAuthV2
     */
    protected $saeOauth;
    /**
     * @var \SaeTClientV2
     */
    protected $saeClient;
    
    public function __construct($config) {
        if (DW_ENV_DEBUG > 0) {
            $config = $config['test'];
        }else {
            $config = $config['prod'];
        }
        $this->appKey = $config['WB_AKEY'];
        $this->appSecret = $config['WB_SKEY'];
        $this->callbackUrl = $config['WB_CALLBACK_URL'];
    }
    
	/* (non-PHPdoc)
     * @see \Common\SocialApi\SocialLoginInterface::login()
     */
    public function login()
    {
        $access = $this->getSaeOauth()->getTokenFromJSSDK();
        $user_message = $this->getSaeClient()->show_user_by_id($access['uid']);
        $result = $this->generateUserOauthEntity($user_message);
        return $result;
    }

	/* (non-PHPdoc)
     * @see \Common\SocialApi\SocialLoginInterface::generateUserOauthEntity()
     */
    public function generateUserOauthEntity($datas)
    {
        $userOauth = new UserOauthEntity();
        $access = $this->getSaeOauth()->getTokenFromJSSDK();
        $userOauth->vender = self::VENDER_NAME;
        $userOauth->oauthId = $access['uid'];
        $userOauth->venderAccount = $datas['name'];
        $userOauth->accessToken = $access['access_token'];
        return $userOauth;
    }
    
	/**
     * @return the $saeOauth
     */
    public function getSaeOauth()
    {
        if (!isset($this->saeOauth)) {
            $this->saeOauth = new \SaeTOAuthV2($this->appKey, $this->appSecret);
        }
        return $this->saeOauth;
    }

	/**
     * @return the $saeClient
     */
    public function getSaeClient()
    {
        if (!isset($this->saeClient)) {
            $access = $this->getSaeOauth()->getTokenFromJSSDK();
            $this->saeClient = new \SaeTClientV2($this->appKey, $this->appSecret, $access['access_token'] );
        }
        return $this->saeClient;
    }
}

?>