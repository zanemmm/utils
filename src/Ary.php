<?php
namespace Zane\Utils;

use IteratorAggregate;
use ArrayAccess;
use ArrayIterator;
use Countable;
use JsonSerializable;

class Ary implements IteratorAggregate, ArrayAccess, Countable, JsonSerializable
{
    protected static $config = [
        'keysSearchValue'      => null,
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
        'afterContain'         => true,
        'joinGlue'             => '',
        'eachRecursive'        => false,
        'filterFlag'           => 0,
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
     * 获取或设置该实例的数组
     * @param array|null $array 为空时获取实例的数组，非空时设置实例的数组
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
     * 获取实例数组中的全部值
     * @see http://php.net/manual/zh/function.array-values.php
     * @return Ary 新实例
     */
    public function values(): self
    {
        return static::new(array_values($this->val));
    }

    /**
     * 获取实例数组中的键名
     * @see http://php.net/manual/zh/function.array-keys.php
     * @param null $searchValue 空则返回全部键，非空则返回对应 $searchValue 值的键
     * @param null $strict 为真时数组中的值与 $searchValue 采用严格比较
     * @return Ary 新实例
     */
    public function keys($searchValue = null, $strict = null): self
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
                static::config('keysSearchValue', $searchValue),
                static::config('keysStrict', $strict)
            )
        );
    }

    /**
     * 获取实例数组中第一个元素的值
     * @return mixed
     */
    public function first()
    {
        return reset($this->val);
    }

    /**
     * 获取实例数组中最后一个元素的值
     * @return mixed
     */
    public function end()
    {
        return end($this->val);
    }

    /**
     * 获取实例数组中第一个元素的键
     * @return int|null|string
     */
    public function firstKey()
    {
        $this->first();
        return key($this->val);
    }

    /**
     * 获取实例数组中最后一个元素的键
     * @return int|null|string
     */
    public function endKey()
    {
        $this->end();
        return key($this->val);
    }

    /**
     * 获取实例数组中前 $len 个元素组成的新 Ary 实例
     * @see \Zane\Utils\Ary::slice()
     * @param int $len 获取元素的个数，小于等于 0 则返回空数组的实例，大于等于实例数组的长度则返回原数组(索引可能会改变具体看 $preserveKeys 参数)的新实例
     * @param bool|null $preserveKeys 为 true 则数字索引保持不变，false 则会重置数组的数字索引，字符串键名始终保持不变
     * @return Ary 新实例
     */
    public function limit(int $len, bool $preserveKeys = null): self
    {
        if ($len <= 0) {
            return static::new([]);
        }

        $val = array_slice($this->val, 0, $len, static::config('limitPreserveKeys', $preserveKeys));

        return static::new($val);
    }

    /**
     * 获取实例数组中后 $len 个元素组成的新 Ary 实例
     * @see \Zane\Utils\Ary::slice()
     * @param int $len 获取元素的个数，小于等于 0 则返回空数组的实例，大于等于实例数组的长度则返回原数组(索引可能会改变具体看 $preserveKeys 参数)的新实例
     * @param bool|null $preserveKeys 为 true 则数字索引保持不变，false 则会重置数组的数字索引，字符串键名始终保持不变
     * @return Ary 新实例
     */
    public function tail(int $len, bool $preserveKeys = null): self
    {
        if ($len <= 0) {
            return static::new([]);
        }

        $val = array_slice($this->val, -$len, null, static::config('tailPreserveKeys', $preserveKeys));

        return static::new($val);
    }

    /**
     * 从实例数组中取出一段并返回新的实例
     * @see http://php.net/manual/zh/function.array-slice.php
     * @param int $offset 起始偏移量
     * @param int $len 长度
     * @param bool|null $preserveKeys 为 true 则数字索引保持不变，false 则会重置数组的数字索引，字符串键名始终保持不变
     * @return Ary 新实例
     */
    public function slice(int $offset, int $len = null, bool $preserveKeys = null): self
    {
        $val = array_slice($this->val, $offset, $len, static::config('slicePreserveKeys', $preserveKeys));

        return static::new($val);
    }

    /**
     * 将一个实例数组分割为多个并返回多个新实例
     * @see http://php.net/manual/zh/function.array-chunk.php
     * @param int $size 每个新实例数组的大小，最后一个实例数组可能小于 $size
     * @param bool|null $preserveKeys 为 true 则数字索引保持不变，false 则会重置数组的数字索引
     * @return Ary 新实例
     */
    public function chunk(int $size, bool $preserveKeys = null): self
    {
        $chunks = array_chunk($this->val, $size, static::config('chunkPreserveKeys', $preserveKeys));

        $val = [];
        foreach ($chunks as $chunk) {
            $val[] = static::new($chunk);
        }

        return static::new($val);
    }

    /**
     * 返回由实例数组中指定的一列所组成的新实例
     * @see http://php.net/manual/zh/function.array-column.php
     * @param mixed $columnKey 需要返回值的列，它可以是索引数组的列索引，或者是关联数组的列的键，也可以是属性名，为 NULL 时返回整个数组
     * @param null $indexKey 作为返回数组的索引或键的列
     * @return Ary 新实例
     */
    public function column($columnKey, $indexKey = null): self
    {
        // 当列键名为 val 会与数组属性 val 冲突，因为在 PHP 中同一个类的对象即使不是同一个实例也可以互相访问对方的私有与受保护成员
        // 所以并不会触发 __get() 方法，来获取数组中的值，而是直接将整个 val 属性返回
        // 为了解决这个问题采用匿名类的方式将 array_column 函数从 Ary 类调用改变到在匿名类中调用
        if ($columnKey === 'val') {
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
     * 统计实例数组中值的出现次数,
     * 返回一个键为原数组的值，值为原数组值出现的次数的新实例
     * @see http://php.net/manual/zh/function.array-count-values.php
     * @return Ary 新实例
     */
    public function countValues(): self
    {
        return static::new(array_count_values($this->val));
    }

    /**
     * 交换实例数组中的键和值并返回新的实例
     * @see http://php.net/manual/zh/function.array-flip.php
     * @return Ary 新实例
     */
    public function flip(): self
    {
        return static::new(array_flip($this->val));
    }

    /**
     * 检查实例数组中是否存在某个值
     * @see http://php.net/manual/zh/function.in-array.php
     * @param mixed $needle 要检查的值
     * @param bool|null $strict 是否严格比较
     * @return bool
     */
    public function exist($needle, bool $strict = null): bool
    {
        return in_array($needle, $this->val, static::config('existStrict', $strict));
    }

    /**
     * 检查实例数组里是否有指定的键名或索引
     * @see http://php.net/manual/zh/function.array-key-exists.php
     * @param int|string $key 要检查的键
     * @return bool
     */
    public function keyExist($key): bool
    {
        // isset 性能比 array_key_exists 高，但 isset 在数组成员的值为 null 时会返回 false
        // 所以利用 || 运算符的短路特性，仅当 isset 为 false 时使用 array_key_exists 判断是否存在该键
        return (isset($this->val[$key]) || array_key_exists($key, $this->val));
    }

    /**
     * 检查实例数组存在指定键名的值，且值不为 null
     * @see http://php.net/manual/zh/function.isset.php
     * @param int|string $key
     * @return bool
     */
    public function isSet($key): bool
    {
        return isset($this->val[$key]);
    }

    /**
     * 对实例数组的值进行排序
     * @see http://php.net/manual/zh/function.sort.php
     * @see http://php.net/manual/zh/function.rsort.php
     * @see http://php.net/manual/zh/function.asort.php
     * @see http://php.net/manual/zh/function.arsort.php
     * @param bool|null $asc true为升序，false为降序
     * @param bool|null $preserveKeys true 则保持索引关联，false 则以数字索引重置
     * @param int|null $flag 排序类型标记，参见 PHP 手册 sort 函数
     * @return Ary 原实例
     */
    public function sort(bool $asc = null, bool $preserveKeys = null, int $flag = null): self
    {
        // 通过将 $asc 左移一位并或上 $preserveKeys 得出范围在 0b00 到 0b11 的 $status
        // 通过 $status 的值选择不同的排序函数
        $status = static::config('sortAsc', $asc) << 1 | static::config('sortPreserveKeys', $preserveKeys);
        $flag   = static::config('sortFlag', $flag);

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
     * 使用用户自定义的比较函数对数组中的值进行排序
     * @see http://php.net/manual/zh/function.usort.php
     * @see http://php.net/manual/zh/function.uasort.php
     * @param callable $fn 比较函数
     * @param bool|null $preserveKeys true 则保持索引关联，false 则以数字索引重置
     * @return Ary 原实例
     */
    public function userSort(callable $fn, bool $preserveKeys = null): self
    {
        if (static::config('userSortPreserveKeys', $preserveKeys)) {
            uasort($this->val, $fn);
        } else {
            usort($this->val, $fn);
        }

        return $this;
    }

    /**
     * 用“自然排序”算法对实例数组的值进行排序
     * @see http://php.net/manual/zh/function.natsort.php
     * @see http://php.net/manual/zh/function.natcasesort.php
     * @param bool|null $caseSensitive 大小写敏感
     * @return Ary 原实例
     */
    public function natSort(bool $caseSensitive = null): self
    {
        if (static::config('natSortCaseSensitive', $caseSensitive)) {
            natsort($this->val);
        } else {
            natcasesort($this->val);
        }

        return $this;
    }

    /**
     * 对实例数组按照键名排序
     * @see http://php.net/manual/zh/function.ksort.php
     * @param bool|null $asc true 为升序，false 为降序
     * @param int|null $flag 排序类型标记，参见 PHP 手册 sort 函数
     * @return Ary 原实例
     */
    public function keySort(bool $asc = null, int $flag = null): self
    {
        if (static::config('keySortAsc', $asc)) {
            ksort($this->val, static::config('keySortFlag', $flag));
        } else {
            krsort($this->val, static::config('keySortFlag', $flag));
        }

        return $this;
    }

    /**
     * 使用用户自定义的比较函数对数组中的键名进行排序
     * @see http://php.net/manual/zh/function.uksort.php
     * @param callable $fn 比较函数
     * @return Ary 原实例
     */
    public function userKeySort(callable $fn): self
    {
        uksort($this->val, $fn);

        return $this;
    }

    /**
     * 打乱实例数组顺序
     * @see http://php.net/manual/zh/function.shuffle.php
     * @return Ary 原实例
     */
    public function shuffle(): self
    {
        // todo 检查是否打乱成功
        shuffle($this->val);

        return $this;
    }

    /**
     * 移除实例数组中重复的值
     * @see http://php.net/manual/zh/function.array-unique.php
     * @param int|null $flag 排序类型标记
     * @return Ary 新实例
     */
    public function unique(int $flag = null): self
    {
        $val = array_unique($this->val, static::config('uniqueFlag', $flag));

        return static::new($val);
    }

    /**
     * 返回单元顺序相反的实例数组
     * @see http://php.net/manual/zh/function.array-reverse.php
     * @return Ary 新实例
     */
    public function reverse(): self
    {
        $val = array_reverse($this->val);

        return static::new($val);
    }

    /**
     * 向实例数组的末尾插入元素（入栈）
     * @see http://php.net/manual/zh/function.array-push.php
     * @param array ...$elements 插入的元素，可为任意多个
     * @return Ary 原实例
     */
    public function push(...$elements): self
    {
        array_push($this->val, ...$elements);

        return $this;
    }

    /**
     * 从实例数组中的末尾弹出一个元素（出栈）
     * @see http://php.net/manual/zh/function.array-pop.php
     * @param bool|null $getElement true 则返回元素值，false 则返回原实例
     * @return $this|mixed 原实例或元素值
     */
    public function pop(bool $getElement = null)
    {
        $element = array_pop($this->val);
        if (static::config('popGetElement', $getElement)) {
            return $element;
        }

        return $this;
    }

    /**
     * 向实例数组的开头插入元素
     * @see http://php.net/manual/zh/function.array-unshift.php
     * @param array ...$elements 插入的元素可为任意多个
     * @return Ary 原实例
     */
    public function unShift(...$elements): self
    {
        array_unshift($this->val, ...$elements);

        return $this;
    }

    /**
     * 从实例数组中的开头弹出一个元素
     * @see http://php.net/manual/zh/function.array-shift.php
     * @param bool|null $getElement true 则返回元素值，false 则返回原实例
     * @return $this|mixed 原实例或元素值
     */
    public function shift(bool $getElement = null)
    {
        $element = array_shift($this->val);
        if (static::config('shiftGetElement', $getElement)) {
            return $element;
        }

        return $this;
    }

    /**
     * 向实例数组尾部追加一个实例数组
     * @see http://php.net/manual/zh/function.array-merge.php
     * @param Ary $array 要追加的数组
     * @param bool|null $preserveValues true 则相同键名（包括数字索引）不会覆盖原实例数组，false 相同键名会覆盖原实例数组，数字索引会重新索引
     * @return Ary 新实例
     */
    public function append(self $array, bool $preserveValues = null): self
    {
        $preserveValues = static::config('appendPreserveValues', $preserveValues);
        if ($preserveValues) {
            $val = $this->val + $array->val();
        } else {
            $val = array_merge($this->val, $array->val());
        }

        return static::new($val);
    }

    /**
     * 在数组中搜索给定的值，如果成功则返回首个相应的键名
     * @see http://php.net/manual/zh/function.array-search.php
     * @param mixed $needle 要搜索的值
     * @param bool|null $strict 是否采用严格比较
     * @return false|int|string 搜索结果的键名或 false
     */
    public function search($needle, bool $strict = null)
    {
        return array_search($needle, $this->val, static::config('searchStrict', $strict));
    }

    /**
     * 获取第一个指定值之前的元素所组成的实例数组
     * @param mixed $needle 指定值
     * @param bool|null $contain 结果是否包含指定值
     * @param bool|null $preserveKeys 为 true 则数字索引保持不变，false 则会重置数组的数字索引，字符串键名始终保持不变
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
        if (static::config('beforeContain', $contain)) {
            $len++;
        }

        return $this->slice(0, $len, static::config('beforePreserveKeys', $preserveKeys));
    }

    /**
     * 获取第一个指定值之后的元素所组成的实例数组
     * @param mixed $needle 指定值
     * @param bool|null $contain 结果是否包含指定值
     * @param bool|null $preserveKeys 为 true 则数字索引保持不变，false 则会重置数组的数字索引，字符串键名始终保持不变
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
        if (!static::config('afterContain', $contain)) {
            $offset++;
        }

        return $this->slice($offset, null, static::config('afterPreserveKeys', $preserveKeys));
    }

    /**
     * 使用传递的实例数组替换原实例数组中的元素
     * @see http://php.net/manual/zh/function.array-replace.php
     * @param Ary[] ...$arrays 替换的实例数组
     * @return Ary 新实例
     */
    public function replace(Ary ...$arrays): self
    {
        $ary = static::new($arrays);

        return static::new(
            array_replace($this->val, ...$ary->toArray(false))
        );
    }

    /**
     * 清除实例数组中所有等值为 false 的元素（包括： null false 0 '' []）
     * @see http://php.net/manual/zh/function.array-filter.php
     * @return Ary 原实例
     */
    public function clean(): self
    {
        $this->val = array_filter($this->val);

        return $this;
    }

    /**
     * 将实例数组连接为字符串
     * @see http://php.net/manual/zh/function.implode.php
     * @param string|null $glue 元素间连接的字符串，默认为 ''
     * @return string
     */
    public function join(string $glue = null): string
    {
        // 当 val 数组中存在 Ary 实例时，其魔术方法 __toString 会同样调用 join 方法，并以 $config 中 joinGlue 的值为 $glue
        // 所以调用 join 方法时需要将 $config 中 joinGlue 的值设置为当前实参 $glue， 并在调用结束后恢复原值
        $oldGlue = static::config('joinGlue');
        static::setConfig(['joinGlue' => $glue]);
        $str = implode($glue, $this->val);
        static::setConfig(['joinGlue' => $oldGlue]);

        return $str;
    }

    /**
     * 使用用户自定义函数对实例数组中的每个元素做回调处理
     * @see http://php.net/manual/zh/function.array-walk.php
     * @param callable $fn 使用的回调函数
     * @param null $userData 用户数据，回调函数的第三个参数
     * @param bool $recursive 是否递归实例数组
     * @return Ary 原实例
     */
    public function each(callable $fn, $userData = null, bool $recursive = null): self
    {
        if (static::config('eachRecursive', $recursive)) {
            array_walk_recursive($this->toArray(true), $fn, $userData);
        } else {
            array_walk($this->val, $fn, $userData);
        }

        return $this;
    }

    /**
     * 为实例数组的每个元素应用回调函数
     * @see http://php.net/manual/zh/function.array-map.php
     * @param callable $fn 使用的回调函数
     * @return Ary 新实例
     */
    public function map(callable $fn): self
    {
        $val = array_map($fn, $this->val);

        return static::new($val);
    }

    /**
     * 用回调函数过滤实例数组中的元素
     * @see http://php.net/manual/zh/function.array-filter.php
     * @param callable $fn 使用的回调函数
     * @param int|null $flag 决定callback接收的参数形式
     * @return Ary 新实例
     */
    public function filter(callable $fn, int $flag = null): self
    {
        $val = array_filter($this->val, $fn, static::config('filterFlag', $flag));

        return static::new($val);
    }

    /**
     * 用回调函数迭代地将实例数组简化为单一的值
     * @see http://php.net/manual/zh/function.array-reduce.php
     * @param callable $fn 使用的回调函数
     * @param null $initial 回调函数初始值
     * @return mixed
     */
    public function reduce(callable $fn, $initial = null)
    {
        return array_reduce($this->val, $fn, $initial);
    }

    /**
     * 以指定长度将一个值填充进实例数组
     * @see http://php.net/manual/zh/function.array-pad.php
     * @param int $size 新实例数组的长度
     * @param mixed $element 填充的值
     * @return Ary 新实例
     */
    public function pad(int $size, $element): self
    {
        $val = array_pad($this->val, $size, $element);

        return static::new($val);
    }

    /**
     * 判断实例数组是否为空
     * @see http://php.net/manual/zh/function.empty.php
     * @return bool
     */
    public function empty(): bool
    {
        return empty($this->val);
    }

    /**
     * 计算实例数组中所有值的乘积
     * @see http://php.net/manual/zh/function.array-product.php
     * @return number
     */
    public function product(): number
    {
        return array_product($this->val);
    }

    /**
     * 确认实例数组中没有等值为 false 的值，包括： false null 0 '' []
     * @return bool
     */
    public function allTrue(): bool
    {
        return (bool)$this->product();
    }

    /**
     * 对实例数组中所有值求和
     * @see http://php.net/manual/zh/function.array-sum.php
     * @return number
     */
    public function sum(): number
    {
        return array_sum($this->val);
    }

    /**
     * 从实例数组中随机取出一个或多个元素组成新的实例
     * @see http://php.net/manual/zh/function.array-rand.php
     * @param int $num 取出数量
     * @return Ary
     */
    public function rand(int $num): self
    {
        // todo 抛出异常
        if ($num === 1) {
            $val = [array_rand($this->val, $num)];
        } else {
            $val = array_rand($this->val, $num);
        }
        return static::new($val);
    }

    /**
     * 从实例数组中随机取出一个元素
     * @return mixed
     */
    public function randElement()
    {
        return array_rand($this->val, 1);
    }

    /**
     * 将实例数组转成普通数组
     * @param bool|null $recursive true 则递归调用，false 则只会将当前实例数组中的 Ary 元素转为数组后便不再递归
     * @return array
     */
    public function toArray(bool $recursive = null): array
    {
        $array = [];
        if (static::config('toArrayRecursive', $recursive)) {
            foreach ($this->val as $item) {
                if ($item instanceof static) {
                    $array[] = $item->toArray(true);
                } else {
                    $array[] = $item;
                }
            }
        } else {
            foreach ($this->val as $item) {
                if ($item instanceof static) {
                    $array[] = $item->val();
                } else {
                    $array[] = $item;
                }
            }
        }

        return $array;
    }

    /**
     * 将实例数组转为 json 字符串
     * @param int|null $options 格式选项
     * @param int|null $depth 最大嵌套深度
     * @return string
     */
    public function toJson(int $options = null, int $depth = null): string
    {
        return json_encode(
            $this->val,
            static::config('toJsonOptions', $options),
            static::config('toJsonDepth', $depth)
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
        return $this->join(static::config('joinGlue'));
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
     * 返回一个新实例
     * @param array $array 实例包含的数组
     * @return Ary 新实例
     */
    public static function new(array $array = []): self
    {
        return new static($array);
    }

    /**
     * 设置类方法的默认值
     * @param array $config
     */
    public static function setConfig(array $config): void
    {
        foreach ($config as $key => $val) {
            static::$config[$key] = $val;
        }
    }

    /**
     * 从 json 字符串创建一个新实例
     * @param string $json json
     * @param int|null $depth 最大嵌套深度
     * @param int|null $options 格式选项
     * @return Ary 新实例
     */
    public static function fromJson(string $json, int $depth = null, int $options = null): self
    {
        return static::new(
            json_decode(
                $json,
                true,
                static::config('fromJsonDepth', $depth),
                static::config('fromJsonOptions', $options)
            )
        );
    }

    /**
     * 创建一个新的实例数组，用第一个实例数组的值作为其键名，第二个实例数组的值作为其值
     * @see http://php.net/manual/zh/function.array-combine.php
     * @param Ary $key 作为键的实例数组
     * @param Ary $val 作为值的实例数组
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
     * @param int $startIndex 实例数组第一个索引
     * @param int $num 实例数组长度
     * @param mixed $val 填充值
     * @return Ary 新实例
     */
    public static function fill(int $startIndex, int $num, $val): self
    {
        return static::new(array_fill($startIndex, $num, $val));
    }

    /**
     * 使用 $val 的值作为值，$keys 的值作为键名来创建并填充一个新实例
     * @param Ary $keys 作为键的实例数组
     * @param mixed $val 填充值
     * @return Ary 新实例
     */
    public static function fillKeys(self $keys, $val): self
    {
        return static::new(array_fill_keys($keys->val(), $val));
    }

    /**
     * 若 $val 非 null 则返回 $val，否则返回 $config 中 $key 对应的值
     * @param string $key
     * @param null $val
     * @return mixed|null
     */
    protected static function config(string $key, $val = null)
    {
        if (is_null($val)) {
            return static::$config[$key] ?? null;
        }

        return $val;
    }
}
