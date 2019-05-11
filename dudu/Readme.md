## 项目概述

* 产品名称：都督 dudu
* 项目代号：dudu
* 官方地址：https://code.aliyun.com/372271602/dudu

<br>

略

## 功能如下

- 保密

## 运行环境要求

- nginx 1.8+
- php 7.1+
- mysql 5.7+


<br>

## 开发环境部署/安装

本项目代码使用 php 框架 [laravel 5.5](https://d.laravel-china.org/docs/5.5/) 开发，本地开发环境使用 [laravel homestead](https://d.laravel-china.org/docs/5.5/homestead)。

下文将在假定读者已经安装好了 homestead 的情况下进行说明。如果您还未安装 homestead，可以参照 [homestead 安装与设置](https://laravel-china.org/docs/5.5/homestead#installation-and-setup) 进行安装配置。
<br>

### 基础安装

#### 1. 克隆源代码

克隆 `dudu` 源代码到本地： (若用ssh协议，请自行上code.aliyun配置公钥)

    > git clone https://code.aliyun.com/372271602/dudu.git

#### 2. 配置本地 Nginx 环境

新建 nginx 配置文件
    
    sudo cp /etc/nginx/sites-available/default /etc/nginx/sites-available/dudu.conf 


打开配置文件
    
    sudo vim /etc/nginx/sites-available/dudu.conf
    
修改配置文件中项目路径及虚拟域名
 ```
 server {
        # 项目路径
        root /home/username/Code/dudu/public; # username 是你电脑的当前用户名

        # 本地虚拟域名
        server_name dudu.test; 
}
 ``` 
添加配置文件软连接
    
    sudo ln -s /etc/nginx/sites-available/dudu.conf /etc/nginx/sites-enabled/
    
将虚拟域名添加至本地 hosts 文件

    # 打开 hosts 文件
    sudo vim /etc/hosts
    
    # 添加本地虚拟域名 dns
    127.0.0.1     dudu.test
    
       

#### 3. 安装扩展包依赖
进入项目目录, 并运行下面命令

	composer install

#### 4. 生成配置文件

```
cp .env.example .env
```

你可以根据情况修改 `.env` 文件里的内容，如数据库连接、缓存、邮件设置等：

```
APP_URL=http://dudu.test
...
DB_HOST=localhost
DB_DATABASE=dudu
DB_USERNAME=xxxxxx  # 本地数据库的用户名
DB_PASSWORD=xxxxxx  # 本地数据库的密码
```

#### 5. 生成项目秘钥

```shell
php artisan key:generate
```

#### 6. 生成数据表及生成测试数据

```shell
$ php artisan migrate --seed
```


### 前端框架安装

#### 1. 安装 Laravel Mix

进入项目目录，运行命令

```shell
yarn install
```

#### 2. eslint配置

```$xslt
将 example.eslintrc.json 改为 .eslintrc.json
```

#### 3.开发环境热加载模式

    npm run watch-poll

```
运行 npm run watch-poll 后，需要关闭点击事件监听
访问：localhost:3001,关闭 Sync Options 的 Clicks
这个如果不关，开发的时候，clicks事件会触发三次
```

#### 4. 编译前端内容

```shell
// 运行所有 Mix 任务... （开发环境）
npm run dev

// 运行所有 Mix 任务并缩小输出.. （生产环境）
npm run production
```


### 链接入口

* 首页地址：http://dudu.test/#/
* 管理后台：http://dudu.test/admin/login
* 测试账号： admin
* 测试密码： admin

至此, 安装完成 ^_^。

<br>

## 服务器架构说明
- 暂无


## 扩展包使用情况

| 扩展包 | 一句话描述 | 本项目应用场景 |  
| --- | --- | --- | --- | --- | --- | --- | --- |  
| [overtrue/laravel-wechat](https://github.com/overtrue/laravel-wechat) | 微信开发第三方SDK | 用于接入微信公众号 |  
| [vue.js](https://github.com/vuejs/vue) | 前端框架 | 不解释 |  
| [vue-router](https://github.com/vuejs/vue-router) | vue前端路由 | 标配 |  
| [vuex](https://github.com/vuejs/vuex) | 状态存储器 | 标配 |  
| [axios](https://github.com/axios/axios) | api请求工具库 | 标配 |  
| [vue-axios](https://github.com/imcvampire/vue-axios) | api请求工具辅助工具 | 为了方便的全局引用axios |  
| [vuetify](https://github.com/vuetifyjs/vuetify) | Material Design Component Framework | 前端样式框架 |  
| [eslint](https://github.com/eslint/eslint) | 前端编码规范校验工具 | 标准化工具 |  

composer require chumper/zipper



## 自定义 Artisan 命令

消息提醒定时任务
    
    php artisan schedule:run


## 队列清单
- 暂无
