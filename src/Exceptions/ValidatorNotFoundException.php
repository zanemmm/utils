<?php
/**
 * ValidatorNotFoundException 类
 *
 * 找不到对应验证器异常
 *
 * @package    utils
 * @license    MIT
 * @link       https://github.com/zanemmm/utils
 */
namespace Zane\Utils\Exceptions;

use Exception;
use Throwable;

class ValidatorNotFoundException extends Exception
{
    public function __construct($message = "", $code = 404, Throwable $previous = null)
    {
        if (strlen($message) === 0) {
            $this->message = "Custom validator not found, at {$this->file}, line: {$this->line}";
        }
    }
}
