<?php

/**
 * Laravel 验证扩展类配置文件
 * @author songyz <574482856@qq.com>
 * @date 2019/09/20 20:27
 */
return [
    'failure_throw_exception' => \Songyz\Exceptions\ValidatorFailureException::class,//验证失败抛出异常类
    'failure_throw_code' => 422,//验证失败抛出异常错误码
    'namespace' => '', // 如果生成的文件不在app目录下，可自定义指定命名空间 默认为空
    'request_path' => base_path('app' . DIRECTORY_SEPARATOR . 'Http' . DIRECTORY_SEPARATOR . 'Requests'),
    'append_extend_rules' => [
        //添加验证规则  正则表达式
        'chinese_name' => '/^([\x{4e00}-\x{9fa5}])+$/u', //正则匹配
    ],
];

