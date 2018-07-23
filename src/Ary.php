<?php

namespace Zane\Utils;

use TypeError;
use IteratorAggregate;
use ArrayAccess;
use ArrayIterator;
use Countable;

class Ary implements IteratorAggregate, ArrayAccess, Countable
{
    protected static $config = [
        'keysSearchValue'      => null,
        'keysStrict'           => true,
        'limitPreserveKeys'    => false,
        'slicePreserveKeys'    => false,
        'chunkPreserveKeys'    => false,
        'existStrict'          => true,
        'sortAsc'              => true,
        'sortPreserveKeys'     => false,
        'sortFlag'             => SORT_REGULAR,
        'keySortAsc'           => true,
        'keySortFlag'          => SORT_REGULAR,
        'uniqueFlag'           => SORT_REGULAR,
        'natSortCaseSensitive' => true,
        'joinGlue'             => '',
        'filterFlag'           => 0,
        'toJsonOptions'        => JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
        'toJsonDepth'          => 512,
        'fromJsonOptions'      => 0,
        'fromJsonDepth'        => 512,
        'popGetElement'        => true,
        'shiftGetElement'      => true,
        'appendPreserveValues' => false,
        'searchStrict'         => true,
        'beforeContain'        => false,
        'afterContain'         => true,
        'toArrayRecursive'     => false
    ];

    protected $val;

    public function __construct(array $array = [])
    {
        $this->val = $array;
    }

    public function val(array $array = null)
    {
        if (is_null($array)) {
            return $this->val;
        }

        $this->val = $array;

        return $this;
    }

    public function values(): self
    {
        return static::new(array_values($this->val));
    }

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

    public function first()
    {
        return reset($this->val);
    }

    public function end()
    {
        return end($this->val);
    }

    public function firstKey()
    {
        $this->first();
        return key($this->val);
    }

    public function endKey()
    {
        $this->end();
        return key($this->val);
    }

    public function limit(int $len, bool $preserveKeys = null): self
    {
        if ($len < 0) {
            return static::new([]);
        }

        $val = array_slice($this->val, 0, $len, static::config('limitPreserveKeys', $preserveKeys));

        return static::new($val);
    }

    public function slice(int $offset, int $len, bool $preserveKeys = null): self
    {
        $val = array_slice($this->val, $offset, $len, static::config('slicePreserveKeys', $preserveKeys));

        return static::new($val);
    }

    public function chunk(int $size, bool $preserveKeys): self
    {
        $chunks = array_chunk($this->val, $size, static::config('chunkPreserveKeys', $preserveKeys));

        $val = [];
        foreach ($chunks as $chunk) {
            $val[] = static::new($chunk);
        }

        return static::new($val);
    }

    public function column($columnKey, $indexKey = null): self
    {
        return static::new(array_column($this->val, $columnKey, $indexKey));
    }

    public function countValues(): self
    {
        return static::new(array_count_values($this->val));
    }

    public function flip(): self
    {
        return static::new(array_flip($this->val));
    }

    public function exist($needle, bool $strict = null): bool
    {
        return in_array($needle, $this->val, static::config('existStrict', $strict));
    }

    public function keyExist($key): bool
    {
        return array_key_exists($key, $this->val);
    }

    public function isSet($key): bool
    {
        return isset($key, $this->val);
    }

    public function sort(bool $asc = null, bool $preserveKeys = null, int $flag = null): self
    {
        // 通过将 $asc 左移一位并加上 $preserveKeys 得出范围在 0b00 到 0b11 的 $status
        // 通过 $status 的值选择不同的排序函数
        $status = static::config('sortAsc', $asc) << 1 + static::config('sortPreserveKeys', $preserveKeys);
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

    public function userSort(callable $fn, bool $preserveKeys = null): self
    {
        if (static::config('userSortPreserveKeys', $preserveKeys)) {
            uasort($this->val, $fn);
        } else {
            usort($this->val, $fn);
        }

        return $this;
    }

    public function userKeySort(callable $fn): self
    {
        uksort($this->val, $fn);

        return $this;
    }

    public function natSort(bool $caseSensitive = null): self
    {
        if (static::config('natSortCaseSensitive', $caseSensitive)) {
            natsort($this->val);
        } else {
            natcasesort($this->val);
        }

        return $this;
    }

    public function keySort(bool $asc = null, int $flag = null): self
    {
        if (static::config('keySortAsc', $asc)) {
            ksort($this->val, static::config('keySortFlag', $flag));
        } else {
            krsort($this->val, static::config('keySortFlag', $flag));
        }

        return $this;
    }

    public function shuffle(): self
    {
        // todo 检查是否打乱成功
        shuffle($this->val);

        return $this;
    }

    public function unique($flag): self
    {
        return array_unique($this->val, static::config('uniqueFlag', $flag));
    }

    public function reverse(): self
    {
        $val = array_reverse($this->val);

        return static::new($val);
    }

    public function push(...$elements): self
    {
        array_push($this->val, ...$elements);

        return $this;
    }

    public function pop(bool $getElement = null)
    {
        $element = array_pop($this->val);
        if (static::config('popGetElement', $getElement)) {
            return $element;
        }

        return $this;
    }

    public function unShift(...$elements): self
    {
        array_unshift($this->val, ...$elements);

        return $this;
    }

    public function shift(bool $getElement = null)
    {
        $element = array_shift($this->val);
        if (static::config('shiftGetElement', $getElement)) {
            return $element;
        }

        return $this;
    }

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

    public function search($needle, bool $strict = null)
    {
        return array_search($needle, $this->val, static::config('searchStrict', $strict));
    }

    public function before($needle, bool $contain = null): self
    {
        // keys 将原数组的键作为值重新索引为新的数组
        // 内层的 search 返回搜索内容对应原数组的键
        // 通过外层的 search 可以获取原数组的键所对应的新数组的键，此时的键即为 slice 所需的长度
        $len = $this->keys()->search($this->search($needle));
        if (static::config('beforeContain', $contain)) {
            $len++;
        }

        return $this->slice(0, $len, true);
    }

    public function after($needle, bool $contain = null)
    {
        // 原理与 before 相同
        $offset = $this->keys()->search($this->search($needle));
        if (!static::config('afterContain', $contain)) {
            $offset++;
        }

        return $this->slice($offset, null, true);
    }
    
    public function replace(Ary ...$arrays): self
    {
        $ary = static::new($arrays);

        return static::new(
            array_replace($this->val, ...$ary->toArray(false))
        );
    }

    public function replaceRecursive(Ary ...$arrays): self
    {
        $ary = static::new($arrays);

        return static::new(
            array_replace_recursive($this->val, ...$ary->toArray(true))
        );
    }

    public function clean(): self
    {
        $this->val = array_filter($this->val);

        return $this;
    }

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

    public function each(callable $fn, $userData = null): self
    {
        array_walk($this->val, $fn, $userData);

        return $this;
    }

    public function map(callable $fn): self
    {
        $val = array_map($fn, $this->val);

        return static::new($val);
    }

    public function filter(callable $fn, int $flag = null): self
    {
        $val = array_filter($this->val, $fn, static::config('filterFlag', $flag));

        return static::new($val);
    }

    public function reduce(callable $fn, $initial = null)
    {
        return array_reduce($this->val, $fn, $initial);
    }

    public function pad(int $size, $element)
    {
        array_pad($this->val, $size, $element);
    }

    public function allTrue(): bool
    {
        return (bool)$this->product();
    }

    public function product(): float
    {
        return array_product($this->val);
    }

    public function sum(): float
    {
        return array_sum($this->val);
    }

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

    public function toJson(int $options = null, int $depth = 512): string
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

    public static function new(array $array = []): self
    {
        return new static($array);
    }

    public static function setConfig(array $config): void
    {
        foreach ($config as $key => $val) {
            static::$config[$key] = $val;
        }
    }

    public static function fromJson(string $json, int $depth = null, int $options = null): self
    {
        return static::new(
            json_decode(
                $json,
                true,
                static::config('fromJsonDepth', $depth),
                static::config('fromJsonDepth', $options)
            )
        );
    }

    public static function combine(self $key, self $val): self
    {
        return static::new(
            array_combine($key->val(), $val->val())
        );
    }

    public static function fill(int $startIndex, int $num, $val): self
    {
        return static::new(array_fill($startIndex, $num, $val));
    }

    public static function fillKeys(array $keys, $val): self
    {
        return static::new(array_fill_keys($keys, $val));
    }

    protected static function config(string $key, $val = null)
    {
        if (is_null($val)) {
            return static::$config[$key] ?? null;
        }

        return $val;
    }
}
