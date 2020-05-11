Songyz Laravel Validator扩展
==============================

# 解决痛点
* 验证器Request中，可以编写多个验证规则
* 验证与业务代码分离、代码更加清晰
* 增加多种常用的验证规则 如 手机号验证、身份证验证
* 增强、优化扩展规则，通过配置文件即可实现多种规则
* 减少编写代码，提高代码复用 释放劳动力

## 安装配置
  ### 1、安装
```shell script
    composer require songyz/laravel_validator
```
执行 `composer update` 完成包加载。
### 2、laravel配置项 
找到 `config/app.php` 文件，在`providers`中添加以下代码
```php
  Songyz\Providers\ValidationServiceProvider::class,
  Songyz\Providers\ValidatorConfigProvider::class,
```
### 3、发布配置文件
```shell script
php artisan vendor:publish --provider="Songyz\Providers\ValidatorConfigProvider"
```
发布完后，会在`config`目录下生成 songyz_validator.php 配置文件

至此项目配置完成。

## 使用方法
假设 创建一个User用户类，创建新增方法、更新方法，实现必要的验证
### 1、创建Controller文件及接口
```shell script
php artisan mack:controller UsersController
```
再新创建Controller中新建两个方法：
```php
 public function add()
    {
        /**
         * 1、进行必要参数验证
         *    需要验证 username 不能为空
         *           moible    不能为空 且必须是手机号
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
         *    需要验证 username 不能为空
         *           moible   不能为空 且必须是手机号
         *           id       不能为空 且必须是数字
         * 2、验证主键Id是否存在
         * 2、接收参数
         * 3、调用方法入库
         * ...
         */

    }

    public function del()
    {
        /**
         * 1、进行必要参数验证
         *    需要验证  id       不能为空 且必须是数字
         * 2、验证主键Id是否存在
         * 2、接收参数
         * 3、调用方法入库
         * ...
         */
    }
```
### 2、创建验证器
```shell script
php artisan songyz:make:request --request_name=UserRequest
```
执行成功后，在 `app/Http/Requests/` 目录下看到UserRequest.php。  
`UserRequest.php`，代码如下:  
```php
<?php

namespace App\Http\Requests;

use Songyz\Validator\FormRequest;

class UserRequest extends FormRequest
{

    /**
     * 定义一个公共的验证 在del、update 方法中复用
     * @var array
     */
    protected $commonValidator = [
        'rules' => [
            'id' => 'required|integer'
        ],
        'messages' => [
            'id.required' => 'id不能为空',
            'id.integer' => 'id必须是数字',
        ]
    ];

    /**
     * 属性名需和控制器名保持一致
     * 对应控制器的add方法
     * @var array
     */
    protected $add = [
        'rules' => [
            'username' => 'required',
            'mobile' => 'required|mobile',
        ],
        'messages' => [
            'username.required' => '用户名不能为空',
            'mobile.required' => 'mobile不能为空',
            'mobile.mobile' => 'mobile格式不正确',
        ]
    ];

    /**
     * 属性名需和控制器名保持一致
     * 对应控制器的 update方法
     * @var array
     */
    protected $update = [
        'rules' => [
             'username' => 'required',
            'mobile' => 'required|mobile',
        ],
        'messages' => [
            'username.required' => '用户名不能为空',
            'mobile.required' => 'mobile不能为空',
            'mobile.mobile' => 'mobile格式不正确',
        ]
    ];

    /**
     * 删除方法
     * @var array
     */
    protected $del = [];

    /**
     * 实现验证器复用
     * reuseAttribute
     *
     * @author songyz <574482856@qq.com>
     * @date 2020/5/10 10:48
     */
    protected function reuseAttribute()
    {
        $this->del = $this->commonValidator; //实现了id的验证

        /**
         * 将update和commonValidator 合并起来
         * 实现了 验证 用户名、手机号以及id
         *
         * 扩展：当然 如果你愿意 $update 中的属性也可以不用写，用下面代码实现
         *
         * protected $update = [];
         * $this->update = $this->multiMerge($this->commonValidator, $this->add);
         */
        $this->update = $this->multiMerge($this->commonValidator, $this->update);
    }
}
```

### 3、控制器方法与验证器类绑定- Laravel通过依赖注入实现

```php
use App\Http\Requests\UserRequest;

    public function add(UserRequest $request)
    {
        echo 'add';
        /**
         * 1、进行必要参数验证
         *    需要验证 username 不能为空
         *           moible    不能为空 且必须是手机号
         * 2、接收参数
         * 3、调用方法入库
         * ...
         */
        echo time();

    }

    public function update(UserRequest $request)
    {
        echo 'update';
        /**
         * 1、进行必要参数验证
         *    需要验证 username 不能为空
         *           moible   不能为空 且必须是手机号
         *           id       不能为空 且必须是数字
         * 2、验证主键Id是否存在
         * 2、接收参数
         * 3、调用方法入库
         * ...
         */

    }

    public function del(UserRequest $request)
    {
        echo 'del';
        /**
         * 1、进行必要参数验证
         *    需要验证  id       不能为空 且必须是数字
         * 2、验证主键Id是否存在
         * 2、接收参数
         * 3、调用方法入库
         * ...
         */
    }
```
### 4、示例演示

`web.php` 定义路由：
```php
Route::prefix('users')->group(function () {
    Route::any('add', 'UsersController@add');
    Route::any('update', 'UsersController@update');
    Route::any('del', 'UsersController@del');
});
```
* users/add
> 未填写mobile
![图片](http://www.xiaosongit.com/Public/Upload/image/20200510/1589081856871205.png)

>mobile格式错误
![图片](http://www.xiaosongit.com/Public/Upload/image/20200510/1589081914307862.png)

>add方法验证通过
![图片](http://www.xiaosongit.com/Public/Upload/image/20200510/1589082004854535.png)

* users/update
>mobie为空
![图片](http://www.xiaosongit.com/Public/Upload/image/20200510/1589083074759721.png)

* users/del
> id格式不正确
![图片](http://www.xiaosongit.com/Public/Upload/image/20200510/1589082845670523.png)

> id通过验证
![图片](http://www.xiaosongit.com/Public/Upload/image/20200510/1589082963765808.png)




