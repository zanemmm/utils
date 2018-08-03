<?php
/**
 * StrEncodingException 类
 *
 * 字符串编码异常
 *
 * @package    utils
 * @license    MIT
 * @link       https://github.com/zanemmm/utils
 */
namespace Zane\Utils\Exceptions;

use Exception;
use Throwable;

class StrEncodingException extends Exception
{
    public function __construct($message = "", $code = 400, Throwable $previous = null)
    {
        if (strlen($message) === 0) {
            $this->message = "The string must be in UTF-8 format encoding, at {$this->file}, line: {$this->line}";
        }
    }
}
