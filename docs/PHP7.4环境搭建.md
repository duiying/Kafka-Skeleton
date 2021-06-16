# PHP7.4 环境搭建

安装基础的软件环境：  

```bash
yum -y install net-tools
yum -y install vim
yum -y install wget
yum -y install autoconf
yum -y install gcc gcc-c++ make
yum -y install git
yum install -y zlib zlib-devel
yum -y install libxml2 libxml2-devel
yum -y install openssl openssl-devel
yum -y install sqlite-devel
yum -y install libcurl libcurl-devel
yum -y install gd
yum -y install libpng libpng-devel
yum -y install libjpeg libjpeg-devel libjpeg-turbo libjpeg-turbo-devel
yum -y install freetype freetype-devel
yum -y install oniguruma oniguruma-devel
yum -y install libuuid libuuid-devel
yum -y install https://rpms.remirepo.net/enterprise/7/remi/x86_64/oniguruma5php-6.9.6-1.el7.remi.x86_64.rpm
yum -y install https://rpms.remirepo.net/enterprise/7/remi/x86_64/oniguruma5php-devel-6.9.6-1.el7.remi.x86_64.rpm
```

准备一个 lib 目录，存放下载的软件包，再准备一个 php-extensions 目录，作为一些软件的安装目录：   

```bash
[work@VM-0-9-centos ~]$ mkdir lib
[work@VM-0-9-centos ~]$ mkdir php-extensions
[work@VM-0-9-centos ~]$ ls
lib  php-extensions
```

安装 PHP7.4 及基本扩展：  

```bash
[work@VM-0-9-centos ~]$ cd /home/work/lib/
[work@VM-0-9-centos lib]$ wget https://www.php.net/distributions/php-7.4.20.tar.gz && tar -xvf php-7.4.20.tar.gz php-7.4.20/ && cd php-7.4.20/
[work@VM-0-9-centos php-8.0.3]$ ./configure --prefix=/home/work/service/php74 --with-config-file-path=/home/work/service/php74/etc --with-config-file-scan-dir=/home/work/service/php74/etc/ext --with-fpm-user=work --with-fpm-group=work --disable-debug --disable-ipv6 --disable-rpath --enable-bcmath --enable-exif --enable-mysqlnd --enable-ftp --enable-mbregex --enable-pcntl --enable-xml --enable-mbstring --enable-sockets --enable-dom --enable-shmop --enable-sysvsem --enable-soap --enable-fpm --enable-tokenizer --with-mhash --with-pdo-mysql=mysqlnd --with-mysqli=mysqlnd --with-pear --with-curl --with-openssl --with-zlib --enable-gd --with-jpeg --with-freetype
[work@VM-0-9-centos php-8.0.3]$ make && make install
```

php.ini 配置：  

```bash
# 先查看一下 ini 文件应该放到哪个目录下
[work@VM-0-9-centos etc]$ /home/work/service/php74/bin/php -r "phpinfo();" | grep 'php.ini'
Configuration File (php.ini) Path => /home/work/service/php74/etc
# 拷贝一份 ini 配置文件
[work@VM-0-9-centos etc]$ cp /home/work/lib/php-7.4.20/php.ini-production /home/work/service/php74/etc/php.ini
# 此时可以看到 ini 配置文件已经生效
[work@VM-0-9-centos etc]$ /home/work/service/php74/bin/php --ini
Configuration File (php.ini) Path: /home/work/service/php74/etc
Loaded Configuration File:         /home/work/service/php74/etc/php.ini
Scan for additional .ini files in: /home/work/service/php74/etc/ext
Additional .ini files parsed:      (none)
```

将 PHP 命令配置到环境变量中：  

```bash
vim /etc/profile

# 最底部追加下面两行内容
PATH=$PATH:/home/work/service/php74/bin
export PATH

# 使环境变量生效
source /etc/profile
```

安装 Redis 扩展：  

```bash
cd /home/work/lib/
wget https://pecl.php.net/get/redis-5.3.4.tgz && tar -xvf redis-5.3.4.tgz && cd /home/work/lib/redis-5.3.4/
phpize
./configure --with-php-config=/home/work/service/php74/bin/php-config
make && make install
sed -i '$a \\nextension=redis.so' /home/work/service/php74/etc/php.ini
```

安装 Kafka 扩展：  

```bash
# 先安装 librdkafka
[work@VM-0-9-centos uuid-1.2.0]$ cd /home/work/lib/
[work@VM-0-9-centos lib]$ wget https://github.com/edenhill/librdkafka/archive/refs/tags/v1.6.1.tar.gz
[work@VM-0-9-centos lib]$ tar -xvf v1.6.1.tar.gz && cd librdkafka-1.6.1/
[work@VM-0-9-centos librdkafka-1.6.1]$ ./configure --prefix=/home/work/php-extensions/librdkafka
[work@VM-0-9-centos librdkafka-1.6.1]$ make && make install

# 再安装 rdkafka 扩展
[work@VM-0-9-centos librdkafka-1.6.1]$ cd /home/work/lib/
[work@VM-0-9-centos lib]$ wget https://github.com/arnaud-lb/php-rdkafka/archive/refs/tags/5.0.0.tar.gz
[work@VM-0-9-centos lib]$ tar -xvf 5.0.0.tar.gz && cd /home/work/lib/php-rdkafka-5.0.0
[work@VM-0-9-centos php-rdkafka-5.0.0]$ /home/work/service/php74/bin/phpize
[work@VM-0-9-centos php-rdkafka-5.0.0]$ ./configure --with-php-config=/home/work/service/php74/bin/php-config --with-rdkafka=/home/work/php-extensions/librdkafka
[work@VM-0-9-centos php-rdkafka-5.0.0]$ make && make install
[work@VM-0-9-centos php-rdkafka-5.0.0]$ sed -i '$a \\nextension=rdkafka.so' /home/work/service/php74/etc/php.ini
```

最后，通过 `php -m` 查看是否成功安装了 Redis、Kafka 扩展。  

> Composer 环境安装这里不再赘述。  







