<?php
/**
 * KeyTypeException 类
 *
 * 数组键名类型不符异常
 *
 * @package    utils
 * @license    MIT
 * @link       https://github.com/zanemmm/utils
 */
namespace Zane\Utils\Exceptions;

use Exception;
use Throwable;

class AryKeyTypeException extends Exception
{
    public function __construct($message = "", $code = 500, Throwable $previous = null)
    {
        if (strlen($message) === 0) {
            $this->message = "The type of key must be string or integer, at {$this->file}, line: {$this->line}";
        }
    }
}
