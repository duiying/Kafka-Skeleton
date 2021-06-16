# Rsyslog 相关配置

- 关闭 selinux，重启系统：`vim /etc/selinux/config`，将 SELINUX 值更改为 disabled
- 拷贝 Rsyslog 配置文件到指定目录：`cp Kafka-Skeleton/rsyslog/* /etc/rsyslog.d/`
- 安装 rsyslog-kafka：`yum -y install rsyslog-kafka`
- 每次改完 Rsyslog，需要重启 Rsyslog：`systemctl restart rsyslog`
