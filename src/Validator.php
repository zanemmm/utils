<?php
/**
 * Validator 类.
 *
 * 提供各种常用的验证器
 *
 * @license    MIT
 *
 * @link       https://github.com/zanemmm/utils
 */

namespace Zane\Utils;

use Zane\Utils\Exceptions\ValidatorNotFoundException;

class Validator
{
    protected static $validators = [];

    /**
     * 验证字符为 '1'、'true'、'on'、'yes' 之一，不区分大小写，忽略字符串左右的空白符.
     *
     * @param string $input
     *
     * @return bool
     */
    public static function accepted(string $input): bool
    {
        return filter_var($input, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * 验证字符为 '1'、'true'、'on'、'yes'、'0'、'false'、'off'、'no' 之一，不区分大小写，忽略字符串左右的空白符.
     *
     * @param string $input
     *
     * @return bool
     */
    public static function boolean(string $input): bool
    {
        $bool = filter_var($input, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if (is_null($bool)) {
            return false;
        }

        return true;
    }

    /**
     * 验证字符串只包含英文字母.
     *
     * @see http://php.net/manual/zh/function.ctype-alpha.php
     *
     * @param string $input
     *
     * @return bool
     */
    public static function alpha(string $input): bool
    {
        return ctype_alpha($input);
    }

    /**
     * 验证字符串只包含英文字母和数字.
     *
     * @see http://php.net/manual/zh/function.ctype-alnum.php
     *
     * @param string $input
     *
     * @return bool
     */
    public static function alphaNum(string $input): bool
    {
        return ctype_alnum($input);
    }

    /**
     * 验证字符串只包含数字，可指定数字长度.
     *
     * @param string   $input
     * @param int|null $len   指定数字长度
     *
     * @return bool
     */
    public static function num(string $input, int $len = null): bool
    {
        $num = ctype_digit($input);
        if ($num === false || is_null($len)) {
            return $num;
        }

        if (strlen($input) === $len) {
            return true;
        }

        return false;
    }

    /**
     * 验证字符串为数值类型，包括整型和浮点型.
     *
     * @see http://php.net/manual/zh/function.is-numeric.php
     *
     * @param string $input
     *
     * @return bool
     */
    public static function numeric(string $input): bool
    {
        return is_numeric($input);
    }

    /**
     * 验证字符串为 int 类型，注意：整型的范围
     * 数字左右的空白符，如：空格，Tab 不会影响验证
     *
     * @param $input
     *
     * @return bool
     */
    public static function int(string $input): bool
    {
        $int = filter_var($input, FILTER_VALIDATE_INT);

        if ($int !== false) {
            return true;
        }

        return false;
    }

    /**
     * 验证字符串为 int 类型并且小于等于 $max，注意：整型的范围
     * 数字左右的空白符，如：空格，Tab 不会影响验证
     *
     * @param string   $input
     * @param int|null $max
     *
     * @return bool
     */
    public static function intMax(string $input, int $max): bool
    {
        $options = ['options' => ['max_range' => $max]];
        $int = filter_var($input, FILTER_VALIDATE_INT, $options);

        if ($int !== false) {
            return true;
        }

        return false;
    }

    /**
     * 验证字符串为 int 类型并且大于等于 $min，注意：整型的范围
     * 数字左右的空白符，如：空格，Tab 不会影响验证
     *
     * @param string $input
     * @param int    $min
     *
     * @return bool
     */
    public static function intMin(string $input, int $min): bool
    {
        $options = ['options' => ['min_range' => $min]];
        $int = filter_var($input, FILTER_VALIDATE_INT, $options);

        if ($int !== false) {
            return true;
        }

        return false;
    }

    /**
     * 验证字符串为 int 类型，大于等于 $min，小于等于 $max，注意：整型的范围
     * 数字左右的空白符，如：空格，Tab 不会影响验证
     *
     * @param string $input
     * @param int    $min
     * @param int    $max
     *
     * @return bool
     */
    public static function intBetween(string $input, int $min, int $max): bool
    {
        $options = [
            'options' => [
                'min_range' => $min,
                'max_range' => $max,
            ],
        ];
        $int = filter_var($input, FILTER_VALIDATE_INT, $options);

        if ($int !== false) {
            return true;
        }

        return false;
    }

    /**
     * 验证字符串为 float 类型，注意：浮点型的范围
     * float 方法兼容 int 方法
     * 数字左右的空白符，如：空格，Tab 不会影响验证
     *
     * @param string $input
     *
     * @return bool
     */
    public static function float(string $input): bool
    {
        $float = filter_var($input, FILTER_VALIDATE_FLOAT);

        if ($float !== false) {
            return true;
        }

        return false;
    }

    /**
     * 验证字符串为 float 类型并且小于等于 $max，注意：浮点型的范围
     * 数字左右的空白符，如：空格，Tab 不会影响验证
     *
     * @param string $input
     * @param float  $max
     *
     * @return bool
     */
    public static function floatMax(string $input, float $max): bool
    {
        $float = filter_var($input, FILTER_VALIDATE_FLOAT);

        if ($float !== false && $float <= $max) {
            return true;
        }

        return false;
    }

    /**
     * 验证字符串为 float 类型并且大于等于 $min，注意：浮点型的范围
     * 数字左右的空白符，如：空格，Tab 不会影响验证
     *
     * @param string $input
     * @param float  $min
     *
     * @return bool
     */
    public static function floatMin(string $input, float $min): bool
    {
        $float = filter_var($input, FILTER_VALIDATE_FLOAT);

        if ($float !== false && $float >= $min) {
            return true;
        }

        return false;
    }

    /**
     * 验证字符串为 float 类型，大于等于 $min，小于等于 $max，注意：浮点型的范围
     * 数字左右的空白符，如：空格，Tab 不会影响验证
     *
     * @param string $input
     * @param float  $min
     * @param float  $max
     *
     * @return bool
     */
    public static function floatBetween(string $input, float $min, float $max): bool
    {
        $float = filter_var($input, FILTER_VALIDATE_FLOAT);

        if ($float !== false && $float >= $min && $float <= $max) {
            return true;
        }

        return false;
    }

    /**
     * 验证字符串为 json 格式，注意： json 格式嵌套深度不能超过 512.
     *
     * @see http://php.net/manual/zh/function.json-decode.php
     *
     * @param string $input
     *
     * @return bool
     */
    public static function json(string $input): bool
    {
        json_decode($input, true, 512);

        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * 验证字符串为合法 IP，IPv4 和 IPv6 都可以.
     *
     * @param string $input
     *
     * @return bool
     */
    public static function ip(string $input): bool
    {
        $ip = filter_var($input, FILTER_VALIDATE_IP);

        if ($ip !== false) {
            return true;
        }

        return false;
    }

    /**
     * 验证字符串为合法 IPv4 地址
     *
     * @param string $input
     *
     * @return bool
     */
    public static function ipv4(string $input): bool
    {
        $ip = filter_var($input, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);

        if ($ip !== false) {
            return true;
        }

        return false;
    }

    /**
     * 验证字符串为合法 IPv6 地址
     *
     * @param string $input
     *
     * @return bool
     */
    public static function ipv6(string $input): bool
    {
        $ip = filter_var($input, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);

        if ($ip !== false) {
            return true;
        }

        return false;
    }

    /**
     * 验证字符串为域名（域名最后不能加 . 号），不支持中文域名.
     *
     * @param string $input
     *
     * @return bool
     */
    public static function domain(string $input)
    {
        if (preg_match(
            '/^([a-zA-Z0-9][-a-zA-Z0-9]{0,61}[a-zA-Z0-9]|[a-zA-Z0-9])'
            ."(.([a-zA-Z0-9][-a-zA-Z0-9]{0,61}[a-zA-Z0-9]|[a-zA-Z0-9]))*\.[a-zA-Z]{2,62}$/",
            $input
        )) {
            return true;
        }

        return false;
    }

    /**
     * 验证域名合法且能通过 DNS 解析
     * 注：该方法会进行 DNS 解析，需要消耗网络资源.
     *
     * @param string $input
     *
     * @return bool
     */
    public static function activeDomain(string $input): bool
    {
        if (static::domain($input)) {
            return checkdnsrr($input, 'ANY');
        }

        return false;
    }

    /**
     * 验证字符串为合法 URL，默认允许任何协议.
     *
     * @param string          $input
     * @param string|string[] $schemes 协议名称如： http、https，可传递允许协议名称数组
     *
     * @return bool
     */
    public static function url(string $input, $schemes = null): bool
    {
        $url = filter_var($input, FILTER_VALIDATE_URL);
        if ($url === false) {
            return false;
        }

        if (!is_null($schemes)) {
            if (!is_array($schemes)) {
                $schemes = [$schemes];
            }
            foreach ($schemes as $scheme) {
                $scheme .= '://';
                $pos = strpos($input, $scheme);
                // 找到对应协议
                if ($pos === 0) {
                    return true;
                }
            }
            // 没有找到对应协议
            return false;
        }

        return true;
    }

    /**
     * 验证字符串为邮箱，不支持支持中文域名和中文用户名.
     *
     * @param string $input
     *
     * @return bool
     */
    public static function email(string $input): bool
    {
        $email = filter_var($input, FILTER_VALIDATE_EMAIL);
        if ($email !== false) {
            return true;
        }

        return false;
    }

    /**
     * 验证字符串为国内手机号.
     *
     * @param string $input
     *
     * @return bool
     */
    public static function phone(string $input): bool
    {
        if (preg_match("/^1[34578]{1}\d{9}$/", $input)) {
            return true;
        }

        return false;
    }

    /**
     * 验证国内身份证号码
     *
     * @param string $input
     *
     * @return bool
     */
    public static function IDCard(string $input): bool
    {
        if (strlen($input) !== 18 || !static::num(substr($input, 0, 17))) {
            return false;
        }

        // 加权因子
        $factor = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
        // 校验码对应值
        $code = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];
        // 校验和
        $checksum = 0;
        for ($i = 0; $i < 17; $i++) {
            $checksum += substr($input, $i, 1) * $factor[$i];
        }
        // 最后身份证最后一位数字
        $last = $code[$checksum % 11];

        if ($input[17] == $last) {
            return true;
        }

        return false;
    }

    /**
     * 验证国内身份证号码且年龄小于等于 $maxAge.
     *
     * @param string $input
     * @param int    $maxAge
     *
     * @return bool
     */
    public static function IDCardMaxAge(string $input, int $maxAge): bool
    {
        if (static::IDCard($input)) {
            $year = date('Y') - substr($input, 6, 4);
            $monthDay = date('md') - substr($input, 10, 4);

            return ($year < $maxAge || $year == $maxAge && $monthDay <= 0) ? true : false;
        }

        return false;
    }

    /**
     * 验证国内身份证号码且年龄大于等于 $minAge.
     *
     * @param string $input
     * @param int    $minAge
     *
     * @return bool
     */
    public static function IDCardMinAge(string $input, int $minAge): bool
    {
        if (static::IDCard($input)) {
            $year = date('Y') - substr($input, 6, 4);
            $monthDay = date('md') - substr($input, 10, 4);

            return ($year > $minAge || $year == $minAge && $monthDay >= 0) ? true : false;
        }

        return false;
    }

    /**
     * 验证国内身份证号码且年龄在 $minAge 和 $maxAge 之间.
     *
     * @param string $input
     * @param int    $minAge
     * @param int    $maxAge
     *
     * @return bool
     */
    public static function IDCardBetween(string $input, int $minAge, int $maxAge)
    {
        if (static::IDCard($input)) {
            $year = date('Y') - substr($input, 6, 4);
            $monthDay = date('md') - substr($input, 10, 4);
            $minAgeOk = ($year > $minAge || $year == $minAge && $monthDay >= 0) ? true : false;
            $maxAgeOk = ($year < $maxAge || $year == $maxAge && $monthDay <= 0) ? true : false;
            if ($minAgeOk && $maxAgeOk) {
                return true;
            }
        }

        return false;
    }

    /**
     * 设置一个自定义的验证器.
     *
     * @param $key
     * @param callable $fn
     */
    public static function set($key, callable $fn)
    {
        static::$validators[$key] = $fn;
    }

    /**
     * 获取指定验证器.
     *
     * @param string $key
     *
     * @throws ValidatorNotFoundException
     *
     * @return callable
     */
    public static function get(string $key): callable
    {
        // 若 $key 与当前类中方法相同，则构造一个该方法的闭包并返回
        if (method_exists(__CLASS__, $key)) {
            return function (...$arguments) use ($key) {
                return static::{$key}(...$arguments);
            };
        }

        if (!isset(static::$validators[$key])) {
            throw new ValidatorNotFoundException();
        }

        return static::$validators[$key];
    }

    /**
     * 调用自定义验证器，不存在时会抛出异常.
     *
     * @param string $key
     * @param array  $arguments
     *
     * @throws ValidatorNotFoundException
     *
     * @return mixed
     */
    public static function __callStatic(string $key, array $arguments)
    {
        if (!isset(static::$validators[$key])) {
            throw new ValidatorNotFoundException();
        }

        return (static::$validators[$key])(...$arguments);
    }
}
