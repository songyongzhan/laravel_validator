<?php

/**
 * Laravel 验证扩展类配置文件
 * @author songyz <574482856@qq.com>
 * @date 2019/09/20 20:27
 */
return [
    'failure_throw_exception' => \Songyz\Exception\ValidatorFailureException::class,//验证失败抛出异常类
    'failure_throw_code' => 1,//验证失败抛出异常错误码

];

