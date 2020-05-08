# Laravel 以注释形式实现自动数据验证

laravel autoCheck 主要解决减去书写繁琐的验证规则，而是通过注释的形式自动去验证，使用autoCheck只需要完成下面的几个操作步骤即可实现。

## Laravel autoCheck 能够做什么事情，为什么要用？举例说明下

这是我们之前的代码编程方式
```php
/**
     * 添加nav
     * addtest
     * @return \Illuminate\Http\JsonResponse
     * 
     * @date 2019/06/20 14:24
     */
    public function addtest() {
        $data = $this->getData();
        $rules=[
            'title' => 'required',
            'pid' => 'required|numeric',
            'level' => 'required|numeric',
            'url' => 'required',
            'sort_id' => 'numeric',
            'status' => 'required|numeric|in:1,0'
        ];
        $msg=[
            'title.required' => '栏目名称不能为空',
            'pid.required' => '栏目不能为空',
            'pid.numeric' => '栏目id必须是数字',
            'level.required' => '栏目等级不能为空',
            'level.numeric' => '栏目等级必须是数字',
            'url.required' => 'url不能为空',
            'sort_id.numeric' => '排序id必须是数字',
            'status.required' => '展示控制不能为空',
            'status.numeric' => '展示控制必须是数字',
            'status.in' => '展示控制传值错误'
        ];
        $validator = \Illuminate\Support\Facades\Validator::make($data, $rules, $msg);
        if ($validator->fails()) {
            new ApiException($validator->errors()->first());
        }
        $result = $this->_Service->addNav($data);
        return $this->showJson($result);
    }
```

使用autoCheck后的编码方式
```php

    /**
     * add
     * @param int $title <required> 栏目名称不能为空
     * @param int $pid <required|numeric> 栏目不能为空|栏目id必须是数字
     * @param int $level <required|numeric> 栏目等级不能为空|栏目等级必须是数字
     * @param int $url <required> url不能为空
     * @param int $sort_id <numeric> 排序id必须是数字
     * @param int $status <required|numeric|in:1,0> 展示控制不能为空|展示控制必须是数字|展示控制传值错误
     * @return array
     * 
     * @date 2019/5/25 12:53
     */
    public function add() {
        $data = $this->getData();
        $result = $this->_Service->addNav($data);
        return $this->showJson($result);
    }

```

通过上下两个代码的对比，不难看出 autoCheck的优势所在



### 简单实现验证范例 3 步走

##### 1. composer包引入
首先使用composer引入 包

##### 2. 路由中间件中引入 app\Http\Kernel.php

找到 routeMiddleware
```php
<?php
 protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
         ...
        'autoCheck' => \Songyongzhan\AutoCheck\AutoCheck::class,
    ];

```

##### 3. 在路由配置中路由中间件 routes/api.php 添加autoCheck
```php
Route::group(['middleware' => ['autoCheck']], function () {
    Route::post('admin/login', 'Admin\V1\AdminController@login');   
});

```




