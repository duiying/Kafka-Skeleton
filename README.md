<h1 align="center">
    Kafka-Skeleton
</h1>

<p align="center">基于 Lumen 框架的 Kafka 消费脚手架</p>  

`Producer`：  

- Rsyslog

`Consumer`：  

- 基于 Lumen 框架的 Kafka 消费脚本

**如何安装？**  

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






