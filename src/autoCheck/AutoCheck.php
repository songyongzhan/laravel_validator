<?php

namespace Songyongzhan\AutoCheck;

use Songyongzhan\Exceptions\ParameterException;
use Songyongzhan\Libaray\Reflec;
use Illuminate\Support\Facades\App;
use Closure;

class AutoCheck
{
    public $_rule;
    const IGNORE = [];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $file = trim(explode('@', \Route::currentRouteAction(), 2)[0], DS) . '.php';
        $method = explode('@', \Route::currentRouteAction(), 2)[1];

        $validateFileName = str_replace('\\', '_', $file);


        $path = App::storagePath() . DS . 'app' . DS . 'cache' . DS;

        $validateFile = $path . $validateFileName;


        $reflec = new Reflec(\Route::getCurrentRoute()->getController());

        if (file_exists($validateFile) && (filemtime($path . $validateFileName) > $reflec->getFileTime())) {
            $this->_rule = require $validateFile;
        } else {
            //生成缓存
            $this->_rule = $this->_makeFile($reflec, $validateFile);
        }
        //进行数据比对
        if (!is_array($rules = $this->_rule))
            throw new \Exceptions('$this->_rule is not Array.', 500);

        if (isset($rules['rules'][$method]) && ($methodRules = $rules['rules'][$method])) {

            $data = app('request')->except('s');

            //如果unique 排除一个id 变量存在，就进行值的替换
            $rulesStr = implode(',', array_values($methodRules));
            if (strpos($rulesStr, 'unique:') !== FALSE && strpos($rulesStr, '$')) {
                foreach ($methodRules as $k => &$val) {
                    if (strpos($val, 'unique:')) {
                        $rulesData = explode(',', explode('unique:', $val)[1]);
                        if (isset($rulesData[2]) && strpos($rulesData[2], '$') !== FALSE) {
                            $keyName = substr($rulesData[2], 1);
                            if (isset($data[$keyName]))
                                $val = str_replace($rulesData[2], $data[$keyName], $val);
                        }
                    }
                }
            }

            if (TRUE !== ($result = $this->validate($data, $methodRules, $rules['msg'][$method]))) {
                throw new ParameterException($result['errMsg'], 1);
            }
        }

        return $next($request);
    }


    private function _makeFile($reflection, $validateFile)
    {
        $config = [];
        $config['rules'] = [];
        $config['params'] = [];

        foreach ($reflection->getAllComment('/^\s+\*\s@param\s+(\w+)\s+\$(\w+)\s+<([^>]+)>\s?([^\s\*]*)/im'
            , self::IGNORE) as $key => $item) {
            for ($i = 0; $i < count($item[0]); $i++) {

                $config['rules'][$key][$item[2][$i]] = $item[3][$i];

                /*protected $rule =   [
                  'name'  => 'required|max:25',
                  'age'   => 'required|between:1,120',
                  'email' => 'email',
                ];

                protected $message  =   [
                  'name.require' => '名称必须',
                  'name.max'     => '名称最多不能超过25个字符',
                  'age.number'   => '年龄必须是数字',
                  'age.between'  => '年龄只能在1-120之间',
                  'email'        => '邮箱格式错误',
                ];*/

                if (strpos($item[3][$i], '|') && strpos($item[4][$i], '|')) {
                    $moreRules = explode('|', $item[3][$i]);
                    $moreMessage = explode('|', $item[4][$i]);
                    $count = count($moreMessage);
                    for ($j = 0; $j < $count; $j++) {
                        $ruleName = strpos($moreRules[$j], ':') ? (explode(':', $moreRules[$j]))[0] : $moreRules[$j];
                        $config['msg'][$key][$item[2][$i] . '.' . $ruleName] = $moreMessage[$j];
                    }
                } else {
                    $config['msg'][$key][$item[2][$i] . '.' . $item[3][$i]] = $item[4][$i];
                }
            }
            $config['params'][$key] = $reflection->getMethodParams($key, TRUE);
        }
        $config['file'] = $reflection->getFileName();
        $filePath = dirname($validateFile);
        if (!is_dir($filePath))
            @mkdir($filePath, 0777, TRUE);
        $flag = file_put_contents($validateFile, '<?php return ' . var_export($config, TRUE) . ';', LOCK_EX);
        $flag && chmod($validateFile, 0777);
        return $config;
    }

    /**
     * 自动验证方法
     * validate
     * @param $data
     * @param $rules
     * @param $msg
     * @return array|bool
     * @author songyz <songyz@guahao.com>
     * @date 2019/06/05 15:13
     */
    public function validate($data, $rules, $msg)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($data, $rules, $msg);
        if ($validator->fails())
            return ['errMsg' => $validator->errors()->first()];
        else return TRUE;
    }
}
