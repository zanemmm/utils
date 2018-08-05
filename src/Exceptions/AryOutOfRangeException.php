<?php
/**
 * AryOutOfRangeException 类.
 *
 * 超出数组范围异常
 *
 * @license    MIT
 *
 * @link       https://github.com/zanemmm/utils
 */

namespace Zane\Utils\Exceptions;

use OutOfRangeException;
use Throwable;

class AryOutOfRangeException extends OutOfRangeException
{
    public function __construct($message = '', $code = 500, Throwable $previous = null)
    {
        if (strlen($message) === 0) {
            $this->message = "Index of Ary is out of range, at {$this->file}, line: {$this->line}";
        }
    }
}
