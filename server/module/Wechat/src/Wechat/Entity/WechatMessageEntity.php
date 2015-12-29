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

    const  TYPE_BIND = 'bind';
    const  TYPE_NORMAL = 'normal';
    const  TYPE_TO_SEND = 'send';
    const  TYPE_TO_RECEIVE = 'receive';


    const TEMPLATE_BIND_SUCCESS = '3bZ2c6_CfH5BmowRUAsuxDkQ0rQJVoi0RFpZHrqD6KI';
    const TEMPLATE_BIND = 'uV_F4SJQbbhciel-6_owFBaYv9UCNBlt4rMeZReThjQ';
    const TEMPLATE_NORMAL = 'uV_F4SJQbbhciel-6_owFBaYv9UCNBlt4rMeZReThjQ';
    const TEMPLATE_SEND = 'RrUQXfxlhcyxW5YG-Cxe47YOzRY1ww1ymSz1hxgy71Q';
}
