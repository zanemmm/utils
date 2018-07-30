<?php
/**
 * Str 类
 *
 * 提供各种方便的方法操作字符串
 *
 * @package    utils
 * @license    MIT
 * @link       https://github.com/zanemmm/utils
 */
namespace Zane\Utils;

use Countable;
use Zane\Utils\Exceptions\StrEncodingException;

class Str implements Countable
{
    protected static $default = [
        'encodingList' => ['ASCII', 'UTF-8', 'GB2312', 'GBK']
    ];

    private $str;

    /**
     * Str constructor.
     * @param string $str
     * @param string|null $fromEncoding
     * @throws StrEncodingException
     */
    public function __construct(string $str, string $fromEncoding = null)
    {
        if (!is_null($fromEncoding)) {
            $str = static::convert($str, $fromEncoding);
        } elseif (!mb_check_encoding($str, 'UTF-8')) {
            throw new StrEncodingException();
        }

        $this->str = $str;
    }

    /**
     * 回实例中的字符串
     * @return string
     */
    public function str(): string
    {
        return $this->str;
    }

    /**
     * 返回字符串长度
     * @return int
     */
    public function len(): int
    {
        return mb_strlen($this->str);
    }

    /**
     * 返回字符串长度
     * @return int
     */
    public function count(): int
    {
        return mb_strlen($this->str);
    }

    /**
     * 返回一个新实例
     * @param string $str 实例包含的字符串
     * @param string|null $fromEncoding 字符串的原编码
     * @return Str 新实例
     * @throws StrEncodingException
     */
    public static function new(string $str, string $fromEncoding = null): self
    {
        return new static($str, $fromEncoding);
    }

    /**
     * 将字符串转为 UTF-8 编码
     * @param string $str 原字符串
     * @param string $fromEncoding 原编码格式
     * @return string UTF-8 编码的字符串
     */
    public static function convert(string $str, string $fromEncoding): string
    {
        return mb_convert_encoding($str, 'UTF-8', $fromEncoding);
    }

    /**
     * 设置类方法的默认值
     * @param array $default
     */
    public static function setDefault(array $default): void
    {
        foreach ($default as $key => $val) {
            static::$default[$key] = $val;
        }
    }

    /**
     * 若传入两个参数则第一个参数为 null 时，则返回 $default 数组中以 $second 为键名的值
     * 若第一个参数不为 null 则返回第一个参数的值
     * 若只传入一个参数则直接返回 $default 数组中以 $first 为键名的值
     * @param null $first
     * @param string|null $second
     * @return mixed|null
     */
    protected static function default($first, string $second = null)
    {
        if (func_num_args() < 2) {
            return static::$default[$first] ?? null;
        }

        if (is_null($first)) {
            return static::$default[$second] ?? null;
        }

        return $first;
    }
}
