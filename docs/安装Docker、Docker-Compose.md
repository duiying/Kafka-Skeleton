# 安装 Docker、Docker-Compose

```bash
# 安装 Docker 和 Docker-Compose
yum -y install epel-release
yum -y install docker docker-compose

# 启动 Docker 服务
service docker start

# 配置阿里云 Docker 镜像加速器（建议配置加速器, 可以提升 Docker 拉取镜像的速度）
mkdir -p /etc/docker
vim /etc/docker/daemon.json

# 新增下面内容
{
"registry-mirrors": ["https://8auvmfwy.mirror.aliyuncs.com"]
}

# 重新加载配置、重启 Docker
systemctl daemon-reload
systemctl restart docker 
```

