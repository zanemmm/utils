<?php
/**
 * Ary 类.
 *
 * 提供各种方便的方法操作数组
 *
 * @license    MIT
 *
 * @link       https://github.com/zanemmm/utils
 */

namespace Zane\Utils;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Zane\Utils\Exceptions\AryKeyTypeException;
use Zane\Utils\Exceptions\AryOutOfRangeException;

class Ary implements IteratorAggregate, ArrayAccess, Countable, JsonSerializable
{
    protected static $default = [
        'keysStrict'           => true,
        'limitPreserveKeys'    => false,
        'tailPreserveKeys'     => false,
        'slicePreserveKeys'    => false,
        'chunkPreserveKeys'    => false,
        'existStrict'          => true,
        'sortAsc'              => true,
        'sortPreserveKeys'     => false,
        'sortFlag'             => SORT_REGULAR,
        'userSortPreserveKeys' => false,
        'natSortCaseSensitive' => true,
        'keySortAsc'           => true,
        'keySortFlag'          => SORT_REGULAR,
        'uniqueFlag'           => SORT_STRING,
        'popGetElement'        => true,
        'shiftGetElement'      => true,
        'appendPreserveValues' => false,
        'searchStrict'         => true,
        'beforePreserveKeys'   => true,
        'afterPreserveKeys'    => true,
        'beforeContain'        => false,
        'afterContain'         => false,
        'beforeKeyContain'     => false,
        'afterKeyContain'      => false,
        'intersectCompKey'     => false,
        'diffCompKey'          => false,
        'joinGlue'             => '',
        'eachRecursive'        => false,
        'filterFlag'           => 0,
        'flatPreserveKeys'     => false,
        'toArrayRecursive'     => false,
        'toJsonOptions'        => JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
        'toJsonDepth'          => 512,
        'fromJsonDepth'        => 512,
        'fromJsonOptions'      => 0,
    ];

    private $val;

    public function __construct(array $array = [])
    {
        $this->val = $array;
    }

    /**
     * 获取或设置该实例的数组.
     *
     * @param array|null $array 为空时获取实例的数组，非空时设置实例的数组
     *
     * @return $this|array 原实例或数组
     */
    public function val(array $array = null)
    {
        if (is_null($array)) {
            return $this->val;
        }

        $this->val = $array;

        return $this;
    }

    /**
     * 使用「点」式语法从深度嵌套数组中取回指定的值
     *
     * @see https://laravel.com/docs/5.6/helpers#method-array-get
     *
     * @param string     $dotKey  符合「点」式语法的键名
     * @param mixed|null $default 默认值
     *
     * @return mixed
     */
    public function get(string $dotKey = null, $default = null)
    {
        if (is_null($dotKey)) {
            return $this->val;
        }

        if (array_key_exists($dotKey, $this->val)) {
            return $this->val[$dotKey];
        }

        $keys = explode('.', $dotKey);
        $array = $this->val;

        foreach ($keys as $key) {
            if (is_array($array) && array_key_exists($key, $array)) {
                $array = $array[$key];
            } elseif ($array instanceof static && array_key_exists($key, $array->val())) {
                $array = $array[$key];
            } else {
                return $default;
            }
        }

        return $array;
    }

    /**
     * 使用「点」式语法从深度嵌套数组中设置指定的值
     *
     * @see https://laravel.com/docs/5.6/helpers#method-array-set
     *
     * @param string $dotKey 符合「点」式语法的键名
     * @param mixed  $val    要设置的值
     *
     * @return Ary 原实例
     */
    public function set(string $dotKey, $val): self
    {
        $keys = explode('.', $dotKey);
        $array = &$this->val;

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (!isset($array[$key]) || !static::accessible($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $val;

        return $this;
    }

    /**
     * 使用「点」式语法判断是否存在指定键名的值，多个键名要全部存在才返回 true.
     *
     * @see https://laravel.com/docs/5.6/helpers#method-array-has
     *
     * @param string[] $dotKeys 符合「点」式语法的键名
     *
     * @return bool
     */
    public function has(string ...$dotKeys): bool
    {
        foreach ($dotKeys as $dotKey) {
            if (array_key_exists($dotKey, $this->val)) {
                continue;
            }

            $keys = explode('.', $dotKey);
            $array = $this->val;

            foreach ($keys as $key) {
                if (is_array($array) && array_key_exists($key, $array)) {
                    $array = $array[$key];
                } elseif ($array instanceof static && array_key_exists($key, $array->val())) {
                    $array = $array[$key];
                } else {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * 返回只包含指定键名的新实例.
     *
     * @param array ...$keys
     *
     * @return Ary 新实例
     */
    public function only(...$keys)
    {
        $val = array_intersect_key($this->val, array_flip($keys));

        return static::new($val);
    }

    /**
     * 获取实例数组中的全部值
     *
     * @see http://php.net/manual/zh/function.array-values.php
     *
     * @return Ary 新实例
     */
    public function values(): self
    {
        return static::new(array_values($this->val));
    }

    /**
     * 获取实例数组中的键名.
     *
     * @see http://php.net/manual/zh/function.array-keys.php
     *
     * @param mixed|null $searchValue 空则返回全部键，非空则返回对应 $searchValue 值的键
     * @param bool|null  $strict      为真时数组中的值与 $searchValue 采用严格比较
     *
     * @return Ary 新实例
     */
    public function keys($searchValue = null, bool $strict = null): self
    {
        // 实参个数为零时直接调用 array_keys 函数
        // 若此时调用 array_keys 函数时带入 $searchValue 和 $strict 参数会导致 array_keys 返回 数组值为 null 的键名, 与预期结果不符
        $args = func_num_args();
        if ($args === 0) {
            return static::new(array_keys($this->val));
        }

        return static::new(
            array_keys(
                $this->val,
                $searchValue,
                static::default($strict, 'keysStrict')
            )
        );
    }

    /**
     * 返回一个全部键名大写的新实例.
     *
     * @return Ary 新实例
     */
    public function keyToUpperCase(): self
    {
        $val = array_change_key_case($this->val, CASE_UPPER);

        return static::val($val);
    }

    /**
     * 返回一个全部键名小写的新实例.
     *
     * @return Ary 新实例
     */
    public function keyToLowerCase()
    {
        $val = array_change_key_case($this->val, CASE_LOWER);

        return static::val($val);
    }

    /**
     * 返回两个实例，一个包含原本实例的键，另一个包含原本实例的值
     *
     * @return Ary[]
     */
    public function divide(): array
    {
        return [$this->keys(), $this->values()];
    }

    /**
     * 获取实例数组中第一个元素的值
     *
     * @return mixed
     */
    public function first()
    {
        return reset($this->val);
    }

    /**
     * 获取实例数组中最后一个元素的值
     *
     * @return mixed
     */
    public function last()
    {
        return end($this->val);
    }

    /**
     * 获取实例数组中第一个元素的键.
     *
     * @return int|null|string
     */
    public function firstKey()
    {
        $this->first();

        return key($this->val);
    }

    /**
     * 获取实例数组中最后一个元素的键.
     *
     * @return int|null|string
     */
    public function lastKey()
    {
        $this->last();

        return key($this->val);
    }

    /**
     * 获取实例数组中前 $len 个元素组成的新 Ary 实例.
     *
     * @see \Zane\Utils\Ary::slice()
     *
     * @param int       $len          获取元素的个数，小于等于 0 则返回空数组的实例，大于等于实例数组的长度则返回原数组(索引可能会改变具体看 $preserveKeys 参数)的新实例
     * @param bool|null $preserveKeys 为 true 则数字索引保持不变，false 则会重置数组的数字索引，字符串键名始终保持不变
     *
     * @return Ary 新实例
     */
    public function limit(int $len, bool $preserveKeys = null): self
    {
        if ($len <= 0) {
            return static::new([]);
        }

        $val = array_slice($this->val, 0, $len, static::default($preserveKeys, 'limitPreserveKeys'));

        return static::new($val);
    }

    /**
     * 获取实例数组中后 $len 个元素组成的新 Ary 实例.
     *
     * @see \Zane\Utils\Ary::slice()
     *
     * @param int       $len          获取元素的个数，小于等于 0 则返回空数组的实例，大于等于实例数组的长度则返回原数组(索引可能会改变具体看 $preserveKeys 参数)的新实例
     * @param bool|null $preserveKeys 为 true 则数字索引保持不变，false 则会重置数组的数字索引，字符串键名始终保持不变
     *
     * @return Ary 新实例
     */
    public function tail(int $len, bool $preserveKeys = null): self
    {
        if ($len <= 0) {
            return static::new([]);
        }

        $val = array_slice($this->val, -$len, null, static::default($preserveKeys, 'tailPreserveKeys'));

        return static::new($val);
    }

    /**
     * 从实例数组中取出一段并返回新的实例.
     *
     * @see http://php.net/manual/zh/function.array-slice.php
     *
     * @param int       $offset       起始偏移量
     * @param int       $len          长度
     * @param bool|null $preserveKeys 为 true 则数字索引保持不变，false 则会重置数组的数字索引，字符串键名始终保持不变
     *
     * @return Ary 新实例
     */
    public function slice(int $offset, int $len = null, bool $preserveKeys = null): self
    {
        $val = array_slice($this->val, $offset, $len, static::default($preserveKeys, 'slicePreserveKeys'));

        return static::new($val);
    }

    /**
     * 将一个实例数组分割为多个并返回多个新实例.
     *
     * @see http://php.net/manual/zh/function.array-chunk.php
     *
     * @param int       $size         每个新实例数组的大小，最后一个实例数组可能小于 $size
     * @param bool|null $preserveKeys 为 true 则数字索引保持不变，false 则会重置数组的数字索引
     *
     * @return Ary 新实例
     */
    public function chunk(int $size, bool $preserveKeys = null): self
    {
        $chunks = array_chunk($this->val, $size, static::default($preserveKeys, 'chunkPreserveKeys'));

        $val = [];
        foreach ($chunks as $chunk) {
            $val[] = static::new($chunk);
        }

        return static::new($val);
    }

    /**
     * 返回由实例数组中指定的一列所组成的新实例.
     *
     * @see http://php.net/manual/zh/function.array-column.php
     *
     * @param mixed $columnKey 需要返回值的列，它可以是索引数组的列索引，或者是关联数组的列的键，也可以是属性名，为 NULL 时返回整个数组
     * @param null  $indexKey  作为返回数组的索引或键的列
     *
     * @return Ary 新实例
     */
    public function column($columnKey, $indexKey = null): self
    {
        // 当键名为 val 会与数组属性 val 冲突，因为在 PHP 中同一个类的对象即使不是同一个实例也可以互相访问对方的私有与受保护成员
        // 所以并不会触发 __get() 方法，来获取数组中的值，而是直接将整个 val 属性返回
        // 为了解决这个问题采用匿名类的方式将 array_column 函数从 Ary 类调用改变到在匿名类中调用
        if ($columnKey === 'val' || $indexKey === 'val') {
            $object = new class() {
                public function do($array, $columnKey, $indexKey = null)
                {
                    return array_column($array, $columnKey, $indexKey);
                }
            };

            return static::new($object->do($this->val, $columnKey, $indexKey));
        }

        return static::new(array_column($this->val, $columnKey, $indexKey));
    }

    /**
     * column 的加强版，能获取多列，但不会获取对象的属性.
     *
     * @param array|null $columnKeys 包含指定键的数组
     * @param mixed|null $indexKey   作为返回数组的索引或键的列
     *
     * @throws AryKeyTypeException
     *
     * @return Ary 新实例
     */
    public function select(array $columnKeys = null, $indexKey = null): self
    {
        if (!is_null($indexKey) && !static::isValidKey($indexKey)) {
            throw new AryKeyTypeException();
        }

        if (empty($columnKeys)) {
            return static::new($this->val);
        }

        $array = [];
        foreach ($this->val as $rowKey => $rowVal) {
            if (is_array($rowVal) || $rowVal instanceof static) {
                $row = [];
                foreach ($rowVal as $colKey => $colVal) {
                    if (in_array($colKey, $columnKeys)) {
                        $row[$colKey] = $colVal;
                    }
                }
                if (is_null($indexKey) || !isset($rowVal[$indexKey])) {
                    $array[] = $row;
                } else {
                    $array[$rowVal[$indexKey]] = $row;
                }
            }
        }

        return static::new($array);
    }

    /**
     * 返回与指定列条件相符的所有行组成的实例.
     *
     * @param string|int $columnKey 指定列键名
     * @param string     $operator  比较符，包括： >、>=、==、===、<、<=
     * @param mixed      $expected  比较值
     *
     * @throws AryKeyTypeException
     *
     * @return Ary 新的实例
     */
    public function where($columnKey, string $operator, $expected): self
    {
        if (!static::isValidKey($columnKey)) {
            throw new AryKeyTypeException();
        }

        $fn = function ($row) use ($columnKey, $operator, $expected) {
            if (!is_array($row) && !($row instanceof static)) {
                return false;
            }
            $val = $row[$columnKey];
            switch ($operator) {
                case '>':
                    return $val > $expected;
                case '>=':
                    return $val >= $expected;
                case '==':
                    return $val == $expected;
                case '===':
                    return $val === $expected;
                case '<':
                    return $val < $expected;
                case '<=':
                    return $val <= $expected;
                default:
                    return false;
            }
        };

        $val = array_filter($this->val, $fn);

        return static::new($val);
    }

    /**
     * 统计实例数组中值的出现次数,
     * 返回一个键为原数组的值，值为原数组值出现的次数的新实例.
     *
     * @see http://php.net/manual/zh/function.array-count-values.php
     *
     * @return Ary 新实例
     */
    public function countValues(): self
    {
        return static::new(array_count_values($this->val));
    }

    /**
     * 交换实例数组中的键和值并返回新的实例.
     *
     * @see http://php.net/manual/zh/function.array-flip.php
     *
     * @return Ary 新实例
     */
    public function flip(): self
    {
        return static::new(array_flip($this->val));
    }

    /**
     * 检查实例数组中是否存在某个值
     *
     * @see http://php.net/manual/zh/function.in-array.php
     *
     * @param mixed     $needle 要检查的值
     * @param bool|null $strict 是否严格比较
     *
     * @return bool
     */
    public function exist($needle, bool $strict = null): bool
    {
        return in_array($needle, $this->val, static::default($strict, 'existStrict'));
    }

    /**
     * 检查实例数组里是否有指定的键名或索引.
     *
     * @see http://php.net/manual/zh/function.array-key-exists.php
     *
     * @param int|string $key 要检查的键
     *
     * @throws AryKeyTypeException
     *
     * @return bool
     */
    public function existKey($key): bool
    {
        if (!static::isValidKey($key)) {
            throw new AryKeyTypeException();
        }
        // isset 性能比 array_key_exists 高，但 isset 在数组成员的值为 null 时会返回 false
        // 所以利用 || 运算符的短路特性，仅当 isset 为 false 时使用 array_key_exists 判断是否存在该键
        return isset($this->val[$key]) || array_key_exists($key, $this->val);
    }

    /**
     * 检查实例数组存在指定键名的值，且值不为 null.
     *
     * @see http://php.net/manual/zh/function.isset.php
     *
     * @param int|string $key
     *
     * @throws AryKeyTypeException
     *
     * @return bool
     */
    public function isSet($key): bool
    {
        if (!static::isValidKey($key)) {
            throw new AryKeyTypeException();
        }

        return isset($this->val[$key]);
    }

    /**
     * 判断实例数组是否为关联数组.
     *
     * @return bool
     */
    public function isAssoc()
    {
        $keys = array_keys($this->val);

        return array_keys($keys) !== $keys;
    }

    /**
     * 对实例数组的值进行排序.
     *
     * @see http://php.net/manual/zh/function.sort.php
     * @see http://php.net/manual/zh/function.rsort.php
     * @see http://php.net/manual/zh/function.asort.php
     * @see http://php.net/manual/zh/function.arsort.php
     *
     * @param bool|null $asc          true为升序，false为降序
     * @param bool|null $preserveKeys true 则保持索引关联，false 则以数字索引重置
     * @param int|null  $flag         排序类型标记，参见 PHP 手册 sort 函数
     *
     * @return Ary 原实例
     */
    public function sort(bool $asc = null, bool $preserveKeys = null, int $flag = null): self
    {
        // 通过将 $asc 左移一位并或上 $preserveKeys 得出范围在 0b00 到 0b11 的 $status
        // 通过 $status 的值选择不同的排序函数
        $status = static::default($asc, 'sortAsc') << 1 | static::default($preserveKeys, 'sortPreserveKeys');
        $flag = static::default($flag, 'sortFlag');

        switch ($status) {
            // $asc = false, $preserveKeys = false
            case 0b00:
                rsort($this->val, $flag);
                break;
            // $asc = false, $preserveKeys = true
            case 0b01:
                arsort($this->val, $flag);
                break;
            // $asc = true, $preserveKeys = false
            case 0b10:
                sort($this->val, $flag);
                break;
            // $asc = true, $preserveKeys = true
            case 0b11:
                asort($this->val, $flag);
                break;
        }

        return $this;
    }

    /**
     * 使用用户自定义的比较函数对数组中的值进行排序.
     *
     * @see http://php.net/manual/zh/function.usort.php
     * @see http://php.net/manual/zh/function.uasort.php
     *
     * @param callable  $fn           比较函数
     * @param bool|null $preserveKeys true 则保持索引关联，false 则以数字索引重置
     *
     * @return Ary 原实例
     */
    public function userSort(callable $fn, bool $preserveKeys = null): self
    {
        if (static::default($preserveKeys, 'userSortPreserveKeys')) {
            uasort($this->val, $fn);
        } else {
            usort($this->val, $fn);
        }

        return $this;
    }

    /**
     * 用“自然排序”算法对实例数组的值进行排序.
     *
     * @see http://php.net/manual/zh/function.natsort.php
     * @see http://php.net/manual/zh/function.natcasesort.php
     *
     * @param bool|null $caseSensitive 大小写敏感
     *
     * @return Ary 原实例
     */
    public function natSort(bool $caseSensitive = null): self
    {
        if (static::default($caseSensitive, 'natSortCaseSensitive')) {
            natsort($this->val);
        } else {
            natcasesort($this->val);
        }

        return $this;
    }

    /**
     * 对实例数组按照键名排序.
     *
     * @see http://php.net/manual/zh/function.ksort.php
     *
     * @param bool|null $asc  true 为升序，false 为降序
     * @param int|null  $flag 排序类型标记，参见 PHP 手册 sort 函数
     *
     * @return Ary 原实例
     */
    public function keySort(bool $asc = null, int $flag = null): self
    {
        if (static::default($asc, 'keySortAsc')) {
            ksort($this->val, static::default($flag, 'keySortFlag'));
        } else {
            krsort($this->val, static::default($flag, 'keySortFlag'));
        }

        return $this;
    }

    /**
     * 使用用户自定义的比较函数对数组中的键名进行排序.
     *
     * @see http://php.net/manual/zh/function.uksort.php
     *
     * @param callable $fn 比较函数
     *
     * @return Ary 原实例
     */
    public function userKeySort(callable $fn): self
    {
        uksort($this->val, $fn);

        return $this;
    }

    /**
     * 返回实例数组中的最大值
     *
     * @return mixed
     */
    public function max()
    {
        return static::new($this->val)->sort(false, false, SORT_REGULAR)->first();
    }

    /**
     * 返回实例数组中的最小值
     *
     * @return mixed
     */
    public function min()
    {
        return static::new($this->val)->sort(true, false, SORT_REGULAR)->first();
    }

    /**
     * 返回实例数组中的最大值的键名.
     *
     * @return mixed
     */
    public function maxKey()
    {
        return static::new($this->val)->sort(false, true, SORT_REGULAR)->firstKey();
    }

    /**
     * 返回实例数组中的最小值的键名.
     *
     * @return mixed
     */
    public function minKey()
    {
        return static::new($this->val)->sort(true, true, SORT_REGULAR)->firstKey();
    }

    /**
     * 打乱实例数组顺序.
     *
     * @see http://php.net/manual/zh/function.shuffle.php
     *
     * @return Ary 原实例
     */
    public function shuffle(): self
    {
        shuffle($this->val);

        return $this;
    }

    /**
     * 移除实例数组中重复的值
     *
     * @see http://php.net/manual/zh/function.array-unique.php
     *
     * @param int|null $flag 排序类型标记
     *
     * @return Ary 新实例
     */
    public function unique(int $flag = null): self
    {
        $val = array_unique($this->val, static::default($flag, 'uniqueFlag'));

        return static::new($val);
    }

    /**
     * 返回单元顺序相反的实例数组.
     *
     * @see http://php.net/manual/zh/function.array-reverse.php
     *
     * @return Ary 新实例
     */
    public function reverse(): self
    {
        $val = array_reverse($this->val);

        return static::new($val);
    }

    /**
     * 移除实例数组中指定键名的值
     *
     * @param array ...$keys 指定键名
     *
     * @return Ary 新实例
     */
    public function except(...$keys): self
    {
        $val = array_diff_key($this->val, array_flip($keys));

        return static::new($val);
    }

    /**
     * 向实例数组的末尾插入元素（入栈）.
     *
     * @see http://php.net/manual/zh/function.array-push.php
     *
     * @param array ...$elements 插入的元素，可为任意多个
     *
     * @return Ary 原实例
     */
    public function push(...$elements): self
    {
        array_push($this->val, ...$elements);

        return $this;
    }

    /**
     * 从实例数组中的末尾弹出一个元素（出栈）.
     *
     * @see http://php.net/manual/zh/function.array-pop.php
     *
     * @param bool|null $getElement true 则返回元素值，false 则返回原实例
     *
     * @return $this|mixed 原实例或元素值
     */
    public function pop(bool $getElement = null)
    {
        $element = array_pop($this->val);
        if (static::default($getElement, 'popGetElement')) {
            return $element;
        }

        return $this;
    }

    /**
     * 向实例数组的开头插入元素.
     *
     * @see http://php.net/manual/zh/function.array-unshift.php
     *
     * @param array ...$elements 插入的元素可为任意多个
     *
     * @return Ary 原实例
     */
    public function unShift(...$elements): self
    {
        array_unshift($this->val, ...$elements);

        return $this;
    }

    /**
     * 从实例数组中的开头弹出一个元素.
     *
     * @see http://php.net/manual/zh/function.array-shift.php
     *
     * @param bool|null $getElement true 则返回元素值，false 则返回原实例
     *
     * @return $this|mixed 原实例或元素值
     */
    public function shift(bool $getElement = null)
    {
        $element = array_shift($this->val);
        if (static::default($getElement, 'shiftGetElement')) {
            return $element;
        }

        return $this;
    }

    /**
     * 向实例数组尾部追加一个实例数组.
     *
     * @see http://php.net/manual/zh/function.array-merge.php
     *
     * @param Ary       $array          要追加的数组
     * @param bool|null $preserveValues true 则相同键名（包括数字索引）不会覆盖原实例数组，false 相同键名会覆盖原实例数组，数字索引会重新索引
     *
     * @return Ary 新实例
     */
    public function append(self $array, bool $preserveValues = null): self
    {
        $preserveValues = static::default($preserveValues, 'appendPreserveValues');
        if ($preserveValues) {
            $val = $this->val + $array->val();
        } else {
            $val = array_merge($this->val, $array->val());
        }

        return static::new($val);
    }

    /**
     * 在数组中搜索给定的值，如果成功则返回首个相应的键名.
     *
     * @see http://php.net/manual/zh/function.array-search.php
     *
     * @param mixed     $needle 要搜索的值
     * @param bool|null $strict 是否采用严格比较
     *
     * @return false|int|string 搜索结果的键名或 false
     */
    public function search($needle, bool $strict = null)
    {
        return array_search($needle, $this->val, static::default($strict, 'searchStrict'));
    }

    /**
     * 获取第一个指定值之前的元素所组成的实例数组.
     *
     * @param mixed     $needle       指定值
     * @param bool|null $contain      结果是否包含指定值
     * @param bool|null $preserveKeys 为 true 则数字索引保持不变，false 则会重置数组的数字索引，字符串键名始终保持不变
     *
     * @return Ary 新实例
     */
    public function before($needle, bool $contain = null, bool $preserveKeys = null): self
    {
        $key = $this->search($needle, true);
        // 如果数组中无此值，直接返回空实例数组
        if ($key === false) {
            return static::new([]);
        }
        // keys 将原数组的键名作为值，重新索引为新的数组
        // 通过 search 可以获取原数组的键名所对应的数字索引，此时的数字索引即为 slice 所需的长度
        $len = $this->keys()->search($key, true);
        if (static::default($contain, 'beforeContain')) {
            $len++;
        }

        return $this->slice(0, $len, static::default($preserveKeys, 'beforePreserveKeys'));
    }

    /**
     * 获取第一个指定值之后的元素所组成的实例数组.
     *
     * @param mixed     $needle       指定值
     * @param bool|null $contain      结果是否包含指定值
     * @param bool|null $preserveKeys 为 true 则数字索引保持不变，false 则会重置数组的数字索引，字符串键名始终保持不变
     *
     * @return Ary 新实例
     */
    public function after($needle, bool $contain = null, bool $preserveKeys = null): self
    {
        $key = $this->search($needle, true);
        // 如果数组中无此值，直接返回空实例数组
        if ($key === false) {
            return static::new([]);
        }
        // 原理与 before 相同
        $offset = $this->keys()->search($key, true);
        if (!static::default($contain, 'afterContain')) {
            $offset++;
        }

        return $this->slice($offset, null, static::default($preserveKeys, 'afterPreserveKeys'));
    }

    /**
     * 获取指定键名之前的元素所组成的实例数组.
     *
     * @param string|int $key     指定的键名
     * @param bool|null  $contain 是否包含该键名的元素
     *
     * @return Ary 新实例
     */
    public function beforeKey($key, bool $contain = null): self
    {
        if (!array_key_exists($key, $this->val)) {
            return static::new([]);
        }

        $len = $this->keys()->search($key, true);
        if (static::default($contain, 'beforeKeyContain')) {
            $len++;
        }

        return $this->slice(0, $len, true);
    }

    /**
     * 获取指定键名之后的元素所组成的实例数组.
     *
     * @param string|int $key     指定的键名
     * @param bool|null  $contain 是否包含该键名的元素
     *
     * @return Ary 新实例
     */
    public function afterKey($key, bool $contain = null)
    {
        if (!array_key_exists($key, $this->val)) {
            return static::new([]);
        }

        $offset = $this->keys()->search($key, true);
        if (!static::default($contain, 'afterKeyContain')) {
            $offset++;
        }

        return $this->slice($offset, null, true);
    }

    /**
     * 使用传递的实例数组替换原实例数组中的元素.
     *
     * @see http://php.net/manual/zh/function.array-replace.php
     *
     * @param Ary[] ...$arrays 替换的实例数组
     *
     * @return Ary 新实例
     */
    public function replace(self ...$arrays): self
    {
        $ary = static::new($arrays);

        return static::new(
            array_replace($this->val, ...$ary->toArray(false))
        );
    }

    /**
     * 返回两实例数组的值的交集.
     *
     * @see http://php.net/manual/zh/function.array-intersect.php
     * @see http://php.net/manual/zh/function.array-intersect-assoc.php
     *
     * @param Ary       $ary     用于比较的实例
     * @param bool|null $compKey 是否比较键名
     *
     * @return Ary 新实例
     */
    public function intersect(self $ary, bool $compKey = null): self
    {
        if (static::default($compKey, 'intersectCompKey')) {
            $val = array_intersect_assoc($this->val, $ary->val());
        } else {
            $val = array_intersect($this->val, $ary->val());
        }

        return static::new($val);
    }

    /**
     * 返回两实例数组的值的差集.
     *
     * @see http://php.net/manual/zh/function.array-diff.php
     * @see http://php.net/manual/zh/function.array-diff-assoc.php
     *
     * @param Ary       $ary     用于比较的实例
     * @param bool|null $compKey 是否比较键名
     *
     * @return Ary 新实例
     */
    public function diff(self $ary, bool $compKey = null): self
    {
        if (static::default($compKey, 'diffCompKey')) {
            $val = array_diff_assoc($this->val, $ary->val());
        } else {
            $val = array_diff($this->val, $ary->val());
        }

        return static::new($val);
    }

    /**
     * 使用键名比较计算实例数组的交集.
     *
     * @see http://php.net/manual/zh/function.array-intersect-key.php
     *
     * @param Ary $ary 用于比较的实例
     *
     * @return Ary 新实例
     */
    public function intersectKey(self $ary): self
    {
        $val = array_intersect_key($this->val, $ary->val());

        return static::new($val);
    }

    /**
     * 使用键名比较计算实例数组的差集.
     *
     * @see http://php.net/manual/zh/function.array-diff-key.php
     *
     * @param Ary $ary 用于比较的实例
     *
     * @return Ary 新实例
     */
    public function diffKey(self $ary): self
    {
        $val = array_diff_key($this->val, $ary->val());

        return static::new($val);
    }

    /**
     * 清除实例数组中所有等值为 false 的元素（包括： null false 0 '' []）
     * 警告：空的 Ary 实例并不会清除.
     *
     * @see http://php.net/manual/zh/function.array-filter.php
     *
     * @return Ary 原实例
     */
    public function clean(): self
    {
        $this->val = array_filter($this->val);

        return $this;
    }

    /**
     * 将实例数组连接为字符串.
     *
     * @see http://php.net/manual/zh/function.implode.php
     *
     * @param string|null $glue 元素间连接的字符串，默认为 ''
     *
     * @return string
     */
    public function join(string $glue = null): string
    {
        // 当 val 数组中存在 Ary 实例时，其魔术方法 __toString 会同样调用 join 方法，并以 $default 中 joinGlue 的值为 $glue
        // 所以调用 join 方法时需要将 $default 中 joinGlue 的值设置为当前实参 $glue， 并在调用结束后恢复原值
        $oldGlue = static::default('joinGlue');
        static::setDefault(['joinGlue' => $glue]);
        $str = implode($glue, $this->val);
        static::setDefault(['joinGlue' => $oldGlue]);

        return $str;
    }

    /**
     * 使用用户自定义函数对实例数组中的每个元素做回调处理.
     *
     * @see http://php.net/manual/zh/function.array-walk.php
     *
     * @param callable $fn        使用的回调函数
     * @param null     $userData  用户数据，回调函数的第三个参数
     * @param bool     $recursive 是否递归实例数组
     *
     * @return Ary 原实例
     */
    public function each(callable $fn, $userData = null, bool $recursive = null): self
    {
        if (static::default($recursive, 'eachRecursive')) {
            $array = $this->toArray(true);
            array_walk_recursive($array, $fn, $userData);
        } else {
            array_walk($this->val, $fn, $userData);
        }

        return $this;
    }

    /**
     * 为实例数组的每个元素应用回调函数.
     *
     * @see http://php.net/manual/zh/function.array-map.php
     *
     * @param callable $fn 使用的回调函数
     *
     * @return Ary 新实例
     */
    public function map(callable $fn): self
    {
        $val = array_map($fn, $this->val);

        return static::new($val);
    }

    /**
     * 用回调函数过滤实例数组中的元素.
     *
     * @see http://php.net/manual/zh/function.array-filter.php
     *
     * @param callable $fn   使用的回调函数
     * @param int|null $flag 决定callback接收的参数形式
     *
     * @return Ary 新实例
     */
    public function filter(callable $fn, int $flag = null): self
    {
        $val = array_filter($this->val, $fn, static::default($flag, 'filterFlag'));

        return static::new($val);
    }

    /**
     * 用回调函数迭代地将实例数组简化为单一的值
     *
     * @see http://php.net/manual/zh/function.array-reduce.php
     *
     * @param callable   $fn      使用的回调函数
     * @param mixed|null $initial 回调函数初始值
     *
     * @return mixed
     */
    public function reduce(callable $fn, $initial = null)
    {
        return array_reduce($this->val, $fn, $initial);
    }

    /**
     * 将一个多维数组扁平化为一个一维数组.
     *
     * @param bool|null $preserveKeys true 保持值不为数组或 Ary 对象的字符串键名，重置数字索引，false 则删除所有键名，重新以数字索引
     *
     * @return Ary 新实例
     */
    public function flat(bool $preserveKeys = null)
    {
        $array = [];
        $fn = function ($val, $key, $preserveKeys) use (&$array) {
            if ($preserveKeys && is_string($key)) {
                $array[$key] = $val;
            } else {
                $array[] = $val;
            }
        };

        $this->each($fn, static::default($preserveKeys, 'flatPreserveKeys'), true);

        return static::new($array);
    }

    /**
     * 以指定长度将一个值填充进实例数组.
     *
     * @see http://php.net/manual/zh/function.array-pad.php
     *
     * @param int   $size    新实例数组的长度
     * @param mixed $element 填充的值
     *
     * @return Ary 新实例
     */
    public function pad(int $size, $element): self
    {
        $val = array_pad($this->val, $size, $element);

        return static::new($val);
    }

    /**
     * 返回用 $val 填充当前所有键的新实例.
     *
     * @param mixed $val 填充值
     *
     * @return Ary 新实例
     */
    public function fill($val): self
    {
        return static::new(array_fill_keys($this->keys()->val(), $val));
    }

    /**
     * 判断实例数组是否为空.
     *
     * @see http://php.net/manual/zh/function.empty.php
     *
     * @return bool
     */
    public function empty(): bool
    {
        return empty($this->val);
    }

    /**
     * 计算实例数组中所有值的乘积.
     *
     * @see http://php.net/manual/zh/function.array-product.php
     *
     * @return float
     */
    public function product(): float
    {
        return array_product($this->val);
    }

    /**
     * 确认实例数组中的值全为 true，请确保实例数组中只包含：布尔类型的数据，否则将产生未预料的结果.
     *
     * @return bool
     */
    public function allTrue(): bool
    {
        return (bool) $this->product();
    }

    /**
     * 对实例数组中所有值求和.
     *
     * @see http://php.net/manual/zh/function.array-sum.php
     *
     * @return float
     */
    public function sum(): float
    {
        return array_sum($this->val);
    }

    /**
     * 从实例数组中随机取出一个或多个元素组成新的实例，保持索引关联.
     *
     * @see http://php.net/manual/zh/function.array-rand.php
     *
     * @param int $num 取出数量
     *
     * @throws AryOutOfRangeException
     *
     * @return Ary 新实例
     */
    public function rand(int $num): self
    {
        if ($num > count($this->val)) {
            throw new AryOutOfRangeException();
        }

        if ($num === 1) {
            $key = array_rand($this->val, $num);
            $val = [$key => $this->val[$key]];
        } else {
            $keys = array_rand($this->val, $num);
            $val = array_intersect_key($this->val, array_flip($keys));
        }

        return static::new($val);
    }

    /**
     * 从实例数组中随机取出一个元素的值
     *
     * @throws AryOutOfRangeException
     *
     * @return mixed
     */
    public function randVal()
    {
        if (empty($this->val)) {
            throw new AryOutOfRangeException();
        }

        return $this->val[array_rand($this->val, 1)];
    }

    /**
     * 从实例数组中随机取出一个元素的键名.
     *
     * @throws AryOutOfRangeException
     *
     * @return mixed
     */
    public function randKey()
    {
        if (empty($this->val)) {
            throw new AryOutOfRangeException();
        }

        return array_rand($this->val, 1);
    }

    /**
     * 将实例数组转成普通数组.
     *
     * @param bool|null $recursive true 则递归调用，false 则只会将当前实例数组中的 Ary 元素转为数组后便不再递归
     *
     * @return array
     */
    public function toArray(bool $recursive = null): array
    {
        $array = [];
        if (static::default($recursive, 'toArrayRecursive')) {
            $array = static::valToArray($this->val);
        } else {
            foreach ($this->val as $k => $v) {
                if ($v instanceof static) {
                    $array[$k] = $v->val();
                } else {
                    $array[$k] = $v;
                }
            }
        }

        return $array;
    }

    protected static function valToArray(array $val)
    {
        $array = $val;
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $array[$k] = static::valToArray($v);
            } elseif ($v instanceof static) {
                $array[$k] = static::valToArray($v->val());
            } else {
                $array[$k] = $v;
            }
        }

        return $array;
    }

    /**
     * 将实例数组转为 json 字符串.
     *
     * @param int|null $options 格式选项
     * @param int|null $depth   最大嵌套深度
     *
     * @return string
     */
    public function toJson(int $options = null, int $depth = null): string
    {
        return json_encode(
            $this->val,
            static::default($options, 'toJsonOptions'),
            static::default($depth, 'toJsonDepth')
        );
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->val);
    }

    public function offsetExists($offset)
    {
        return isset($this->val[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->val[$offset]) ? $this->val[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->val[] = $value;
        } else {
            $this->val[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->val[$offset]);
    }

    public function count(): int
    {
        return count($this->val);
    }

    public function jsonSerialize(): array
    {
        return $this->val;
    }

    public function __toString(): string
    {
        return $this->join(static::default('joinGlue'));
    }

    public function __isset($key) : bool
    {
        return isset($this->val[$key]);
    }

    public function __get($key)
    {
        return $this->val[$key];
    }

    public function __set($key, $val)
    {
        $this->val[$key] = $val;
    }

    public function __unset($key)
    {
        unset($this->val[$key]);
    }

    /**
     * 返回一个新实例.
     *
     * @param array $array 实例包含的数组
     *
     * @return Ary 新实例
     */
    public static function new(array $array = []): self
    {
        return new static($array);
    }

    /**
     * 设置类方法的默认值
     *
     * @param array $default
     */
    public static function setDefault(array $default): void
    {
        foreach ($default as $key => $val) {
            static::$default[$key] = $val;
        }
    }

    /**
     * 从 json 字符串创建一个新实例.
     *
     * @param string   $json    json
     * @param int|null $depth   最大嵌套深度
     * @param int|null $options 格式选项
     *
     * @return Ary 新实例
     */
    public static function fromJson(string $json, int $depth = null, int $options = null): self
    {
        return static::new(
            json_decode(
                $json,
                true,
                static::default($depth, 'fromJsonDepth'),
                static::default($options, 'fromJsonOptions')
            )
        );
    }

    /**
     * 创建一个新的实例数组，用第一个实例数组的值作为其键名，第二个实例数组的值作为其值
     *
     * @see http://php.net/manual/zh/function.array-combine.php
     *
     * @param Ary $key 作为键的实例数组
     * @param Ary $val 作为值的实例数组
     *
     * @return Ary 新实例
     */
    public static function combine(self $key, self $val): self
    {
        return static::new(
            array_combine($key->val(), $val->val())
        );
    }

    /**
     * 创建指定长度的实例数组并填充一个值
     *
     * @param int   $startIndex 实例数组第一个索引
     * @param int   $num        实例数组长度
     * @param mixed $val        填充值
     *
     * @return Ary 新实例
     */
    public static function newFill(int $startIndex, int $num, $val): self
    {
        return static::new(array_fill($startIndex, $num, $val));
    }

    /**
     * 判断 $val 是否能以数组的方式访问.
     *
     * @param mixed $val
     *
     * @return bool
     */
    public static function accessible($val): bool
    {
        return is_array($val) || $val instanceof ArrayAccess;
    }

    /**
     * 判断 $key 是否能作键名.
     *
     * @param mixed $key
     *
     * @return bool
     */
    public static function isValidKey($key): bool
    {
        return is_string($key) || is_int($key);
    }

    /**
     * 若传入两个参数，第一个参数为 null 时，则返回 $default 数组中以 $second 为键名的值
     * 若第一个参数不为 null 则返回第一个参数的值
     * 若只传入一个参数则直接返回 $default 数组中以 $first 为键名的值
     *
     * @param null        $first
     * @param string|null $second
     *
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
