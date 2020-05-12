<?php

namespace Songyz\Validator;

use Freelancehunt\Validators\CreditCard;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Factory as FrameValidator;
use Songyz\Library\IdentityCard;

/**
 * Class Validator
 * @package Songyz\Validator
 * @author songyz <574482856@qq.com>
 * @date 2020/5/10 20:04
 */
class Validator extends FrameValidator
{
    /**
     *
     * addMobile
     * @return mixed
     *
     * @author songyz <574482856@qq.com>
     * @date 2020/5/10 20:04
     */
    protected function addMobile()
    {
        return $this->extend('mobile', function ($attribute, $value, $parameters, $validator) {
            //$attribute 属性名
            //$value 传过来的值
            return preg_match('/^1\d{10}$/', $value);
        });
    }

    /**
     * 验证银行卡
     * addCreditCard
     * @return mixed
     *
     * @author songyz <574482856@qq.com>
     * @date 2020/5/10 20:05
     */
    protected function addCreditCard()
    {
        return $this->extend('credit_card', function ($attribute, $value, $parameters, $validator) {
            $result = CreditCard::validCreditCard($value);
            if (!is_array($result) || isset($result['valid']) || $result['valid'] !== true) {
                return false;
            }
            return true;
        });
    }

    /**
     * 判断身份证
     * addIdCard
     * @return mixed
     *
     * @date 2020/5/10 20:00
     */
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
        $this->addCreditCard();

        //从config中获取到扩展字典，然后注册到验证类中
        $this->parseConfigRules();
    }

    private function parseConfigRules()
    {
        $rules = config('songyz_validator.append_extend_rules');

        if (empty($rules)) {
            return false;
        }

        $isPattern = '/^[\/|#]+[\s\S]+[\/|#]+([uUism]*)$/';
        foreach ($rules as $ruleKey => $ruleVal) {
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
