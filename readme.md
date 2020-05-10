Laravel Validator扩展 Authentication
==============================

##安装配置
  ###1、安装
  ```json
"require": {
    "songyz/laravel_validator": "^0.",
}  
```
执行 `composer update` 完成包加载。
###2、laravel配置项 
找到 `config/app.php` 文件，在`providers`中添加以下代码
```php
  Songyz\Providers\ValidationServiceProvider::class,
  Songyz\Providers\ValidatorConfigProvider::class,
```
###3、发布配置文件
```shell script
php artisan vendor:publish --provider="Songyz\Providers\ValidatorConfigProvider"
```
发布完后，会在`config`目录下生成 songyz_validator.php 配置文件

至此项目配置完成。

##使用方法
假设 创建一个User用户类，创建新增方法、更新方法，实现必要的验证
###1、创建Controller文件及接口
```shell script
php artisan mack:controller UsersController
```
再新创建Controller中新建两个方法：
```php
public function add()
    {
        /**
         * 1、进行必要参数验证
         * 2、接收参数
         * 3、调用方法入库
         * ...
         */
        echo time();
    }

    public function update()
    {
        /**
         * 1、进行必要参数验证
         * 2、验证主键Id是否存在
         * 2、接收参数
         * 3、调用方法入库
         * ...
         */
    }
```
