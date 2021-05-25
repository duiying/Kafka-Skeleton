<?php

namespace App\Util;

class Util
{
    use Singleton;

    /**
     * 根据 Kafka 配置获取消费者
     *
     * @param string $groupId
     * @param string $brokerList
     * @param string $topicName
     * @return \RdKafka\KafkaConsumer
     * @throws \Exception
     */
    public function getKafkaConsumer($groupId = '', $brokerList = '', $topicName = '')
    {
        $conf = new \RdKafka\Conf();
        $conf->set('group.id', $groupId);
        $conf->set('metadata.broker.list', $brokerList);

        // 每次 poll 数据前会先检查上次提交位点的时间，如果距离当前时间已经超过参数 auto.commit.interval.ms 规定的时长，则客户端会启动位点提交动作
        $conf->set('enable.auto.commit', 'true');
        $conf->set('auto.offset.reset', 'earliest');
        $conf->set('auto.commit.interval.ms', 1000);

        $conf->setRebalanceCb(function(\RdKafka\KafkaConsumer $kafka, $err, array $partitions = null) {
            switch ($err) {
                case RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS:
                    $kafka->assign($partitions);
                    break;
                case RD_KAFKA_RESP_ERR__REVOKE_PARTITIONS:
                    $kafka->assign(null);
                    break;
                default:
                    throw new \Exception($err);
            }
        });

        $consumer = new \RdKafka\KafkaConsumer($conf);
        $consumer->subscribe([$topicName]);
        return $consumer;
    }
}
