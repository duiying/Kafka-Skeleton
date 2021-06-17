<?php

namespace App\Constant;

class KafkaConstant
{
    const TOPIC_CANAL               = 'canal';
    const TOPIC_USER_REGISTER       = 'user-register';

    // Topic 列表
    const TOPIC_LIST = [
        self::TOPIC_CANAL,
        self::TOPIC_USER_REGISTER,
    ];
}
