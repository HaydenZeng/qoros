<?php

return array(
    'wechat' => array(
        'url' => 'http://qoros.jiubei.me/wechat',
        'token' => 'qoros',
        'EncodingAESKey' => 'hBNVdWU5i56ZqcsH38bwRrDDdALsljavwixhIILDXRi',
        'appid' => 'wx95ab641d29e1e251',
        'secret' => 'a3eb94bd006dc9122601824262ffcebf',
        // 菜单配置
        'menu' => array(
            array('name' => '车型展示', 'type' => 'view',  'sub_button' => array(
                array('name' => '观致逸云', 'type' => 'view', 'url' => 'http://www.qoros.com/qoros_qloud/?utm_medium=wechatr'),
                array('name' => '观致3轿车', 'type' => 'view', 'url' => 'http://www.qoros.com/qoros_3_sedan/?utm_medium=wechat'),
                array('name' => '观致3五门版', 'type' => 'view', 'url' => 'http://www.qoros.com/qoros_3_hatch/?utm_medium=wechat'),
                array('name' => '观致3都市SUV', 'type' => 'view', 'url' => 'http://www.qoros.com/qoros_3_city_suv/?utm_medium=wechat'),
                array('name' => '观致5 SUV', 'type' => 'view', 'url' => 'http://www.qoros.com/qoros_5_suv/?utm_source=officialqorosauto&utm_medium=wechat'),
            )),
            array('name' => '素朴生活', 'sub_button' => array(
                array('name' => '保险计划', 'type' => 'view', 'url' => 'http://www.qoros.com/insurance/?utm_medium=wechat'),
                array('name' => '观致超惠车贷专案', 'type' => 'view', 'url' => 'http://www.qoros.com/financing/?utm_medium=wechat'),
                array('name' => '查找经销商', 'type' => 'view', 'url' => 'http://www.qoros.com/dealers/?utm_medium=wechat'),
                array('name' => '预约试驾', 'type' => 'view', 'url' => 'http://www.qoros.com/test_drive/?utm_medium=wechat'),
            )),
            array('name' => '最新活动', 'sub_button' => array(
                array('name' => '观致5-暖心计划', 'type' => 'view', 'url' => 'http://qwechat.chinacloudapp
                .cn/qoros/user/register'),
                array('name' => '观致5-任性计划', 'type' => 'view', 'url' => 'http://qoroswechat.chinacloudapp.cn/frontend/web/index.php?r=site/order'),
            )),
        ),
    ),


);