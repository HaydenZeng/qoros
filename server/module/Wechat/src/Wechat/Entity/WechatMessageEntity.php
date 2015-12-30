<?php
namespace Wechat\Entity;

use Doctrine\ORM\Mapping as ORM;
use Common\Entity\Entity;

/**
 * Class WechatMessageEntity
 * @package Wechat\Entity
 */
class WechatMessageEntity extends Entity
{
    const ALIAS = 'wcmessage';

    const  TYPE_LUCKY_DRAW = 'lucky_draw';

    const TEMPLATE_LUCKY_DRAW = 'xA0zgdKmPFlMQHOXZIm_d5eDejK0U2-po7knuB6CVkM';
}
