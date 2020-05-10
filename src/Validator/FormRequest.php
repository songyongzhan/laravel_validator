<?php

namespace Songyz\Validator;

use Illuminate\Foundation\Http\FormRequest as FoundationFormRequest;
use Illuminate\Support\Str;
use Songyz\Exceptions\ValidatorFailureException;

/**
 * 系统核心验证器
 * Class FormRequest
 * @package Songyz\Validator
 * @author songyz <574482856@qq.com>
 * @date 2020/5/10 20:03
 */
class FormRequest extends FoundationFormRequest
{
    /** @var array 忽略验证的方法 */
    protected $ignore = [];

    /** @var array 公共 */
    protected $commonValidator = [];

    public function authorize()
    {
        return true;
    }

    public function __construct(
        array $query = [],
        array $request = [],
        array $attributes = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        $content = null
    ) {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
        $this->reuseAttribute();
    }

    /**
     * 获取所有数据，并把 null去掉
     * all
     * @param null $keys
     * @return array
     *
     * @date 2020/3/28 18:01
     */
    public function all($keys = null)
    {
        $result = parent::all($keys);
        //递归将里面所有的值遍历将null 替换成空
        $result = $this->fetchResult($result);

        return $result;
    }

    /**
     * 过滤null值
     * fetchResult
     * @param $data
     * @return array
     *
     * @date 2020/5/8 18:13
     */
    private function fetchResult($data)
    {
        if (!$data || !is_array($data)) {
            return $data;
        }
        foreach ($data as $key => $val) {
            if (is_array($val)) {
                $data[$key] = $this->fetchResult($val);
            } else {
                $data[$key] = is_null($val) ? '' : $val;
            }
        }
        return $data;
    }

    /**
     * 属性复用
     * reuseAttribute
     * @date 2019/09/24 11:41
     */
    protected function reuseAttribute()
    {

    }

    /**
     * 返回验证规则
     * rules
     * @return array
     * @date 2019/09/23 10:22
     */
    public function rules()
    {
        $routeInfo = $this->getPathInfoSnake();

        /** 忽略验证ignore中的方法 */
        if (in_array($routeInfo['method'], $this->ignore)) {
            return [];
        }
        if (property_exists($this, $routeInfo['method'])) {
            $property = (string)$routeInfo['method'];
            $tempRules = $this->$property;
            if (isset($tempRules['rules']) && is_array($tempRules['rules'])) {
                return $tempRules['rules'];
            }
        }

        $functionName = $routeInfo['method'];
        if (!$functionName) {
            return [];
        }
        $result = $this->$functionName();

        return $result['rules'] ?? [];
    }

    /**
     * 获取属性替换属性
     * attributes
     * @return array
     *
     * @date 2019/09/23 10:23
     */
    public function attributes()
    {
        $routeInfo = $this->getPathInfoSnake();
        if (property_exists($this, $routeInfo['method'])) {
            $property = (string)$routeInfo['method'];
            $tempRules = $this->$property;
            if (isset($tempRules['attributes']) && is_array($tempRules['attributes'])) {
                return $tempRules['attributes'];
            }
        }

        return [];
    }

    /**
     * 获取提示信息
     * messages
     * @return array
     * @author songyz <574482856@qq.com>
     * @date 2019/09/23 10:23
     */
    public function messages()
    {
        $routeInfo = $this->getPathInfoSnake();
        /** 忽略验证ignore中的方法 */
        if (in_array($routeInfo['method'], $this->ignore)) {
            return [];
        }
        if (property_exists($this, $routeInfo['method'])) {
            $property = (string)$routeInfo['method'];
            $tempRules = $this->$property;
            if (isset($tempRules['messages']) && is_array($tempRules['messages'])) {
                return $tempRules['messages'];
            }
        }

        $functionName = $routeInfo['method'];
        if (!$functionName) {
            return [];
        }
        $result = $this->$functionName();
        return $result['messages'] ?? [];
    }

    /**
     * 数据验证
     * failedValidation
     * @param \Illuminate\Validation\Validator $validator
     * @author songyz <574482856@qq.com>
     * @date 2019/09/20 20:27
     */
    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        //如果验证失败，则获取配置文件中的异常
        $exception = config('songyz_validator.failure_throw_exception');
        $errCode = config('songyz_validator.failure_throw_code');
        empty(strlen($errCode)) && $errCode = 1;

        if (empty($exception)) {
            $exception = ValidatorFailureException::class;
        }

        throw new $exception($validator->getMessageBag()->first(), $errCode);
    }

    /**
     * 如果方法不存在，则返回空数组
     * __call
     * @param string $method
     * @param array $parameters
     * @return array|mixed
     * @author songyz <574482856@qq.com>
     * @date 2019/09/20 20:27
     */
    public function __call($method, $parameters)
    {
        return [];
    }

    /**
     * 获取当前路由相关信息
     * getPathInfoSnake
     * @return string
     * @author songyz <574482856@qq.com>
     * @date 2019/09/20 18:26
     */
    private function getPathInfoSnake()
    {
        $pathInfo = app('request')->getPathInfo();
        $params = explode('/', trim($pathInfo, '/'));
        $function = array_slice($params, 0, 2);
        $pathInfo = Str::camel(implode('_', $function));
        $file = trim(explode('@', \Route::currentRouteAction(), 2)[0], DIRECTORY_SEPARATOR) . '.php';
        $method = explode('@', \Route::currentRouteAction(), 2)[1];

        return ['method' => $method, 'pathInfo' => $pathInfo, 'filePosition' => $file];
    }

    /**
     * 通过传入的rules字符串解析规则
     * parseRules
     * @param $rules
     * @return array|string
     * @author songyz <574482856@qq.com>
     * @date 2019/09/20 20:14
     */
    protected function parseRules(string $rules)
    {
        if (!$rules) {
            return [];
        }
        $rules = str_replace('@param', ' @param', $rules);
        preg_match_all('/^\s+@param\s+\$(\w+)\s+<([^>]+)>\s?([^\s\*]*)/im', $rules, $result);
        if (!$result) {
            return [];
        }

        $titles = $result[1];
        $rules = $result[2];
        $message = $result[3];
        $isMessageEmptyData = array_unique($message);

        $isMessageEmpty = empty(array_shift($isMessageEmptyData));
        $messageInfo = [];
        foreach ($rules as $key => $item) {

            if (strpos($item, '|') && strpos($message[$key], '|')) {
                $moreRules = explode('|', $item);
                $moreMessage = explode('|', $message[$key]);
                $count = count($moreMessage);
                if ($count != count($moreRules)) {
                    return [];
                }
                for ($j = 0; $j < $count; $j++) {
                    $ruleName = strpos($moreRules[$j], ':') ? (explode(':', $moreRules[$j]))[0] : $moreRules[$j];
                    $messageInfo[$titles[$key] . '.' . $ruleName] = $moreMessage[$j];
                }
            } else {
                //如果message为空，则message就都为空
                if (!$isMessageEmpty) {
                    $messageInfo[$titles[$key] . '.' . $item] = $message[$key];
                }
            }
        }
        return ['rules' => array_combine($titles, $rules), 'messages' => $messageInfo];
    }

    /**
     * 获取pathInfo中的接口
     * validationData
     * @return array
     *
     * @author songyz <574482856@qq.com>
     * @date 2020/5/8 18:17
     */
    public function validationData()
    {
        //这样既可实现path_info上的值验证， 这是有顺序的，为了和 laravel的取值保持一致，先 get、post然后才path_info
        return array_merge($this->route()->parameters(), $this->all());
    }

    /**
     * multiMerge($default,$data)
     * PS:
     *
     * $e=$this->multiMerge($default,$data);
     * print_r($e);
     *
     * 递归合并规则数组
     * multiMerge
     * @return array
     * @author songyz <574482856@qq.com>
     * @date 2019/09/24 13:16
     */
    protected function multiMerge()
    {
        $args = func_get_args();
        $merged = array();
        while ($args) {
            $array = array_shift($args);
            if (!$array) {
                continue;
            }
            foreach ($array as $key => $value) {
                if (is_string($key)) {
                    if (is_array($value) && array_key_exists($key, $merged)
                        && is_array($merged[$key])) {
                        $merged[$key] = call_user_func_array([$this, 'multiMerge'],
                            array($merged[$key], $value));
                    } else {
                        $merged[$key] = $value;
                    }
                } else {
                    $merged[] = $value;
                }
            }

        }
        return $merged;
    }
}
