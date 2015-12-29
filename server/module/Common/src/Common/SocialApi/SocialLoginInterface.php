<?php
namespace Common\SocialApi;

use User\Entity\UserOauthEntity;
interface SocialLoginInterface
{
    /**
     * @return Usero
     */
    public function login();
    /**
     * @param unknown $datas
     * @return UserOauthEntity
     */
    public function generateUserOauthEntity($datas);
}

?>