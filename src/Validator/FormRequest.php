<?php

namespace Songyz\Validator;

use Illuminate\Foundation\Http\FormRequest as FoundationFormRequest;


/**
 * 系统核心验证器
 * Class FormRequest
 * @package Songyz\Validator
 * @author songyz <574482856@qq.com>
 * @date 2020/5/10 20:03
 */
class FormRequest extends FoundationFormRequest
{
    use ValidationTrait;

    /** @var string 设置configName */
    protected $configName='songyz_validator';

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
}
