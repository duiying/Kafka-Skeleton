<?php

namespace App\Util;

use App\Constant\KafkaConstant;
use Illuminate\Support\Facades\Log;

/**
 * Kafka 工具类
 *
 * @package App\Util
 */
class KafkaUtil
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

    /**
     * 消息写入 Kafka
     * 并非直接由程序写入 Kafka 中，实际写入 Kafka 的流程为：1、由程序写入本地磁盘文件；2、通过 Rsyslog 收集磁盘中的日志数据到 Kafka 中。
     *
     * @param string $topicName
     * @param array $data
     * @return bool
     */
    public function sendToKafka($topicName = '', $data = [])
    {
        if (empty($topicName || empty($data))) {
            Log::error(sprintf('send to kafka error，topicName：%s，data：%s', $topicName, json_encode($data)));
            return false;
        }

        // 数据中默认加上时间戳 & 格式化时间
        !isset($data['kafka_timestamp'])    && $data['kafka_timestamp'] = time();
        !isset($data['kafka_format_time'])  && $data['kafka_format_time'] = date('Y-m-d H:i:s');

        $path = "/data/logs/$topicName/";
        !is_dir('/data') && mkdir('/data');
        !is_dir('/data/logs') && mkdir('/data/logs');
        !is_dir($path) && mkdir($path);

        $file = $path . date('YmdH') . '.log';
        $sendRes = file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND | LOCK_EX);
        if ($sendRes === false) {
            Log::error(sprintf('send to kafka failed，topicName：%s，data：%s', $topicName, json_encode($data)));
            return false;
        }

        return true;
    }

    /**
     * 备份数据，只保留当天的日志
     *
     * @return bool
     */
    public static function bakKafkaLog()
    {
        $topicList = KafkaConstant::TOPIC_LIST;

        if (!is_dir('/data')) {
            return false;
        }
        if (!is_dir('/data/logs')) {
            return false;
        }

        foreach ($topicList as $topicName) {
            $dirName = "/data/logs/$topicName";
            if (!is_dir($dirName)) {
                continue;
            }

            $shouldBakFileList = self::getDirFileList($dirName);
            if (empty($shouldBakFileList)) {
                continue;
            }

            // 移动文件到备份目录下
            foreach ($shouldBakFileList as $shouBakFile) {
                !is_dir('/data/logs/bak') && mkdir('/data/logs/bak');
                !is_dir("/data/logs/bak/$topicName") && mkdir("/data/logs/bak/$topicName");
                exec("mv $shouBakFile /data/logs/bak/$topicName/");
            }
        }

        return true;
    }

    /**
     * 获取一个文件夹下所有应该被清理的文件
     *
     * @param $dir
     * @return array
     */
    public static function getDirFileList($dir)
    {
        $dir = rtrim($dir, '/');

        // 目录下的所有文件
        $fileList = [];
        if ($handle = opendir($dir)) {
            while (($file = readdir($handle)) !== false) {
                // 排除文件夹
                if (is_dir($dir . '/' . $file)) {
                    continue;
                }
                // 排除 . 和 ..
                if ($file != '..' && $file != '.') {
                    $fileList[] = $file;
                }
            }
            closedir($handle);
        }

        if (empty($fileList)) {
            return [];
        }

        // 应该备份的文件
        $shouldBakFileList = [];
        foreach ($fileList as $k => $v) {
            $ymdInfoList = explode('.', $v);
            if (isset($ymdInfoList[0]) && intval($ymdInfoList[0]) == $ymdInfoList[0] && strlen((string)$ymdInfoList[0]) === 10) {
                // 如果不是今天的日志，那么该日志文件应该备份到其他目录
                if (date('Ymd') !== date('Ymd', strtotime('YmdH', intval($ymdInfoList[0])))) {
                    $shouldBakFileList[] = $dir . '/' . $v;
                }
            }
        }

        return $shouldBakFileList;
    }
}
