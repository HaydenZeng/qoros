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
    const  TYPE_FINISH_SHARE = 'share';

    const TEMPLATE_LUCKY_DRAW = 'xA0zgdKmPFlMQHOXZIm_d5eDejK0U2-po7knuB6CVkM';
    const TEMPLATE_FINISH_SHARE = '5TW43udgbwFURcddwWECWmGFO4n2lgUiG0cmihXEirs';

//    const TEMPLATE_LUCKY_DRAW = 'oknrrK8hBEGhu6EsgJFvcwmAllthusyqfX9xJOXtQJc';
//    const TEMPLATE_FINISH_SHARE = '_nqcKbUHEg0QGpmLtoXER24rB4q9YUILUm5ukzV9yM8';

}
