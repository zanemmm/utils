<?php
/**
 * Str 类.
 *
 * 提供各种方便的方法操作字符串
 *
 * @license    MIT
 *
 * @link       https://github.com/zanemmm/utils
 */

namespace Zane\Utils;

use Countable;
use Zane\Utils\Exceptions\StrEncodingException;

class Str implements Countable
{
    protected static $default = [
        'positionCaseSensitive' => true,
        'positionReverse'       => false,
        'searchBefore'          => false,
        'searchCaseSensitive'   => true,
        'searchReverse'         => false,
        'beforeContain'         => false,
        'afterContain'          => false,
        'replaceCaseSensitive'  => true,
        'toMd5RawOutput'        => false,
        'toSha1RawOutput'       => false,
        'passwordHashAlgorithm' => PASSWORD_DEFAULT,
        'passwordHashCost'      => 10,
        'compCaseSensitive'     => true,
        'natCompCaseSensitive'  => true,
    ];

    private $str;

    /**
     * Str constructor.
     *
     * @param string      $str
     * @param string|null $fromEncoding
     *
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
     * 返回实例中的字符串.
     *
     * @return string
     */
    public function str(): string
    {
        return $this->str;
    }

    /**
     * 设置实例中的字符串.
     *
     * @param string|Str $str 设置的字符串必须以 UTF-8 编码
     *
     * @throws StrEncodingException
     *
     * @return $this 原实例
     */
    public function set($str)
    {
        $str = static::getStr($str);
        if (!mb_check_encoding($str, 'UTF-8')) {
            throw new StrEncodingException();
        }
        $this->str = $str;

        return $this;
    }

    /**
     * 字符串转大写.
     *
     * @see http://php.net/manual/zh/function.mb-convert-case.php
     *
     * @throws StrEncodingException
     *
     * @return Str 新实例
     */
    public function toUpperCase(): self
    {
        $str = mb_convert_case($this->str, MB_CASE_UPPER, 'UTF-8');

        return static::new($str);
    }

    /**
     * 字符串转小写.
     *
     * @see http://php.net/manual/zh/function.mb-convert-case.php
     *
     * @throws StrEncodingException
     *
     * @return Str 新实例
     */
    public function toLowerCase(): self
    {
        $str = mb_convert_case($this->str, MB_CASE_LOWER, 'UTF-8');

        return static::new($str);
    }

    /**
     * 字符串单词首字母转大写.
     *
     * @see http://php.net/manual/zh/function.mb-convert-case.php
     *
     * @throws StrEncodingException
     *
     * @return Str 新实例
     */
    public function toTitleCase(): self
    {
        $str = mb_convert_case($this->str, MB_CASE_TITLE, 'UTF-8');

        return static::new($str);
    }

    /**
     * 使用一个字符串分割另一个字符串.
     *
     * @see http://php.net/manual/zh/function.explode.php
     *
     * @param string|Str $delimiter 边界上的分隔字符
     * @param int|null   $limit     最多分割为 limit 个元素
     *
     * @return Ary
     */
    public function explode($delimiter, int $limit = null): Ary
    {
        $delimiter = static::getStr($delimiter);

        if (func_num_args() === 1) {
            $array = explode($delimiter, $this->str);
        } else {
            $array = explode($delimiter, $this->str, $limit);
        }

        return Ary::new($array);
    }

    /**
     * 使用正则表达式分割多字节字符串.
     *
     * @see http://php.net/manual/zh/function.mb-split.php
     *
     * @param string|Str $pattern 正则表达式
     * @param int|null   $limit   最多分割为 limit 个元素
     *
     * @return Ary
     */
    public function split($pattern, int $limit = null): Ary
    {
        $pattern = static::getStr($pattern);

        if (func_num_args() === 1) {
            $array = mb_split($pattern, $this->str);
        } else {
            $array = mb_split($pattern, $this->str, $limit);
        }

        return Ary::new($array);
    }

    /**
     * 获取部分字符串.
     *
     * @see http://php.net/manual/zh/function.mb-substr.php
     *
     * @param int      $start 开始字符数
     * @param int|null $len   子串长度
     *
     * @throws StrEncodingException
     *
     * @return Str 新实例
     */
    public function substring(int $start, int $len = null): self
    {
        $str = mb_substr($this->str, $start, $len, 'UTF-8');

        return static::new($str);
    }

    /**
     * 子字符串出现次数.
     *
     * @see http://php.net/manual/zh/function.mb-substr-count.php
     *
     * @param string|Str $needle 子字符串
     *
     * @return int
     */
    public function substringCount($needle): int
    {
        $needle = static::getStr($needle);

        return mb_substr_count($this->str, $needle, 'UTF-8');
    }

    /**
     * 以指定长度截断字符串.
     *
     * @param int             $len    指定长度
     * @param string|Str|null $marker 截断字符串后连接的字符串，比如可以为：“……”
     *
     * @throws StrEncodingException
     *
     * @return Str 新实例
     */
    public function truncate(int $len, $marker = null): self
    {
        if ($len >= mb_strlen($this->str)) {
            return static::new($this->str);
        }

        $str = mb_substr($this->str, 0, $len, 'UTF-8').static::getStr($marker);

        return static::new($str);
    }

    /**
     * 查找字符串在另一个字符串中出现的位置.
     *
     * @see http://php.net/manual/zh/function.mb-strpos.php
     * @see http://php.net/manual/zh/function.mb-stripos.php
     * @see http://php.net/manual/zh/function.mb-strrpos.php
     * @see http://php.net/manual/zh/function.mb-strripos.php
     *
     * @param string|Str $needle        要查找的字符串
     * @param int        $offset        开始查找偏移量
     * @param bool|null  $caseSensitive 大小写敏感
     * @param bool|null  $reverse       true 为首次出现的位置，false为最后一次出现的位置
     *
     * @return false|int
     */
    public function position($needle, int $offset = 0, bool $caseSensitive = null, bool $reverse = null)
    {
        $needle = static::getStr($needle);

        $position = false;
        $status = static::default($caseSensitive, 'positionCaseSensitive') << 1
            | static::default($reverse, 'positionReverse');
        switch ($status) {
            // $caseSensitive = false, $reverse = false
            case 0b00:
                $position = mb_stripos($this->str, $needle, $offset, 'UTF-8');
                break;
            // $caseSensitive = false, $reverse = true
            case 0b01:
                $position = mb_strripos($this->str, $needle, $offset, 'UTF-8');
                break;
            // $caseSensitive = true, $reverse = false
            case 0b10:
                $position = mb_strpos($this->str, $needle, $offset, 'UTF-8');
                break;
            // $caseSensitive = true, $reverse = true
            case 0b11:
                $position = mb_strrpos($this->str, $needle, $offset, 'UTF-8');
                break;
        }

        return $position;
    }

    /**
     * 查找并返回子串.
     *
     * @see http://php.net/manual/zh/function.mb-strstr.php
     * @see http://php.net/manual/zh/function.mb-stristr.php
     * @see http://php.net/manual/zh/function.mb-strrchr.php
     * @see http://php.net/manual/zh/function.mb-strrichr.php
     *
     * @param string|Str $needle        要查找的字符串
     * @param bool|null  $before        返回 $needle 之前的字符串
     * @param bool|null  $caseSensitive 大小写敏感
     * @param bool|null  $reverse       true 为首次出现的位置，false为最后一次出现的位置
     *
     * @throws StrEncodingException
     *
     * @return Str|false 新实例或 false
     */
    public function search($needle, bool $before = null, bool $caseSensitive = null, bool $reverse = null)
    {
        $needle = static::getStr($needle);
        $before = static::default($before, 'searchBefore');

        $str = false;
        $status = static::default($caseSensitive, 'searchCaseSensitive') << 1
            | static::default($reverse, 'searchReverse');
        switch ($status) {
            // $caseSensitive = false, $reverse = false
            case 0b00:
                $str = mb_stristr($this->str, $needle, $before, 'UTF-8');
                break;
            // $caseSensitive = false, $reverse = true
            case 0b01:
                $str = mb_strrichr($this->str, $needle, $before, 'UTF-8');
                break;
            // $caseSensitive = true, $reverse = false
            case 0b10:
                $str = mb_strstr($this->str, $needle, $before, 'UTF-8');
                break;
            // $caseSensitive = true, $reverse = true
            case 0b11:
                $str = mb_strrchr($this->str, $needle, $before, 'UTF-8');
                break;
        }

        if ($str === false) {
            return false;
        }

        return static::new($str);
    }

    /**
     * 返回子字符串之前的字符串.
     *
     * @param string|Str $needle  指定的字符串
     * @param bool|null  $contain 包含指定的字符串
     *
     * @throws StrEncodingException
     *
     * @return Str|false 新实例或 false
     */
    public function before($needle, bool $contain = null)
    {
        $needle = static::getStr($needle);

        if (static::default($contain, 'beforeContain')) {
            $pos = mb_strpos($this->str, $needle, 0, 'UTF-8');
            if ($pos === false) {
                return false;
            }
            $str = mb_substr($this->str, 0, $pos + mb_strlen($needle), 'UTF-8');

            return static::new($str);
        }

        $str = mb_strstr($this->str, $needle, true, 'UTF-8');
        if ($str === false) {
            return false;
        }

        return static::new($str);
    }

    /**
     * 返回子字符串之后的字符串.
     *
     * @param string|Str $needle  指定的字符串
     * @param bool|null  $contain 包含指定的字符串
     *
     * @throws StrEncodingException
     *
     * @return Str|false 新实例或 false
     */
    public function after($needle, bool $contain = null)
    {
        $needle = static::getStr($needle);

        $str = mb_strstr($this->str, $needle, false, 'UTF-8');
        if ($str === false) {
            return false;
        }

        if (!static::default($contain, 'afterContain')) {
            $str = mb_substr($str, mb_strlen($needle), null, 'UTF-8');
        }

        return static::new($str);
    }

    /**
     * 重复一个字符串.
     *
     * @see http://php.net/manual/zh/function.str-repeat.php
     *
     * @param int $num 重复次数
     * @param string|Str $separator 分隔符
     *
     * @throws StrEncodingException
     *
     * @return Str 新实例
     */
    public function repeat(int $num, $separator = null): self
    {
        if (is_null($separator)) {
            $str = str_repeat($this->str, $num);
        } else {
            $separator = static::getStr($separator);
            $str = implode($separator, array_fill(0, $num, $this->str));
        }

        return static::new($str);
    }

    /**
     * 子字符串替换.
     *
     * @see http://php.net/manual/zh/function.str-replace.php
     * @see http://php.net/manual/zh/function.str-ireplace.php
     *
     * @param string|Str $search
     * @param string|Str $replace
     * @param bool|null  $caseSensitive
     *
     * @throws StrEncodingException
     *
     * @return Str 新实例
     */
    public function replace($search, $replace, bool $caseSensitive = null): self
    {
        $search = static::getStr($search);
        $replace = static::getStr($replace);

        if (static::default($caseSensitive, 'replaceCaseSensitive')) {
            $str = str_replace($search, $replace, $this->str);
        } else {
            $str = str_ireplace($search, $replace, $this->str);
        }

        return static::new($str);
    }

    /**
     * 转为 base64 编码
     *
     * @see http://php.net/manual/zh/function.base64-encode.php
     *
     * @throws StrEncodingException
     *
     * @return Str 新实例
     */
    public function toBase64(): self
    {
        $str = base64_encode($this->str);

        return static::new($str);
    }

    /**
     * 返回字符串的 MD5 散列值
     *
     * @see http://php.net/manual/zh/function.md5.php
     *
     * @param bool|null $rawOutput
     *
     * @return string
     */
    public function toMd5(bool $rawOutput = null): string
    {
        $str = md5($this->str, static::default($rawOutput, 'toMd5RawOutput'));

        return $str;
    }

    /**
     * 返回字符串的 sha1 散列值
     *
     * @see http://php.net/manual/zh/function.sha1.php
     *
     * @param bool|null $rawOutput
     *
     * @return string
     */
    public function toSha1(bool $rawOutput = null): string
    {
        $str = sha1($this->str, static::default($rawOutput, 'toSha1RawOutput'));

        return $str;
    }

    /**
     * 创建密码的散列.
     *
     * @see http://php.net/manual/zh/function.password-hash.php
     *
     * @param int|null $algorithm
     * @param int|null $cost
     *
     * @throws StrEncodingException
     *
     * @return Str
     */
    public function passwordHash(int $algorithm = null, int $cost = null)
    {
        $algorithm = static::default($algorithm, 'passwordHashAlgorithm');
        $cost = static::default($cost, 'passwordHashCost');

        $hash = password_hash($this->str, $algorithm, ['cost' => $cost]);

        return static::new($hash);
    }

    /**
     * 去除字符串首尾处的空白字符（或者其他字符）.
     *
     * @see http://php.net/manual/zh/function.trim.php
     *
     * @param string|Str|null $characterMask
     *
     * @throws StrEncodingException
     *
     * @return Str 新实例
     */
    public function trim($characterMask = null): self
    {
        if (is_null($characterMask)) {
            return static::new(trim($this->str));
        }

        $characterMask = static::getStr($characterMask);

        return static::new(trim($this->str, $characterMask));
    }

    /**
     * 删除字符串开头的空白字符（或其他字符）.
     *
     * @see http://php.net/manual/zh/function.ltrim.php
     *
     * @param string|Str|null $characterMask
     *
     * @throws StrEncodingException
     *
     * @return Str 新实例
     */
    public function ltrim($characterMask = null): self
    {
        if (is_null($characterMask)) {
            return static::new(ltrim($this->str));
        }

        $characterMask = static::getStr($characterMask);

        return static::new(ltrim($this->str, $characterMask));
    }

    /**
     * 删除字符串开头的空白字符（或其他字符）.
     *
     * @see http://php.net/manual/zh/function.rtrim.php
     *
     * @param string|Str|null $characterMask
     *
     * @throws StrEncodingException
     *
     * @return Str 新实例
     */
    public function rtrim($characterMask = null): self
    {
        if (is_null($characterMask)) {
            return static::new(rtrim($this->str));
        }

        $characterMask = static::getStr($characterMask);

        return static::new(rtrim($this->str, $characterMask));
    }

    /**
     * 比较字符串.
     *
     * @see http://php.net/manual/zh/function.strcmp.php
     * @see http://php.net/manual/zh/function.strcasecmp.php
     * @see http://php.net/manual/zh/function.strncmp.php
     * @see http://php.net/manual/zh/function.strncasecmp.php
     *
     * @param string|Str $compStr       用于比较的字符串
     * @param bool|null  $caseSensitive 大小写敏感
     * @param int|null   $num           比较字节数，空则为全部，注：比较的是字节数而不是字符数
     *
     * @return int
     */
    public function comp($compStr, bool $caseSensitive = null, int $num = null): int
    {
        $compStr = static::getStr($compStr);
        $status = static::default($caseSensitive, 'compCaseSensitive') << 1 | !is_null($num);
        $diff = false;
        switch ($status) {
            // $caseSensitive = false, $num = null
            case 0b00:
                $diff = strcasecmp($this->str, $compStr);
                break;
            // $caseSensitive = false, $num != null
            case 0b01:
                $diff = strncasecmp($this->str, $compStr, $num);
                break;
            // $caseSensitive = true, $num = null
            case 0b10:
                $diff = strcmp($this->str, $compStr);
                break;
            // $caseSensitive = true, $num != null
            case 0b11:
                $diff = strncmp($this->str, $compStr, $num);
                break;
        }

        return $diff;
    }

    /**
     * 使用自然排序算法比较字符串.
     *
     * @see http://php.net/manual/zh/function.strnatcmp.php
     * @see http://php.net/manual/zh/function.strnatcasecmp.php
     *
     * @param string|Str $compStr       用于比较的字符串
     * @param bool|null  $caseSensitive 大小写敏感
     *
     * @return int
     */
    public function natComp($compStr, bool $caseSensitive = null): int
    {
        if (static::default($caseSensitive, 'natCompCaseSensitive')) {
            return strnatcmp($this->str, $compStr);
        }

        return strnatcasecmp($this->str, $compStr);
    }

    /**
     * 判断字符串是否相等.
     *
     * @param string|Str $str
     *
     * @return bool
     */
    public function equals($str)
    {
        $str = static::getStr($str);

        return $this->str === $str;
    }

    /**
     * 反转字符串.
     *
     * @throws StrEncodingException
     *
     * @return Str 新实例
     */
    public function reverse(): self
    {
        $reverseStr = '';
        for ($i = mb_strlen($this->str); $i >= 0; $i--) {
            $reverseStr .= mb_substr($this->str, $i, 1);
        }

        return static::new($reverseStr);
    }

    /**
     * 字符串转为字符数组.
     *
     * @return array
     */
    public function toArray(): array
    {
        $str = $this->str;
        $len = mb_strlen($str);
        $array = [];

        while ($len) {
            $array[] = mb_substr($str, 0, 1, 'UTF-8');
            $str = mb_substr($str, 1, $len, 'UTF-8');
            $len = mb_strlen($str);
        }

        return $array;
    }

    /**
     * 字符串转为 Ary 数组.
     *
     * @return Ary
     */
    public function toAry(): Ary
    {
        return Ary::new($this->toArray());
    }

    /**
     * 返回字符串长度.
     *
     * @return int
     */
    public function len(): int
    {
        return mb_strlen($this->str);
    }

    /**
     * 返回字符串长度.
     *
     * @return int
     */
    public function count(): int
    {
        return mb_strlen($this->str);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->str;
    }

    /**
     * 返回一个新实例.
     *
     * @param string      $str          实例包含的字符串
     * @param string|null $fromEncoding 字符串的原编码
     *
     * @throws StrEncodingException
     *
     * @return Str 新实例
     */
    public static function new(string $str, string $fromEncoding = null): self
    {
        return new static($str, $fromEncoding);
    }

    /**
     * 将字符串转为 UTF-8 编码
     *
     * @param string $str          原字符串
     * @param string $fromEncoding 原编码格式
     *
     * @return string UTF-8 编码的字符串
     */
    public static function convert(string $str, string $fromEncoding): string
    {
        return mb_convert_encoding($str, 'UTF-8', $fromEncoding);
    }

    /**
     * @param $str
     *
     * @return string
     */
    protected static function getStr($str): string
    {
        if (is_string($str)) {
            return $str;
        } elseif ($str instanceof static) {
            return $str->str();
        }

        return (string) $str;
    }

    /**
     * 设置类方法的默认值
     *
     * @param array $default
     *
     * @return bool
     */
    public static function setDefault(array $default): bool
    {
        foreach ($default as $key => $val) {
            static::$default[$key] = $val;
        }

        return true;
    }

    /**
     * 若 $val 不为 null 则返回 $val
     * 若 $val 为 null 则直接返回 $default 数组中以 $key 为键名的值
     *
     * @param mixed $val
     * @param string|null     $key
     *
     * @return mixed|null
     */
    protected static function default($val, string $key = null)
    {
        if (is_null($val)) {
            return static::$default[$key] ?? null;
        }

        return $val;
    }
}
