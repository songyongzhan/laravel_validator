Songyz Laravel Validator扩展
==============================

# 解决痛点
* 验证器Request中，可以编写多个验证规则
* 验证与业务代码分离、代码更加清晰
* 增加多种常用的验证规则 如 手机号验证、身份证验证
* 增强、优化扩展规则，通过配置文件即可实现多种规则
* 减少代码编写，提高代码复用 释放劳动力

## 安装配置
  ### 1、安装
```shell script
    composer require songyz/laravel_validator
```
执行 `composer update` 完成包加载。
### 2、加载服务
> Laravel加载服务   
> 找到 `config/app.php` 文件，在`providers`中添加以下代码
```php
  Songyz\Providers\ValidationServiceProvider::class,
  Songyz\Providers\ValidatorConfigProvider::class,
```
> Lumen加载服务  
> 打开`bootstrap/app.php` 添加以下服务提供
```php
$app->register(Songyz\Providers\ValidationServiceProvider::class);  
$app->register(Songyz\Providers\ValidatorConfigProvider::class);  
$app->register(Songyz\Providers\LumenFoundationServiceProvider::class);
```

### 3、发布配置

> Laravel 执行命令发布  
```shell script
php artisan vendor:publish --provider="Songyz\Providers\ValidatorConfigProvider"
```
> Lumen 需手动发布  
> 
> 在`vendor/songyz/laravel_validator/src/config` 找到 `songyz_validator.php` 复制到`config`目录下。  
>如果`config`目录不存在，手动创建即可。

  
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

### 4、验证未通过抛出ValidatorFailureException异常

如果未通过验证，系统将抛出 `ValidatorFailureException` 异常。  
可以在 exceptions/handle.php 捕捉。  
例如：
开发接口项目：  
```php
use Songyz\Exceptions\ValidatorFailureException;

public function render($request, Exception $exception)
{
    //如果是ajax请求 则返回接口形式的数据
    if ($request->ajax()) {
        $message = $exception->getMessage();
        $defaultMessage = '网络开小差喽 请稍后...';

        if ($exception instanceof ValidatorFailureException) {
            //处理你的逻辑...     
        }
        $code = strval($exception->getCode() == '0' ? '1' : $exception->getCode());

        $jsonData = $data ?? [];
        return response()->json(['code' => $code, 'message' => $message, 'data' => $jsonData], 200, [
            'Content-type' => 'application/json'
        ], JSON_UNESCAPED_UNICODE);

    }

    return parent::render($request, $exception);
}
```

### 5、示例演示

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

## 补充异常

此功能可选择使用

打开`app/Exceptions/Handler.php`文件，将下面的代码替换原有的代码
```php
<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Psr\Log\LoggerInterface;
use Songyz\Exceptions\ValidatorFailureException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [

    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * 为什么会写这个方法
     * 以前是 error 级别，这是错误级别
     * 为了将级别设置为 info 所以写的这个方法
     * 将ApiException 和 ValidatorFailureException 这两个异常记录为 info 级别
     *
     * Report or log an exception.
     *
     * @param \Throwable $exception
     * @return void
     *
     * @throws \Throwable
     */
    public function report(Throwable $exception)
    {
        if ($this->shouldntReport($exception)) {
            return;
        }

        if (is_callable($reportCallable = [$exception, 'report'])) {
            return $this->container->call($reportCallable);
        }

        try {
            $logger = $this->container->make(LoggerInterface::class);
        } catch (\Exception $ex) {
            throw $ex;
        }

        $context = array_merge(
            $this->exceptionContext($exception),
            $this->context(),
            ['exception' => $exception]
        );
        $loggerLevel = 'error';

        if ($exception instanceof ApiException || $exception instanceof ValidatorFailureException) {
            $context = [
                'exception' => 'ApiException',
                'file'      => $exception->getFile(),
                'line'      => $exception->getLine()
            ];
            $loggerLevel = 'info';
        }

        $logger->{$loggerLevel}($exception->getMessage(), $context);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * 如果是ajax请求，则返回 json格式
     *
     * 否则返回正常的web页面
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        //如果是ajax请求 则返回接口形式的数据
        if ($request->ajax()) {
            $message = '网络开小差喽 请稍后...';
            if ($exception instanceof ApiException || $exception instanceof ValidatorFailureException) {
                $message = $exception->getMessage();
            }
            //开发环境将详细错误日志打印出来，方便排查问题
            if (app()->environment() == "development") {
                $data = [
                    'exception' => get_class($exception),
                    'file'      => $exception->getFile(),
                    'line'      => $exception->getLine()
                ];
            }
            $code = strval($exception->getCode() == '0' ? '1' : $exception->getCode());
            return response()->json(get_return_json($data ?? [], $code, $message), 200, [], JSON_UNESCAPED_UNICODE);
        }

        return parent::render($request, $exception);
    }
}
```

新建`ApiException`类

```php
<?php

namespace App\Exceptions;

use Throwable;

/**
 *
 * Class ApiException
 * @package App\Exceptions
 */
class ApiException extends \Exception
{
    public $data;

    public function __construct(string $message = "", $code = 1, array $data = [], Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->data = $data;
    }
}
```