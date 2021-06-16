<?php

namespace App\Console\Commands\Kafka;

use App\Constant\KafkaConstant;
use App\Util\KafkaUtil;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Kafka 用户注册生产者脚本（Demo）
 * 每次执行会模拟「一次用户注册成功事件」，并将用户数据写入 Kafka
 *
 * @package App\Console\Commands\Kafka
 */
class UserRegisterDemoProducer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kafka:user:register:producer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kafka 用户注册生产者脚本（Demo）';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $topicName = KafkaConstant::TOPIC_USER_REGISTER;
        $data = [
            'account_id'    => time(),
            'nickname'      => substr(md5(time() . uniqid()), 0, 10),
        ];
        $sendToKafkaRes = KafkaUtil::getInstance()->sendToKafka($topicName, $data);
        if ($sendToKafkaRes === false) {
            Log::error(sprintf('send to kafka failed，topicName：%s，data：%s', $topicName, json_encode($data)));
        } else {
            echo sprintf('send to kafka success，topicName：%s，data：%s', $topicName, json_encode($data)) . PHP_EOL;
        }
    }
}
