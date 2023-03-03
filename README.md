# 基于Hyperf框架开发的接口项目

一个基于hyperf的框架开发的项目  
1.用户登录、注销  
2.创建、编辑供应、求购  
3.图片上传  
4.在线聊天  
5.问题反馈  

# 需要的扩展
 - PHP >= 8.0
 - Swoole PHP extension >= 4.5，禁用 `Short Name`
 - OpenSSL PHP extension
 - JSON PHP extension
 - PDO PHP extension
 - Redis PHP extension
 - Mongodb PHP extension

# 部署方式

- ## docker-compose部署
-   #### 因为没有vendor文件夹、所以需要创建一个容器来手动composer install
1. git clone git@github.com:daninedd/nursery2.git
2. docker run --name hyperf \
  -v /home/nursery2:/data/project \
  -p 9501:9501 -it \
  --privileged -u root \
  --entrypoint /bin/sh \
  hyperf/hyperf:8.0-alpine-v3.15-swoole

- #### 官方的镜像默认没有安装mongodb的扩展，所以我们需要自己装mongodb的扩展才能composer install
3. RUN apk add --no-cache $PHPIZE_DEPS
4. pecl8 install mongodb
5. composer install

6. 项目里出现vendor文件夹，然后退出、删除刚刚创建的容器

### 部署全部项目

`docker compose build` #拷贝.env.example 为.env 修改为自己的配置  
`docker compose up`
进入api容器执行 php bin/hyperf migrate --seed

