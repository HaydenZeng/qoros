<?php

namespace Site\Entity;

use Common\Entity\Entity;

class VerifyCodeEntity extends Entity{
    //有效时间1800秒，30分钟
    const CODE_DURATION = 1800;
    public $code;
    public $duration;
    public $sendTo;
    public $startTime;
    
    public static function generateCode($sendTo) {
        $verifyCodeEntity = new VerifyCodeEntity();
        $code = rand(1000, 9999);
        $verifyCodeEntity->code = $code;
        $verifyCodeEntity->duration = VerifyCodeEntity::CODE_DURATION;
        $verifyCodeEntity->sendTo = $sendTo;
        $verifyCodeEntity->startTime = time();
        
        return $verifyCodeEntity;
    }
}