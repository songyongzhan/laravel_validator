<?php
/**
 * Created by PhpStorm.
 * User: song
 * Date: 2019/7/25
 * Time: 13:15
 * Email: songyz <songyz@guahao.com>
 */

namespace Songyongzhan\Exceptions;

class ParameterException extends \Exception
{
    public function __construct(string $message = "", $code = 1, Throwable $previous = NULL)
    {
        parent::__construct($message, $code, $previous);
    }
}