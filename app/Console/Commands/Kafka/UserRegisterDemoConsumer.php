<?php

namespace App\Console\Commands\Kafka;

use App\Constant\KafkaConstant;
use App\Util\KafkaUtil;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Kafka 用户注册消费者脚本（Demo）
 *
 * @package App\Console\Commands\Kafka
 */
class UserRegisterDemoConsumer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kafka:user:register:consumer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kafka 用户注册消费者脚本（Demo）';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $topicName  = KafkaConstant::TOPIC_USER_REGISTER;
        $groupId    = $this->signature;
        $brokerList = env('KAFKA_BROKER_LIST');

        $consumer = KafkaUtil::getInstance()->getKafkaConsumer($groupId, $brokerList, $topicName);

        while (true) {
            // 60 秒内有数据就消费
            $msg = $consumer->consume(60 * 1000);

            if (empty($msg)) {
                Log::error("$topicName topic msg is null");
                continue;
            }

            switch ($msg->err) {
                case \RD_KAFKA_RESP_ERR_NO_ERROR:
                    $payload = json_decode($msg->payload, true);
                    print_r($payload);
                    break;
                case \RD_KAFKA_RESP_ERR__PARTITION_EOF:
                    Log::error("$topicName topic waiting to receive msg");
                    break;
                case \RD_KAFKA_RESP_ERR__TIMED_OUT:
                    Log::error("$topicName topic timed out");
                    break;
                default:
                    Log::error("Kafka error {$msg->err}：{$msg->errstr()}" );;
                    break;
            }
        }
    }
}
