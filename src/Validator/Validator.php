<?php

namespace Songyz\Validator;

use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Factory as FrameValidator;
use Songyz\Library\IdentityCard;

class Validator extends FrameValidator
{
    //系统内置 手机、身份证号验证
    protected function addMobile()
    {
        return $this->extend('mobile', function ($attribute, $value, $parameters, $validator) {
            //$attribute 属性名
            //$value 传过来的值
            return preg_match('/^1\d{10}$/', $value);
        });
    }

    protected function addIdCard()
    {
        return $this->extend('id_card', function ($attribute, $value, $parameters, $validator) {
            if (empty($value)) {
                return true;
            }

            return IdentityCard::isValid($value);
        });
    }

    /**
     * 根据配置文件中的extendsRule进行扩展
     * initExtendsRule
     *
     * @date 2020/5/9 10:25
     */
    public function initExtendsRule()
    {
        $this->addIdCard();
        $this->addMobile();
        $this->parseConfigRules();
        //从config中获取到扩展字典，然后注册到验证类中
        file_put_contents(base_path('storage' . DIRECTORY_SEPARATOR . 'a.log'), "init\n", FILE_APPEND);
    }

    private function parseConfigRules()
    {
        $rules = config('songyz_validator.append_extend_rules');

        if (empty($rules)) {
            return false;
        }

        $isPattern = '/^[\/|#]+[\s\S]+[\/|#]+([uUism]*)$/';
        foreach ($rules as $ruleKey => $ruleVal) {

            //判断是不是正则表达式
            if (is_callable($rules[$ruleKey])) {
                $this->extend($ruleKey, function ($attribute, $value, $parameters, $validator) use ($rules, $ruleKey) {
                    return $rules[$ruleKey]($attribute, $value, $parameters, $validator);
                });
            } else {
                //判断是不是正则，如果不是，则忽略
                if (!preg_match($isPattern, $ruleVal)) {
                    continue;
                }
                $this->extend($ruleKey, function ($attribute, $value, $parameters, $validator) use ($ruleVal) {
                    return preg_match($ruleVal, $value);
                });
            }
        }
    }


}
