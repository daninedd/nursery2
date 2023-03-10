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
2. docker run --name hyperf \\  
  -v {宿主机的项目地址}:/data/project \\  
  -p 9501:9501 -it \\  
  --privileged -u root \\  
  --entrypoint /bin/sh \\  
  hyperf/hyperf:8.0-alpine-v3.15-swoole

- #### 官方的镜像默认没有安装mongodb的扩展，所以我们需要自己装mongodb的扩展才能composer install
- `sed -i 's/dl-cdn.alpinelinux.org/mirrors.ustc.edu.cn/g' /etc/apk/repositories`  
3. `apk add --no-cache $PHPIZE_DEPS`
4. `pecl8 install mongodb` # 然后extension=mongodb.so 写入到/etc/php8/php.ini里  
5. 在/data/project目录下执行 `composer install`

6. 项目里出现vendor文件夹，然后退出、删除刚刚创建的容器
7. 修改vendor/phper666/jwt-auth/src/Command/JWTCommand.php 第20 行 ,$name 前加上类型判断 ?string, 修改后为：`protected ?string $name = 'jwt:publish';`  
8. 拷贝data/nginx/conf.d/nursery/conf.example为nursery.conf,按需修改。如果需要ssl，在data/nginx下创建目录cert,并且将证书放进去，类型为.key和.pem

### 部署全部项目

`docker compose build` #拷贝.env.example 为.env 修改为自己的配置  
`docker compose up`
进入api容器执行 php bin/hyperf migrate --seed

