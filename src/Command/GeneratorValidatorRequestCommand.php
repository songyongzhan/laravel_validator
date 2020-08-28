<?php

namespace Songyz\Command;

use Illuminate\Console\Command;
use Illuminate\Foundation\Application as LaravelApplication;
use Laravel\Lumen\Application as LumenApplication;

/**
 * 生成验证类脚本
 * Class GeneratorValidatorRequestCommand
 * @package Songyz\Command
 * @author songyz <574482856@qq.com>
 * @date 2020/5/10 20:02
 */
class GeneratorValidatorRequestCommand extends Command
{
    const DS = DIRECTORY_SEPARATOR;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'songyz:make:request
    {--request_name= : Create validator request name}
    {--force : Overwrite any existing files}';

    /**
     * description config name
     * @var string
     */
    protected $configName = 'songyz_validator';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new form request validator class';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //创建类名
        $requestName = $this->option('request_name');

        if (empty($requestName)) {
            while (true) {
                $requestName = $this->ask("请输入验证器类名，支持模块化定义(Goods/Nav) 或者直接写 Nav：");
                if ($requestName) {
                    break;
                }
            }
        }

        //判断是否包含了 / 或者 \ 如果包含里面的一种，则需判断目录
        $requestName = str_replace('/', '\\', $requestName);
        $path = config($this->configName . '.request_path');
        if (empty($path)) {
            $path = base_path('app' . DIRECTORY_SEPARATOR . 'Http' . DIRECTORY_SEPARATOR . 'Requests');
        }

        //判断$requestName 中最后7位是不是 如果是，则去掉 Request
        $lastSevenString = substr($requestName, -7);

        if (strtolower($lastSevenString) == 'request') {
            $requestName = substr($requestName, 0, -7);
        }

        $namespace = $this->calculationNameSpace($path);

        $fileName = ucfirst($requestName) . 'Request.php';
        if (stristr($requestName, '\\')) {
            //计算命名空间   创建目录
            $requestNameData = explode('\\', $requestName);

            //拿到最后一个
            $filePrefix = array_pop($requestNameData);
            $fileName = $filePrefix . 'Request.php';
            $customPath = ucfirst(implode('\\', $requestNameData));
            $namespace = $namespace . '\\' . $customPath;
            $path = rtrim($path, self::DS) . self::DS . $customPath;
        }

        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $templateContent = $this->requestStub();
        $systemCategory = '';
        if ($this->getLaravel() instanceof LumenApplication) {
            $systemCategory = DIRECTORY_SEPARATOR . 'Lumen';
        }

        $templateContent = str_replace(['--namespace--', '--requestName--', '--systemCategory--', '--datetime--'],
            [$namespace, str_replace('Request.php', '', $fileName), $systemCategory, date('Y-m-d H:i:s')],
            $templateContent);

        $createFlag = true;
        if (file_exists($path . self::DS . $fileName) && !$this->option('force')) {
            //提示用户是否替换，如果不 则不生成
            $createFlag = $this->confirm($path . self::DS . $fileName . ' 文件已存在，是否替换');
        }

        if (!$createFlag) {
            return false;
        }

        $fileSize = file_put_contents($path . self::DS . $fileName, $templateContent);

        $this->info($path . self::DS . $fileName . ' 文件创建成功' . $fileSize);
    }


    /**
     * 根据路径计算命名空间
     * calculationNameSpace
     *
     * @date 2020/5/7 21:04
     * @param $path
     * @return string
     */
    private function calculationNameSpace($path)
    {
        //计算根目录 app path
        $basePath = base_path('app');
        $tempPath = str_replace(['/', '\\'], self::DS, str_replace($basePath, '', $path));
        $namespace = config($this->configName . '.namespace', null);
        if ($namespace) {
            return $namespace;
        }
        return "App" . self::DS . trim($tempPath, self::DS);
    }

    /**
     *
     * 生成验证类模板
     * requestStub
     * @return string
     *
     * @author songyz <574482856@qq.com>
     * @date 2020/5/9 21:49
     */
    protected function requestStub()
    {
        return <<<'TOT'
<?php

namespace --namespace--;

use Songyz\Validator--systemCategory--\FormRequest;

/**
 * 数据验证器
 * Class --requestName--Request
 * @package --namespace--
 * @date --datetime--
 */
class --requestName--Request extends FormRequest
{

}
TOT;

    }
}
