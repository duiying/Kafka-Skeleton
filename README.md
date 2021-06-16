<h1 align="center">
    Kafka-Skeleton
</h1>

<p align="center">基于 Lumen 框架的 Kafka 消费脚手架</p>  

`Producer`：  

- Rsyslog

`Consumer`：  

- 基于 Lumen 框架的 Kafka 消费脚本

**1、项目背景**  

业务方产生的数据需要被多个下游服务消费，工作原理如下：  

<div align=center><img src="https://raw.githubusercontent.com/duiying/Kafka-Skeleton/master/docs/原理图.png" width="400"></div>  

以**用户注册**场景为例，一个用户注册成功，此时由「业务方」将该记录写入磁盘文件，再由 Rsyslog 将磁盘上的该记录写入到 Kafka 指定 Topic 中，此时不同的消费服务可以去订阅该 Topic 来进行消费，比如「优惠券部门」、「审核部门」等等，实现了不同部门之间的解耦合。

**2、如何安装**  

1、下载并安装项目。  

```sh
git clone https://github.com/duiying/Kafka-Skeleton
chmod -R 777 Kafka-Skeleton
cd Kafka-Skeleton
cp .env.example .env
composer install
```

2、准备 PHP7.4 基础环境，需要 PHP7.4 CLI（暂不需要 PHP-FPM）、Composer、Redis 扩展、Kafka 扩展。  

参考：[PHP7.4 环境搭建](docs/PHP7.4环境搭建.md)。  

3、通过 Docker-Compose 搭建基础服务（基础服务包括：Kafka、MySQL、Redis）。   

参考： [通过 Docker-Compose 搭建基础服务](docs/通过Docker-Compose搭建基础服务.md)

4、创建相关 Topic。    

进入任意一个 Kafka 容器，创建名为 user-register 的 Topic：  

```sh
docker exec -it kafka1 bash
/opt/kafka_2.13-2.7.0/bin/kafka-topics.sh --create --topic user-register --partitions 5 --zookeeper zoo1:2181 --replication-factor 3
```

5、Rsyslog 相关配置，参考：[Rsyslog 相关配置](docs/Rsyslog相关配置.md)。

6、启动生产者和消费者，发现可以正常消费了。  

```bash
php artisan kafka:user:register:producer
php artisan kafka:user:register:consumer
```

**3、生产者核心代码**  

`Kafka-Skeleton/app/Util/KafkaUtil.php`：  

```php
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
```




